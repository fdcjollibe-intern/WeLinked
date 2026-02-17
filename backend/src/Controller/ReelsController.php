<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Reels Controller
 * 
 * Handles the Reels feature - short-form vertical videos that auto-play
 * Only shows posts with exactly one video attachment
 */
class ReelsController extends AppController
{
    /**
     * Index method - Load reels feed
     * 
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        // Use minimal layout for full-screen Reels experience
        $this->viewBuilder()->setLayout('reels');
        
        $this->loadModel('Posts');
        
        // Get pagination params
        $start = (int)$this->request->getQuery('start', 0);
        $limit = (int)$this->request->getQuery('limit', 5);
        
        // Query for posts with exactly 1 video attachment
        $query = $this->Posts->find()
            ->select([
                'Posts.id',
                'Posts.user_id',
                'Posts.content_text',
                'Posts.location',
                'Posts.created_at',
                'Users.id',
                'Users.full_name',
                'Users.username',
                'Users.profile_photo_path',
            ])
            ->innerJoin(
                ['PostAttachments' => 'post_attachments'],
                ['PostAttachments.post_id = Posts.id']
            )
            ->contain(['Users'])
            ->where([
                'Posts.deleted_at IS' => null,
                'PostAttachments.file_type' => 'video',
                'PostAttachments.upload_status' => 'completed'
            ])
            ->groupBy(['Posts.id'])
            ->having(['COUNT(PostAttachments.id) = 1']) // Only single video posts
            ->order(['Posts.created_at' => 'DESC'])
            ->offset($start)
            ->limit($limit);
        
        $posts = $query->toArray();
        
        // Enhance posts with attachment data and reaction/comment counts
        foreach ($posts as $post) {
            // Get the video attachment
            $attachment = $this->fetchTable('PostAttachments')
                ->find()
                ->where([
                    'post_id' => $post->id,
                    'file_type' => 'video'
                ])
                ->first();
            
            $post->video_url = $attachment->file_path ?? null;
            $post->video_id = $attachment->id ?? null;
            
            // Get reaction counts
            $reactionsTable = $this->fetchTable('Reactions');
            $reactionCounts = $reactionsTable->find()
                ->select([
                    'reaction_type',
                    'count' => $reactionsTable->find()->func()->count('*')
                ])
                ->where([
                    'target_id' => $post->id,
                    'target_type' => 'post'
                ])
                ->groupBy(['reaction_type'])
                ->toArray();
            
            $post->reaction_counts = [];
            $totalReactions = 0;
            foreach ($reactionCounts as $rc) {
                $post->reaction_counts[$rc->reaction_type] = $rc->count;
                $totalReactions += $rc->count;
            }
            $post->total_reactions = $totalReactions;
            
            // Check current user's reaction if logged in
            $post->user_reaction = null;
            $identity = $this->request->getAttribute('identity');
            if (!$identity && isset($this->Authentication) && method_exists($this->Authentication, 'getIdentity')) {
                $identity = $this->Authentication->getIdentity();
            }
            
            if ($identity) {
                $userId = is_object($identity) ? $identity->getIdentifier() : $identity['id'];
                $userReaction = $reactionsTable->find()
                    ->where([
                        'target_id' => $post->id,
                        'target_type' => 'post',
                        'user_id' => $userId
                    ])
                    ->first();
                if ($userReaction) {
                    $post->user_reaction = $userReaction->reaction_type;
                }
            }
            
            // Get comment count
            $commentsTable = $this->fetchTable('Comments');
            $post->comment_count = $commentsTable->find()
                ->where([
                    'post_id' => $post->id,
                    'deleted_at IS' => null
                ])
                ->count();
        }
        
        // If AJAX request, return JSON
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setClassName('Json');
            $this->set([
                'success' => true,
                'reels' => $posts,
                '_serialize' => ['success', 'reels']
            ]);
            return;
        }
        
        // Regular page load
        $this->set(compact('posts'));
    }
}
