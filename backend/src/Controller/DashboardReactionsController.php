<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;

class DashboardReactionsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();
    }

    // Toggle or set a reaction on a post/comment
    public function react()
    {
        // Ensure clean output buffer for JSON response
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $this->log('=== REACTION REQUEST START ===', 'debug');
        
        $type = $this->request->getHeaderLine('Content-Type');
        if (strpos($type, 'application/json') !== false) {
            $data = json_decode((string)$this->request->getBody(), true) ?: [];
        } else {
            $data = $this->request->getData();
        }

        $this->log('Reaction data received: ' . json_encode($data), 'debug');

        $targetType = $data['target_type'] ?? 'post';
        $targetId = (int)($data['target_id'] ?? ($data['post_id'] ?? 0));
        $reactionType = $data['reaction_type'] ?? $data['reaction'] ?? null;

        $this->log("Parsed - targetType: $targetType, targetId: $targetId, reactionType: $reactionType", 'debug');

        if (!$targetId || $targetId <= 0 || !$reactionType) {
            $this->log('Missing target_id or reaction_type', 'error');
            throw new BadRequestException('Missing target_id or reaction_type');
        }

        // Resolve current user id from common auth locations
        $identity = $this->request->getAttribute('identity') ?: null;
        if (!$identity && isset($this->Authentication) && method_exists($this->Authentication, 'getIdentity')) {
            $identity = $this->Authentication->getIdentity();
        }
        if (!$identity) {
            $sessionUser = $this->request->getSession()->read('Auth.User');
            $identity = $sessionUser ?: null;
        }

        $userId = null;
        if (is_object($identity)) {
            $userId = (int)($identity->id ?? 0);
        } elseif (is_array($identity)) {
            $userId = (int)($identity['id'] ?? 0);
        }

        $this->log("User ID: " . ($userId ?? 'NULL'), 'debug');

        if (!$userId || $userId <= 0) {
            $this->log('No user ID found - authentication required', 'error');
            $resp = ['success' => false, 'error' => 'Authentication required'];
            return $this->response->withType('application/json')->withStringBody(json_encode($resp));
        }

        // Fetch Reactions table and persist changes
        try {
            $this->log('Fetching Reactions table...', 'debug');
            $reactions = $this->fetchTable('Reactions');
            $this->log('Reactions table fetched successfully', 'debug');

            // Initialize response variables
            $action = 'none';
            $userReaction = null;

            $existing = $reactions->find()
                ->where([
                    'user_id' => $userId,
                    'target_type' => $targetType,
                    'target_id' => $targetId
                ])->first();

            $this->log('Existing reaction: ' . ($existing ? 'FOUND' : 'NOT FOUND'), 'debug');

            if ($existing) {
                if ($existing->reaction_type === $reactionType) {
                    // same reaction clicked -> remove (toggle off)
                    $this->log("Deleting reaction ID: {$existing->id}", 'debug');
                    if ($reactions->delete($existing)) {
                        $this->log('Reaction deleted successfully', 'debug');
                        $action = 'removed';
                        $userReaction = null;
                        
                        // Delete related notification (after setting response vars)
                        try {
                            $this->deleteReactionNotification($userId, $targetType, $targetId);
                        } catch (\Exception $notifError) {
                            $this->log('Non-critical: notification deletion failed: ' . $notifError->getMessage(), 'warning');
                        }
                    } else {
                        $this->log('Failed to delete reaction', 'error');
                        $resp = ['success' => false, 'error' => 'Failed to delete reaction'];
                        return $this->response->withType('application/json')->withStringBody(json_encode($resp));
                    }
                } else {
                    $this->log("Updating reaction from {$existing->reaction_type} to {$reactionType}", 'debug');
                    $existing->reaction_type = $reactionType;
                    if ($reactions->save($existing)) {
                        $this->log('Reaction updated successfully', 'debug');
                        $action = 'updated';
                        $userReaction = $reactionType;
                        
                        // Update existing notification (don't delete and recreate)
                        try {
                            $this->updateReactionNotification($userId, $targetType, $targetId, $reactionType);
                        } catch (\Exception $notifError) {
                            $this->log('Non-critical: notification update failed: ' . $notifError->getMessage(), 'warning');
                        }
                    } else {
                        $this->log('Failed to update reaction: ' . json_encode($existing->getErrors()), 'error');
                        $resp = ['success' => false, 'errors' => $existing->getErrors()];
                        return $this->response->withType('application/json')->withStringBody(json_encode($resp));
                    }
                }
            } else {
                $this->log('Creating new reaction...', 'debug');
                $entity = $reactions->newEmptyEntity();
                $entity->user_id = $userId;
                $entity->target_type = $targetType;
                $entity->target_id = $targetId;
                $entity->reaction_type = $reactionType;
                
                $this->log('Reaction entity: ' . json_encode([
                    'user_id' => $userId,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'reaction_type' => $reactionType
                ]), 'debug');
                
                if ($reactions->save($entity)) {
                    $this->log("Reaction saved successfully with ID: {$entity->id}", 'debug');
                    $action = 'added';
                    $userReaction = $reactionType;
                    
                    // Create notification for target owner
                    try {
                        $this->createReactionNotification($userId, $targetType, $targetId, $reactionType);
                    } catch (\Exception $notifError) {
                        $this->log('Non-critical: notification creation failed: ' . $notifError->getMessage(), 'warning');
                    }
                } else {
                    $this->log('Failed to save reaction: ' . json_encode($entity->getErrors()), 'error');
                    $resp = ['success' => false, 'errors' => $entity->getErrors(), 'message' => 'Failed to save reaction'];
                    return $this->response->withType('application/json')->withStringBody(json_encode($resp));
                }
            }

            // Build reaction counts
            $countsQuery = $reactions->find()
                ->select([
                    'reaction_type',
                    'count' => $reactions->find()->func()->count('*')
                ])
                ->where(['target_type' => $targetType, 'target_id' => $targetId])
                ->groupBy('reaction_type');

            $counts = [];
            foreach ($countsQuery as $row) {
                $counts[$row->reaction_type] = (int)$row->count;
            }

            $this->log('Reaction counts: ' . json_encode($counts), 'debug');
            $this->log("Action: $action, User reaction: " . ($userReaction ?? 'NULL'), 'debug');

            $resp = [
                'success' => true,
                'action' => $action,
                'counts' => $counts,
                'user_reaction' => $userReaction
            ];
            
            $this->log('=== REACTION REQUEST SUCCESS ===', 'debug');
        } catch (\Exception $e) {
            $this->log('Reaction error: ' . $e->getMessage(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            $resp = [
                'success' => false,
                'error' => 'Failed to save reaction: ' . $e->getMessage()
            ];
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode($resp));
    }

    /**
     * Create notification for reaction
     * 
     * @param int $actorId User who reacted
     * @param string $targetType 'post' or 'comment'
     * @param int $targetId ID of post/comment
     * @param string $reactionType Type of reaction
     * @return void
     */
    private function createReactionNotification(int $actorId, string $targetType, int $targetId, string $reactionType): void
    {
        try {
            // Validate inputs
            if ($actorId <= 0 || $targetId <= 0 || empty($targetType) || empty($reactionType)) {
                $this->log('Invalid parameters for notification creation', 'warning');
                return;
            }
            
            // Get the owner of the target (post or comment)
            $ownerId = null;
            $targetDescription = 'your ' . $targetType;
            
            if ($targetType === 'post') {
                $postsTable = $this->fetchTable('Posts');
                $post = $postsTable->find()
                    ->select(['user_id'])
                    ->where(['id' => $targetId])
                    ->first();
                $ownerId = $post ? $post->user_id : null;
            } elseif ($targetType === 'comment') {
                $commentsTable = $this->fetchTable('Comments');
                $comment = $commentsTable->find()
                    ->select(['user_id'])
                    ->where(['id' => $targetId])
                    ->first();
                $ownerId = $comment ? $comment->user_id : null;
            }
            
            // Don't create notification if reacting to own content
            if (!$ownerId || $ownerId === $actorId) {
                return;
            }
            
            // Get actor username for message
            $usersTable = $this->fetchTable('Users');
            $actor = $usersTable->find()
                ->select(['username'])
                ->where(['id' => $actorId])
                ->first();
            
            // Map reaction type to display text
            $reactionEmoji = [
                'like' => '❤️',
                'love' => '🥰',
                'haha' => '😂',
                'wow' => '😲',
                'sad' => '😢',
                'angry' => '😡',
            ][$reactionType] ?? '👍';
            
            $notificationsTable = $this->fetchTable('Notifications');
            $notification = $notificationsTable->newEntity([
                'user_id' => $ownerId,
                'actor_id' => $actorId,
                'type' => 'reaction',
                'target_type' => $targetType,
                'target_id' => $targetId,
                'message' => ($actor ? $actor->username : 'Someone') . ' reacted ' . $reactionEmoji . ' to ' . $targetDescription,
                'is_read' => false,
            ]);
            $notificationsTable->save($notification);
        } catch (\Exception $e) {
            $this->log('Error creating reaction notification: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Delete notification for reaction
     * 
     * @param int $actorId User who removed reaction
     * @param string $targetType 'post' or 'comment'
     * @param int $targetId ID of post/comment
     * @return void
     */
    private function deleteReactionNotification(int $actorId, string $targetType, int $targetId): void
    {
        try {
            // Validate inputs
            if ($actorId <= 0 || $targetId <= 0 || empty($targetType)) {
                $this->log('Invalid parameters for notification deletion', 'warning');
                return;
            }
            
            $notificationsTable = $this->fetchTable('Notifications');
            $notificationsTable->deleteAll([
                'actor_id' => $actorId,
                'type' => 'reaction',
                'target_type' => $targetType,
                'target_id' => $targetId,
            ]);
        } catch (\Exception $e) {
            $this->log('Error deleting reaction notification: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Update notification for reaction (or create if doesn't exist)
     * This prevents duplicate notifications when changing reaction types
     * 
     * @param int $actorId User who reacted
     * @param string $targetType 'post' or 'comment'
     * @param int $targetId ID of post/comment
     * @param string $reactionType Type of reaction
     * @return void
     */
    private function updateReactionNotification(int $actorId, string $targetType, int $targetId, string $reactionType): void
    {
        try {
            // Validate inputs
            if ($actorId <= 0 || $targetId <= 0 || empty($targetType) || empty($reactionType)) {
                $this->log('Invalid parameters for notification update', 'warning');
                return;
            }
            
            // Get the owner of the target
            $ownerId = null;
            $targetDescription = 'your ' . $targetType;
            
            if ($targetType === 'post') {
                $postsTable = $this->fetchTable('Posts');
                $post = $postsTable->find()
                    ->select(['user_id'])
                    ->where(['id' => $targetId])
                    ->first();
                $ownerId = $post ? $post->user_id : null;
            } elseif ($targetType === 'comment') {
                $commentsTable = $this->fetchTable('Comments');
                $comment = $commentsTable->find()
                    ->select(['user_id'])
                    ->where(['id' => $targetId])
                    ->first();
                $ownerId = $comment ? $comment->user_id : null;
            }
            
            // Don't create/update notification if reacting to own content
            if (!$ownerId || $ownerId === $actorId) {
                return;
            }
            
            // Get actor username for message
            $usersTable = $this->fetchTable('Users');
            $actor = $usersTable->find()
                ->select(['username'])
                ->where(['id' => $actorId])
                ->first();
            
            // Map reaction type to display text
            $reactionEmoji = [
                'like' => '❤️',
                'love' => '🥰',
                'haha' => '😂',
                'wow' => '😲',
                'sad' => '😢',
                'angry' => '😡',
            ][$reactionType] ?? '👍';
            
            $message = ($actor ? $actor->username : 'Someone') . ' reacted ' . $reactionEmoji . ' to ' . $targetDescription;
            
            // Find existing notification
            $notificationsTable = $this->fetchTable('Notifications');
            $existingNotification = $notificationsTable->find()
                ->where([
                    'actor_id' => $actorId,
                    'type' => 'reaction',
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                ])
                ->first();
            
            if ($existingNotification) {
                // Update existing notification
                $existingNotification->message = $message;
                $existingNotification->is_read = false; // Mark as unread since reaction changed
                $notificationsTable->save($existingNotification);
            } else {
                // Create new notification
                $notification = $notificationsTable->newEntity([
                    'user_id' => $ownerId,
                    'actor_id' => $actorId,
                    'type' => 'reaction',
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'message' => $message,
                    'is_read' => false,
                ]);
                $notificationsTable->save($notification);
            }
        } catch (\Exception $e) {
            $this->log('Error updating reaction notification: ' . $e->getMessage(), 'error');
        }
    }
}