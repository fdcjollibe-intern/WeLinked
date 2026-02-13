<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\Query;

class DashboardMiddleColumnController extends AppController
{
    public function index()
    {
        $this->viewBuilder()->disableAutoLayout();

        // Pagination params for AJAX loading
        $start = (int)$this->request->getQuery('start', 0);
        $limit = 20;
        $feed = $this->request->getQuery('feed', 'foryou'); // 'foryou', 'friends', or 'reels'

        // Get current user ID for reaction checking
        $currentUserId = $this->request->getAttribute('identity')?->getIdentifier();

        // Load posts from database
        $posts = [];
        if ($this->getTableLocator()->exists('Posts')) {
            $postsTable = $this->getTableLocator()->get('Posts');
            
            // Build the query
            $query = $postsTable->find()
                ->contain([
                    'Users' => function (Query $q) {
                        return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
                    },
                    'Reactions' => function (Query $q) {
                        return $q->select(['id', 'target_id', 'user_id', 'reaction_type']);
                    }
                ])
                ->where(['Posts.deleted_at IS' => null]);

            // Apply feed filter
            if ($feed === 'friends' && $currentUserId) {
                // Only show posts from users that the current user follows
                $friendshipsTable = $this->getTableLocator()->get('Friendships');
                $friendIds = $friendshipsTable->find()
                    ->select(['following_id'])
                    ->where(['follower_id' => $currentUserId])
                    ->extract('following_id')
                    ->toArray();

                if (!empty($friendIds)) {
                    // Include current user's posts and friends' posts
                    $friendIds[] = $currentUserId;
                    $query->where(['Posts.user_id IN' => $friendIds]);
                } else {
                    // No friends, only show current user's posts
                    $query->where(['Posts.user_id' => $currentUserId]);
                }
            }
            // For 'foryou', show all posts (no additional filter needed)

            $query->orderDesc('Posts.created_at')
                ->limit($limit)
                ->offset($start);

            $posts = $query->all()->toArray();

            // Process posts to add reaction summary and user's reaction
            foreach ($posts as $post) {
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
                
                // Parse attachments if they exist (stored as JSON or comma-separated)
                $post->attachments = [];
                if (!empty($post->content_image_path)) {
                    $decoded = json_decode($post->content_image_path, true);
                    if (is_array($decoded)) {
                        $post->attachments = $decoded;
                    } else {
                        // Single image or comma-separated paths
                        $post->attachments = explode(',', $post->content_image_path);
                    }
                }
            }
        }

        $this->set(compact('posts', 'start', 'limit', 'feed'));
        // Render the existing template under templates/MiddleColumn/index.php
        return $this->render('/MiddleColumn/index');
    }
}
