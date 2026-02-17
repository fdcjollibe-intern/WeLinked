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
        $this->log('=== REACTION REQUEST START ===', 'debug');
        
        $type = $this->request->getHeaderLine('Content-Type');
        if (strpos($type, 'application/json') !== false) {
            $data = json_decode((string)$this->request->getBody(), true) ?: [];
        } else {
            $data = $this->request->getData();
        }

        $this->log('Reaction data received: ' . json_encode($data), 'debug');

        $targetType = $data['target_type'] ?? 'post';
        $targetId = $data['target_id'] ?? ($data['post_id'] ?? null);
        $reactionType = $data['reaction_type'] ?? $data['reaction'] ?? null;

        $this->log("Parsed - targetType: $targetType, targetId: $targetId, reactionType: $reactionType", 'debug');

        if (!$targetId || !$reactionType) {
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
            $userId = $identity->id ?? null;
        } elseif (is_array($identity)) {
            $userId = $identity['id'] ?? null;
        }

        $this->log("User ID: " . ($userId ?? 'NULL'), 'debug');

        if (!$userId) {
            $this->log('No user ID found - authentication required', 'error');
            $resp = ['success' => false, 'error' => 'Authentication required'];
            return $this->response->withType('application/json')->withStringBody(json_encode($resp));
        }

        // Fetch Reactions table and persist changes
        try {
            $this->log('Fetching Reactions table...', 'debug');
            $reactions = $this->fetchTable('Reactions');
            $this->log('Reactions table fetched successfully', 'debug');

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
}
