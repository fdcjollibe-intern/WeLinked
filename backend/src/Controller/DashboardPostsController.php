<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

class DashboardPostsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
    }

    /**
     * Create a new post with mentions and location
     */
    public function create()
    {
        $this->request->allowMethod(['post']);
        
        $this->log("===== Post Create Request Start =====", 'debug');
        $this->log("Request content type: " . $this->request->contentType(), 'debug');
        $this->log("Request data: " . json_encode($this->request->getData()), 'debug');
        $this->log("Request input: " . file_get_contents('php://input'), 'debug');
        
        $data = $this->getJsonData();
        $contentText = $data['content_text'] ?? $data['body'] ?? '';
        $location = $data['location'] ?? null;
        $mentions = $data['mentions'] ?? []; // Array of user IDs
        $media = $data['media'] ?? $data['attachments'] ?? []; // Array of uploaded media files
        
        $this->log("Parsed data - contentText: $contentText, location: $location, media count: " . count($media), 'debug');
        
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->log("No identity found - unauthorized", 'error');
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $this->log("User ID: " . $identity->id, 'debug');

        try {
            $postsTable = $this->fetchTable('Posts');
            $post = $postsTable->newEmptyEntity();
            
            $post->user_id = $identity->id;
            $post->content_text = $contentText;
            $post->location = $location;
            
            // If media exists, store first image URL in content_image_path for now
            // TODO: Create post_attachments table for multiple media support
            if (!empty($media) && isset($media[0]['url'])) {
                $post->content_image_path = $media[0]['url'];
            }
            
            if ($postsTable->save($post)) {
                $this->log("Post saved successfully with ID: " . $post->id, 'debug');
                
                // Save mentions
                if (!empty($mentions)) {
                    $this->log("Saving mentions: " . json_encode($mentions), 'debug');
                    $this->saveMentions($post->id, $mentions, $identity->id);
                }
                
                $this->log("===== Post Create Request End (Success) =====", 'debug');
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post created successfully',
                    'post' => [
                        'id' => $post->id,
                        'content_text' => $post->content_text,
                        'body' => $post->content_text,
                        'location' => $post->location,
                        'created_at' => $post->created_at,
                        'attachments' => $media
                    ]
                ]);
            }
            
            $this->log("Failed to save post. Errors: " . json_encode($post->getErrors()), 'error');
            $this->log("===== Post Create Request End (Failed) =====", 'debug');
            
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create post',
                'errors' => $post->getErrors()
            ], 400);
        } catch (\Exception $e) {
            $this->log('Post create exception: ' . $e->getMessage(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            $this->log("===== Post Create Request End (Error) =====", 'debug');
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit an existing post
     */
    public function edit($postId = null)
    {
        $this->request->allowMethod(['put', 'patch', 'post']);
        
        if (!$postId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }

        $data = $this->getJsonData();
        $identity = $this->request->getAttribute('identity');
        
        try {
            $postsTable = $this->fetchTable('Posts');
            $post = $postsTable->find()
                ->where(['id' => $postId, 'user_id' => $identity->id, 'deleted_at IS' => null])
                ->first();
            
            if (!$post) {
                return $this->jsonResponse(['success' => false, 'message' => 'Post not found or unauthorized'], 404);
            }
            
            // Update fields
            if (isset($data['content_text'])) {
                $post->content_text = $data['content_text'];
            }
            if (isset($data['location'])) {
                $post->location = $data['location'];
            }
            
            if ($postsTable->save($post)) {
                // Update mentions if provided
                if (isset($data['mentions'])) {
                    $this->updateMentions($post->id, $data['mentions'], $identity->id);
                }
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post updated successfully',
                    'post' => [
                        'id' => $post->id,
                        'content_text' => $post->content_text,
                        'location' => $post->location,
                        'updated_at' => $post->updated_at,
                    ]
                ]);
            }
            
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update post',
                'errors' => $post->getErrors()
            ], 400);
        } catch (\Exception $e) {
            error_log('Post edit error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Delete a post (soft delete)
     */
    public function delete($postId = null)
    {
        $this->request->allowMethod(['delete', 'post']);
        
        if (!$postId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }

        $identity = $this->request->getAttribute('identity');
        
        try {
            $postsTable = $this->fetchTable('Posts');
            $post = $postsTable->find()
                ->where(['id' => $postId, 'user_id' => $identity->id, 'deleted_at IS' => null])
                ->first();
            
            if (!$post) {
                return $this->jsonResponse(['success' => false, 'message' => 'Post not found or unauthorized'], 404);
            }
            
            // Soft delete
            $post->deleted_at = new \DateTime();
            
            if ($postsTable->save($post)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete post'], 400);
        } catch (\Exception $e) {
            error_log('Post delete error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Save mentions for a post
     */
    private function saveMentions(int $postId, array $userIds, int $currentUserId): void
    {
        if (empty($userIds)) {
            return;
        }

        $mentionsTable = $this->fetchTable('Mentions');
        $notificationsTable = $this->fetchTable('Notifications');
        $usersTable = $this->fetchTable('Users');
        
        $currentUser = $usersTable->get($currentUserId);
        
        foreach ($userIds as $userId) {
            // Skip self-mentions
            if ($userId == $currentUserId) {
                continue;
            }
            
            // Create mention
            $mention = $mentionsTable->newEntity([
                'post_id' => $postId,
                'mentioned_user_id' => $userId,
                'mentioned_by_user_id' => $currentUserId,
            ]);
            
            if ($mentionsTable->save($mention)) {
                // Create notification
                $notificationsTable->save($notificationsTable->newEntity([
                    'user_id' => $userId,
                    'actor_id' => $currentUserId,
                    'type' => 'mention',
                    'target_type' => 'post',
                    'target_id' => $postId,
                    'message' => $currentUser->username . ' mentioned you in a post',
                ]));
            }
        }
    }

    /**
     * Update mentions for a post (delete old, add new)
     */
    private function updateMentions(int $postId, array $userIds, int $currentUserId): void
    {
        $mentionsTable = $this->fetchTable('Mentions');
        
        // Delete existing mentions
        $mentionsTable->deleteAll(['post_id' => $postId]);
        
        // Add new mentions
        $this->saveMentions($postId, $userIds, $currentUserId);
    }

    /**
     * Helper to get JSON data from request
     */
    private function getJsonData(): array
    {
        $type = $this->request->getHeaderLine('Content-Type');
        if (strpos($type, 'application/json') !== false) {
            $body = (string)$this->request->getBody();
            return json_decode($body, true) ?: [];
        }
        return $this->request->getData();
    }

    /**
     * Helper to return JSON response
     */
    private function jsonResponse(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}

