<?php
declare(strict_types=1);

namespace App\Controller;

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
                        'reaction_counts' => [],
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

            $usersTable = $this->fetchTable('Users');
            $reactionsTable = $this->getTableLocator()->exists('Reactions') 
                ? $this->fetchTable('Reactions') 
                : null;
            
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
                    
                    // Get reaction counts
                    $countsQuery = $reactionsTable->find()
                        ->select(['reaction_type', 'count' => $reactionsTable->find()->func()->count('*')])
                        ->where(['target_type' => 'comment', 'target_id' => $comment->id])
                        ->groupBy('reaction_type');
                    
                    foreach ($countsQuery as $row) {
                        $reactionCounts[$row->reaction_type] = (int)$row->count;
                    }
                }
                
                $commentList[] = [
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'user_id' => $comment->user_id,
                    'content_text' => $comment->content_text,
                    'attachment_url' => $comment->content_image_path ?? null,
                    'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
                    'user' => $user ? $user->toArray() : null,
                    'user_reaction' => $userReaction,
                    'reaction_counts' => $reactionCounts,
                ];
            }

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

            // Soft delete the comment
            $comment->deleted_at = new \DateTime();
            
            if ($commentsTable->save($comment)) {
                // Hard delete comment reactions
                $reactionsTable = $this->fetchTable('Reactions');
                $reactionsTable->deleteAll([
                    'target_type' => 'comment',
                    'target_id' => $commentId
                ]);
                
                // Delete notifications related to this comment
                $notificationsTable = $this->fetchTable('Notifications');
                $notificationsTable->deleteAll([
                    'target_type' => 'comment',
                    'target_id' => $commentId
                ]);
                
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
