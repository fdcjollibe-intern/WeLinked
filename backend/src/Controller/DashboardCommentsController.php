<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CloudinaryUploader;
use Cake\Core\Configure;

class DashboardCommentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
    }

    public function create()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->getJsonData();
        $postId = (int)($data['post_id'] ?? 0);
        $contentText = trim((string)($data['content_text'] ?? ''));
        $attachmentUrl = trim((string)($data['attachment_url'] ?? ''));

        if ($postId <= 0 || ($contentText === '' && $attachmentUrl === '')) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Post ID and comment text or attachment are required'
            ], 400);
        }

        try {
            $commentsTable = $this->fetchTable('Comments');
            $comment = $commentsTable->newEmptyEntity();
            $comment->post_id = $postId;
            $comment->user_id = $identity->id;
            $comment->content_text = $contentText;
            if ($attachmentUrl) {
                $comment->content_image_path = $attachmentUrl;
            }

            if ($commentsTable->save($comment)) {
                // Create notification for post owner (if not commenting on own post)
                $postsTable = $this->fetchTable('Posts');
                $post = $postsTable->find()
                    ->select(['user_id'])
                    ->where(['id' => $postId])
                    ->first();
                    
                if ($post && $post->user_id !== $identity->id) {
                    $usersTable = $this->fetchTable('Users');
                    $actor = $usersTable->find()
                        ->select(['username'])
                        ->where(['id' => $identity->id])
                        ->first();
                    
                    $notificationsTable = $this->fetchTable('Notifications');
                    $notification = $notificationsTable->newEntity([
                        'user_id' => $post->user_id,
                        'actor_id' => $identity->id,
                        'type' => 'comment',
                        'target_type' => 'post',
                        'target_id' => $postId,
                        'message' => ($actor ? $actor->username : 'Someone') . ' commented on your post',
                        'is_read' => false,
                    ]);
                    $notificationsTable->save($notification);
                }
                
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->find()
                    ->select(['id', 'username', 'full_name', 'profile_photo_path'])
                    ->where(['id' => $identity->id])
                    ->first();
                $userPayload = $user ? $user->toArray() : null;

                return $this->jsonResponse([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->id,
                        'post_id' => $comment->post_id,
                        'user_id' => $comment->user_id,
                        'content_text' => $comment->content_text,
                        'attachment_url' => $comment->content_image_path ?? null,
                        'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
                        'user' => $userPayload,
                        'user_reaction' => null,
                        'reaction_counts' => new \stdClass(),
                    ],
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unable to save comment',
                'errors' => $comment->getErrors(),
            ], 400);
        } catch (\Exception $e) {
            $this->log('Comment create error: ' . $e->getMessage(), 'error');
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    public function list()
    {
        $this->request->allowMethod(['get']);
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $postId = (int)($this->request->getQuery('post_id') ?? 0);
        if ($postId <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Post ID is required'
            ], 400);
        }

        try {
            $commentsTable = $this->fetchTable('Comments');
            $comments = $commentsTable->find()
                ->where(['post_id' => $postId, 'deleted_at IS' => null])
                ->orderByAsc('created_at')
                ->all();

            $this->log("Found " . count($comments) . " comments for post {$postId}", 'info');

            $usersTable = $this->fetchTable('Users');
            
            // Always try to fetch Reactions table
            try {
                $reactionsTable = $this->fetchTable('Reactions');
                $this->log("Reactions table loaded successfully", 'info');
            } catch (\Exception $e) {
                $this->log("Failed to load Reactions table: " . $e->getMessage(), 'error');
                $reactionsTable = null;
            }
            
            $this->log("Reactions table available: " . ($reactionsTable ? 'yes' : 'no'), 'info');
            
            $commentList = [];
            foreach ($comments as $comment) {
                $user = $usersTable->find()
                    ->select(['id', 'username', 'full_name', 'profile_photo_path'])
                    ->where(['id' => $comment->user_id])
                    ->first();
                
                // Get reaction data for this comment
                $userReaction = null;
                $reactionCounts = [];
                
                if ($reactionsTable) {
                    // Get current user's reaction
                    $myReaction = $reactionsTable->find()
                        ->where([
                            'user_id' => $identity->id,
                            'target_type' => 'comment',
                            'target_id' => $comment->id
                        ])
                        ->first();
                    $userReaction = $myReaction ? $myReaction->reaction_type : null;
                    $this->log("Comment {$comment->id} - Current user (ID: {$identity->id}) reaction: " . ($userReaction ?: 'none'), 'info');
                    
                    // Get reaction counts - simplified query
                    $this->log("Fetching reaction counts for comment {$comment->id}...", 'info');
                    
                    $allReactions = $reactionsTable->find()
                        ->where([
                            'target_type' => 'comment',
                            'target_id' => $comment->id
                        ])
                        ->all();
                    
                    $this->log("Found " . count($allReactions) . " total reactions for comment {$comment->id}", 'info');
                    
                    // Count manually
                    foreach ($allReactions as $reaction) {
                        $type = $reaction->reaction_type;
                        if (!isset($reactionCounts[$type])) {
                            $reactionCounts[$type] = 0;
                        }
                        $reactionCounts[$type]++;
                        $this->log("  - Reaction: {$type} (user: {$reaction->user_id})", 'info');
                    }
                    
                    if (empty($reactionCounts)) {
                        $this->log("Comment {$comment->id} has no reactions (after counting)", 'info');
                    } else {
                        $this->log("Comment {$comment->id} final counts: " . json_encode($reactionCounts), 'info');
                    }
                }
                
                $this->log("Comment {$comment->id} reactions - user: {$userReaction}, counts: " . json_encode($reactionCounts), 'info');
                
                $commentList[] = [
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'user_id' => $comment->user_id,
                    'content_text' => $comment->content_text,
                    'attachment_url' => $comment->content_image_path ?? null,
                    'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
                    'user' => $user ? $user->toArray() : null,
                    'user_reaction' => $userReaction,
                    'reaction_counts' => empty($reactionCounts) ? new \stdClass() : $reactionCounts,
                ];
            }

            $this->log("Returning " . count($commentList) . " comments with reactions", 'info');
            $this->log("Full comment list JSON: " . json_encode($commentList), 'info');
            
            return $this->jsonResponse([
                'success' => true,
                'comments' => $commentList,
            ]);
        } catch (\Exception $e) {
            $this->log('Comment list error: ' . $e->getMessage(), 'error');
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    public function edit()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->getJsonData();
        $commentId = (int)($data['comment_id'] ?? 0);
        $contentText = trim((string)($data['content_text'] ?? ''));
        $removeAttachment = (bool)($data['remove_attachment'] ?? false);
        
        if ($commentId <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Comment ID is required'
            ], 400);
        }

        try {
            $commentsTable = $this->fetchTable('Comments');
            $comment = $commentsTable->find()
                ->where(['id' => $commentId, 'deleted_at IS' => null])
                ->first();

            if (!$comment) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            // Only comment owner can edit
            if ($comment->user_id !== $identity->id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'You do not have permission to edit this comment'
                ], 403);
            }

            // Update content
            $comment->content_text = $contentText;
            
            // Remove attachment if requested
            if ($removeAttachment) {
                $comment->content_image_path = null;
            }

            if ($commentsTable->save($comment)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Comment updated successfully',
                    'comment' => [
                        'id' => $comment->id,
                        'content_text' => $comment->content_text,
                        'attachment_url' => $comment->content_image_path ?? null,
                    ]
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unable to update comment'
            ], 400);
        } catch (\Exception $e) {
            $this->log('Comment edit error: ' . $e->getMessage(), 'error');
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    public function delete()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->getJsonData();
        $commentId = (int)($data['comment_id'] ?? 0);
        if ($commentId <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Comment ID is required'
            ], 400);
        }

        try {
            $commentsTable = $this->fetchTable('Comments');
            $comment = $commentsTable->find()
                ->where(['id' => $commentId, 'deleted_at IS' => null])
                ->first();

            if (!$comment) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            // Check if user owns the comment or the post
            $postsTable = $this->fetchTable('Posts');
            $post = $postsTable->find()
                ->where(['id' => $comment->post_id])
                ->first();

            $canDelete = ($comment->user_id === $identity->id) || ($post && $post->user_id === $identity->id);
            if (!$canDelete) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'You do not have permission to delete this comment'
                ], 403);
            }

            // Delete Cloudinary attachment if exists
            if (!empty($comment->attachment_url)) {
                $this->deleteCloudinaryAttachment($comment->attachment_url);
            }
            
            // Soft delete the comment
            $comment->deleted_at = new \DateTime();
            
            if ($commentsTable->save($comment)) {
                // Hard delete comment reactions
                $reactionsTable = $this->fetchTable('Reactions');
                $deletedReactions = $reactionsTable->deleteAll([
                    'target_type' => 'comment',
                    'target_id' => $commentId
                ]);
                $this->log("Deleted {$deletedReactions} reactions for comment {$commentId}", 'info');
                
                // Delete notifications related to this comment
                $notificationsTable = $this->fetchTable('Notifications');
                
                // Delete notifications about reactions TO this comment (target_type='comment')
                $deletedReactionNotifs = $notificationsTable->deleteAll([
                    'target_type' => 'comment',
                    'target_id' => $commentId
                ]);
                $this->log("Deleted {$deletedReactionNotifs} reaction notifications for comment {$commentId}", 'info');
                
                // Delete notification about THIS comment being created (target_type='post', type='comment', actor_id=comment author)
                $deletedCommentNotif = $notificationsTable->deleteAll([
                    'type' => 'comment',
                    'target_type' => 'post',
                    'target_id' => $comment->post_id,
                    'actor_id' => $comment->user_id
                ]);
                $this->log("Deleted {$deletedCommentNotif} comment notification for post {$comment->post_id}", 'info');
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Comment deleted successfully'
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unable to delete comment'
            ], 400);
        } catch (\Exception $e) {
            $this->log('Comment delete error: ' . $e->getMessage(), 'error');
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Delete attachment from Cloudinary if it exists
     * 
     * @param string $url Cloudinary URL
     * @return void
     */
    private function deleteCloudinaryAttachment(string $url): void
    {
        try {
            // Check if it's a Cloudinary URL
            if (strpos($url, 'cloudinary.com') === false) {
                return;
            }

            // Load Cloudinary config to check if it's enabled
            Configure::load('cloudinary', 'default');
            $cloudinaryConfig = (array)Configure::read('Cloudinary');
            
            if (empty($cloudinaryConfig['api_key']) || empty($cloudinaryConfig['cloud_name'])) {
                return;
            }

            // Extract public_id from Cloudinary URL
            // URL format: https://res.cloudinary.com/<cloud_name>/<resource_type>/upload/<version>/<folder>/<public_id>.<extension>
            // We need to extract everything after 'upload/' and before the file extension
            preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/', $url, $matches);
            
            if (empty($matches[1])) {
                $this->log('Could not extract public_id from URL: ' . $url, 'warning');
                return;
            }

            $publicId = $matches[1];
            
            // Determine resource type (image or video)
            $resourceType = 'image';
            if (preg_match('/\.(mp4|mov|avi|webm|mkv)$/i', $url)) {
                $resourceType = 'video';
            }

            // Delete from Cloudinary
            $uploader = new CloudinaryUploader();
            $deleted = $uploader->delete($publicId, $resourceType);
            
            if ($deleted) {
                $this->log('Successfully deleted Cloudinary attachment: ' . $publicId, 'info');
            } else {
                $this->log('Failed to delete Cloudinary attachment: ' . $publicId, 'warning');
            }
        } catch (\Exception $e) {
            $this->log('Error deleting Cloudinary attachment: ' . $e->getMessage(), 'error');
        }
    }

    private function getJsonData(): array
    {
        $type = $this->request->getHeaderLine('Content-Type');
        if (strpos($type, 'application/json') !== false) {
            $body = (string)$this->request->getBody();
            return json_decode($body, true) ?: [];
        }
        return $this->request->getData();
    }

    private function jsonResponse(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}
