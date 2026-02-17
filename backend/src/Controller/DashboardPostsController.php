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
        $this->autoRender = false; // Prevent view rendering for JSON response
        $this->request->allowMethod(['post']);
        
        $this->log("\n" . str_repeat('=', 60), 'info');
        $this->log("POST CREATE REQUEST START", 'info');
        $this->log(str_repeat('=', 60), 'info');
        $this->log("Timestamp: " . date('Y-m-d H:i:s'), 'info');
        $this->log("Request method: " . $this->request->getMethod(), 'info');
        $this->log("Request content type: " . $this->request->contentType(), 'info');
        
        $rawInput = file_get_contents('php://input');
        $this->log("Raw request body length: " . strlen($rawInput) . " bytes", 'info');
        $this->log("Raw request body: " . substr($rawInput, 0, 500) . (strlen($rawInput) > 500 ? '...' : ''), 'debug');
        
        $data = $this->getJsonData();
        $this->log("Parsed JSON data keys: " . json_encode(array_keys($data)), 'info');
        
        $contentText = $data['content_text'] ?? $data['body'] ?? '';
        $location = $data['location'] ?? null;
        $mentions = $data['mentions'] ?? []; // Array of user IDs
        $media = $data['media'] ?? $data['attachments'] ?? []; // Array of uploaded media files
        
        $this->log("Content text length: " . strlen($contentText) . " chars", 'info');
        $this->log("Location: " . ($location ?? 'none'), 'info');
        $this->log("Mentions count: " . count($mentions), 'info');
        $this->log("Media attachments count: " . count($media), 'info');
        if (!empty($media)) {
            $this->log("Media details: " . json_encode($media), 'info');
        }
        
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->log("UNAUTHORIZED: No identity found in request", 'error');
            $this->log(str_repeat('=', 60) . "\n", 'info');
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $this->log("User authenticated - ID: " . $identity->id . ", username: " . ($identity->username ?? 'N/A'), 'info');

        $identityData = [];
        if (is_object($identity) && method_exists($identity, 'getOriginalData')) {
            $identityData = (array)$identity->getOriginalData();
        } elseif (is_object($identity)) {
            $identityData = array_filter([
                'id' => $identity->id ?? null,
                'username' => $identity->username ?? null,
                'full_name' => $identity->full_name ?? null,
                'profile_photo_path' => $identity->profile_photo_path ?? null,
            ], fn($v) => $v !== null);
        } elseif (is_array($identity)) {
            $identityData = $identity;
        }

        try {
            $this->log("Fetching Posts table...", 'debug');
            $postsTable = $this->fetchTable('Posts');
            $post = $postsTable->newEmptyEntity();
            
            $this->log("Creating new post entity...", 'debug');
            $post->user_id = $identity->id;
            $post->content_text = $contentText;
            $post->location = $location;
            
            // Store first image URL in content_image_path for backward compatibility
            if (!empty($media) && isset($media[0]['url'])) {
                $post->content_image_path = $media[0]['url'];
                $this->log("Setting content_image_path: " . $media[0]['url'], 'info');
            }
            
            $this->log("Attempting to save post to database...", 'info');
            
            if ($postsTable->save($post)) {
                $this->log("✓ Post saved successfully!", 'info');
                $this->log("Post ID: " . $post->id, 'info');
                $this->log("Created at: " . $post->created_at, 'info');
                
                // Save post attachments to post_attachments table
                if (!empty($media)) {
                    $this->log("Saving " . count($media) . " attachments to post_attachments table...", 'info');
                    $attachmentsTable = $this->fetchTable('PostAttachments');
                    
                    foreach ($media as $index => $file) {
                        try {
                            $attachment = $attachmentsTable->newEmptyEntity();
                            $attachment->post_id = $post->id;
                            $attachment->file_path = $file['url'];
                            
                            // Determine file type from resource_type or URL
                            $resourceType = $file['resource_type'] ?? '';
                            if ($resourceType === 'video') {
                                $attachment->file_type = 'video';
                            } elseif (preg_match('/\.(mp4|webm|mov|avi|mkv)$/i', $file['url'])) {
                                $attachment->file_type = 'video';
                            } else {
                                $attachment->file_type = 'image';
                            }
                            
                            $attachment->file_size = $file['size'] ?? 0;
                            $attachment->display_order = $index;
                            $attachment->upload_status = 'completed';
                            
                            if ($attachmentsTable->save($attachment)) {
                                $this->log("✓ Attachment $index saved (type: {$attachment->file_type})", 'info');
                            } else {
                                $this->log("✗ Failed to save attachment $index: " . json_encode($attachment->getErrors()), 'error');
                            }
                        } catch (\Exception $e) {
                            $this->log("✗ Exception saving attachment $index: " . $e->getMessage(), 'error');
                        }
                    }
                }
                
                // Save mentions
                if (!empty($mentions)) {
                    $this->log("Saving " . count($mentions) . " mentions...", 'info');
                    $this->log("Mention user IDs: " . json_encode($mentions), 'debug');
                    $this->saveMentions($post->id, $mentions, $identity->id);
                    $this->log("✓ Mentions saved", 'info');
                }
                
                $this->log(str_repeat('=', 60), 'info');
                $this->log("POST CREATE SUCCESS", 'info');
                $this->log(str_repeat('=', 60) . "\n", 'info');
                
                $userPayload = [
                    'id' => $identity->id ?? ($identityData['id'] ?? null),
                    'username' => $identity->username ?? ($identityData['username'] ?? null),
                    'full_name' => $identity->full_name ?? ($identityData['full_name'] ?? null),
                    'profile_photo_path' => $identity->profile_photo_path ?? ($identityData['profile_photo_path'] ?? null),
                ];

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post created successfully',
                    'post' => [
                        'id' => $post->id,
                        'content_text' => $post->content_text,
                        'body' => $post->content_text,
                        'location' => $post->location,
                        'created_at' => $post->created_at,
                        'attachments' => $media,
                        'user' => $userPayload
                    ]
                ]);
            }
            
            $this->log("✗ FAILED TO SAVE POST", 'error');
            $this->log("Entity errors: " . json_encode($post->getErrors()), 'error');
            $this->log("Entity data: " . json_encode([
                'user_id' => $post->user_id,
                'content_text' => substr($post->content_text, 0, 100),
                'location' => $post->location,
                'content_image_path' => $post->content_image_path
            ]), 'error');
            $this->log(str_repeat('=', 60) . "\n", 'info');
            
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create post',
                'errors' => $post->getErrors()
            ], 400);
        } catch (\Exception $e) {
            $this->log(str_repeat('=', 60), 'error');
            $this->log("POST CREATE EXCEPTION", 'error');
            $this->log(str_repeat('=', 60), 'error');
            $this->log('Exception class: ' . get_class($e), 'error');
            $this->log('Exception message: ' . $e->getMessage(), 'error');
            $this->log('Exception file: ' . $e->getFile() . ':' . $e->getLine(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            $this->log(str_repeat('=', 60) . "\n", 'error');
            
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

