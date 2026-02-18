<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;

class DashboardPostsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Disable layout for AJAX actions (create, edit, delete)
        // The view action needs the full layout
    }

    /**
     * Create a new post with mentions and location
     */
    public function create()
    {
        $this->viewBuilder()->disableAutoLayout();
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
        $this->viewBuilder()->disableAutoLayout();
        $this->autoRender = false;
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
            
            // Handle attachment removals
            $removeAttachments = $data['remove_attachments'] ?? [];
            if (!empty($removeAttachments)) {
                $attachmentsTable = $this->fetchTable('PostAttachments');
                foreach ($removeAttachments as $url) {
                    $attachmentsTable->deleteAll([
                        'post_id' => $postId,
                        'file_path' => $url
                    ]);
                }
                
                // If old content_image_path matches any removed URL, clear it
                if (!empty($post->content_image_path) && in_array($post->content_image_path, $removeAttachments)) {
                    $post->content_image_path = null;
                }
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
     * Delete a post (soft delete) with cascade hard delete of related data
     */
    public function delete($postId = null)
    {
        $this->viewBuilder()->disableAutoLayout();
        $this->autoRender = false;
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
            
            // Soft delete the post
            $post->deleted_at = new \DateTime();
            
            if ($postsTable->save($post)) {
                // Cascade hard delete: comments, reactions, mentions, notifications
                $this->cascadeDeletePostData((int)$postId);
                
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
     * Cascade hard delete all related data when a post is deleted
     * Deletes: comments (and comment reactions), post reactions, mentions, notifications, attachments
     */
    private function cascadeDeletePostData(int $postId): void
    {
        try {
            // Get all comment IDs for this post (to delete comment reactions)
            $commentsTable = $this->fetchTable('Comments');
            $commentIds = $commentsTable->find()
                ->select(['id'])
                ->where(['post_id' => $postId])
                ->all()
                ->extract('id')
                ->toList();
            
            // Delete reactions on comments
            if (!empty($commentIds)) {
                $reactionsTable = $this->fetchTable('Reactions');
                $reactionsTable->deleteAll([
                    'target_type' => 'comment',
                    'target_id IN' => $commentIds
                ]);
            }
            
            // Delete comments
            $commentsTable->deleteAll(['post_id' => $postId]);
            
            // Delete post reactions
            $reactionsTable = $this->fetchTable('Reactions');
            $reactionsTable->deleteAll([
                'target_type' => 'post',
                'target_id' => $postId
            ]);
            
            // Delete mentions
            $mentionsTable = $this->fetchTable('Mentions');
            $mentionsTable->deleteAll(['post_id' => $postId]);
            
            // Delete notifications related to this post
            $notificationsTable = $this->fetchTable('Notifications');
            $notificationsTable->deleteAll([
                'target_type' => 'post',
                'target_id' => $postId
            ]);
            
            // Delete post attachments
            $attachmentsTable = $this->fetchTable('PostAttachments');
            $attachmentsTable->deleteAll(['post_id' => $postId]);
            
            // Delete likes (legacy table)
            $likesTable = $this->fetchTable('Likes');
            $likesTable->deleteAll([
                'target_type' => 'post',
                'target_id' => $postId
            ]);
            
        } catch (\Exception $e) {
            // Log but don't fail the main delete operation
            error_log('Cascade delete error for post ' . $postId . ': ' . $e->getMessage());
        }
    }

    /**
     * View a single post
     */
    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Post not found'));
        }

        // Get current user ID
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity ? ($identity->id ?? $identity['id'] ?? null) : null;
        $currentUser = $identity ? (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? $identity['username'] ?? 'User',
            'full_name' => $identity->full_name ?? $identity['full_name'] ?? 'Full Name',
            'profile_photo_path' => $identity->profile_photo_path ?? $identity['profile_photo_path'] ?? null
        ] : (object)['username' => 'Guest', 'full_name' => 'Guest User'];

        $postsTable = $this->fetchTable('Posts');
        
        // Build containments array (same as DashboardMiddleColumnController)
        $contain = [
            'Users' => function (Query $q) {
                return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
            },
            'Reactions' => function (Query $q) {
                return $q->select(['id', 'target_id', 'user_id', 'reaction_type']);
            },
            'Mentions' => function (Query $q) {
                return $q->contain(['MentionedUsers' => function (Query $sub) {
                    return $sub->select(['id', 'username', 'gender']);
                }]);
            },
            'PostAttachments' => function (Query $q) {
                return $q->select(['id', 'post_id', 'file_path', 'file_type', 'file_size', 'display_order'])
                    ->where(['upload_status' => 'completed'])
                    ->orderBy(['display_order' => 'ASC']);
            }
        ];
        
        // Fetch the single post
        $post = $postsTable->find()
            ->contain($contain)
            ->where(['Posts.id' => $id, 'Posts.deleted_at IS' => null])
            ->first();

        if (!$post) {
            // Redirect to dashboard instead of showing error
            $this->Flash->error(__('Post not found or has been deleted.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        // Process post to add reaction summary and user's reaction
        $commentCounts = [];
        $commentsTable = $this->fetchTable('Comments');
        $countsQuery = $commentsTable->find()
            ->select([
                'post_id',
                'count' => $commentsTable->find()->func()->count('*'),
            ])
            ->where([
                'post_id' => $post->id,
                'deleted_at IS' => null,
            ])
            ->groupBy('post_id');

        foreach ($countsQuery as $row) {
            $commentCounts[$row->post_id] = (int)$row->count;
        }

        // Count reactions by type
        $reactionCounts = [];
        $userReaction = null;
        
        if (!empty($post->reactions)) {
            foreach ($post->reactions as $reaction) {
                $type = $reaction->reaction_type;
                if (!isset($reactionCounts[$type])) {
                    $reactionCounts[$type] = 0;
                }
                $reactionCounts[$type]++;
                
                // Check if current user has reacted
                if ($currentUserId && $reaction->user_id == $currentUserId) {
                    $userReaction = $type;
                }
            }
        }
        
        // Add computed fields
        $post->reaction_counts = $reactionCounts;
        $post->user_reaction = $userReaction;
        $post->total_reactions = array_sum($reactionCounts);
        
        // Parse attachments if they exist
        $post->attachments = [];
        if (!empty($post->content_image_path)) {
            $decoded = json_decode($post->content_image_path, true);
            if (is_array($decoded)) {
                $post->attachments = $decoded;
            } else {
                $post->attachments = explode(',', $post->content_image_path);
            }
        }

        $post->comments_count = $commentCounts[$post->id] ?? 0;
        $post->mention_palette = $this->buildMentionPalette($post->mentions ?? []);

        // Get friend suggestions for right sidebar
        $friendsCount = 0;
        $suggestions = [];
        if ($currentUserId) {
            $friendshipsTable = $this->fetchTable('Friendships');
            $friendsCount = $friendshipsTable->getFriendsCount($currentUserId);
            $friendSuggestions = $friendshipsTable->getSuggestions($currentUserId, 6);
            
            foreach ($friendSuggestions as $user) {
                $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
                $suggestions[] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'profile_photo_path' => $user->profile_photo_path,
                    'mutual_count' => $mutualCount
                ];
            }
        }

        // Detect mobile
        $detect = new \Detection\MobileDetect();
        $isMobile = $detect->isMobile();
        $isTablet = $detect->isTablet();
        $isMobileView = ($isMobile && !$isTablet);

        $this->set(compact('post', 'currentUser', 'suggestions', 'friendsCount', 'isMobileView'));
        $this->set('title', 'Post by ' . $post->user->username);
    }

    private function buildMentionPalette(iterable $mentions): array
    {
        $palette = [];
        foreach ($mentions as $mention) {
            $user = $mention->mentioned_user ?? null;
            if (!$user || empty($user->username)) {
                continue;
            }
            $palette[] = [
                'username' => $user->username,
                'color' => $this->mapGenderToColor($user->gender ?? null),
            ];
        }

        return $palette;
    }

    private function mapGenderToColor(?string $gender): string
    {
        return match ($gender) {
            'Male' => 'blue',
            'Female' => 'pink',
            default => 'green',
        };
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

