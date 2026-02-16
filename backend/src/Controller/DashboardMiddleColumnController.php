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
        $feed = $this->request->getQuery('feed', 'friends'); // Changed default to 'friends'

        // Get current user ID for reaction checking
        $currentUserId = $this->request->getAttribute('identity')?->getIdentifier();

        // Debug log
        $this->log("===== Middle Column Request Start =====", 'debug');
        $this->log("Feed: $feed, Start: $start, User ID: $currentUserId", 'debug');
        $this->log("Request URL: " . $this->request->getRequestTarget(), 'debug');

        // Load posts from database
        $posts = [];
        $this->log("Checking if Posts table exists...", 'debug');
        try {
            $postsTable = $this->fetchTable('Posts');
            $this->log("Posts table found, building query...", 'debug');
            
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
                $this->log("Feed is 'friends', checking friendships...", 'debug');
                // Only show posts from users that the current user follows
                $friendshipsTable = $this->getTableLocator()->get('Friendships');
                $friendIds = $friendshipsTable->find()
                    ->select(['following_id'])
                    ->where(['follower_id' => $currentUserId])
                    ->extract('following_id')
                    ->toArray();

                $this->log("Found " . count($friendIds) . " friends for user $currentUserId", 'debug');

                if (!empty($friendIds)) {
                    // Include current user's posts and friends' posts
                    $friendIds[] = $currentUserId;
                    $query->where(['Posts.user_id IN' => $friendIds]);
                    $this->log("Filtering posts for user IDs: " . implode(', ', $friendIds), 'debug');
                } else {
                    // No friends, only show current user's posts
                    $query->where(['Posts.user_id' => $currentUserId]);
                    $this->log("No friends found, showing only current user's posts", 'debug');
                }
            } else {
                $this->log("Feed is 'foryou' or no user, showing all posts", 'debug');
            }
            // For 'foryou', show all posts (no additional filter needed)

            // Randomize order so results are not sequential from DB.
            // For both 'foryou' (all posts) and 'friends' (followed users),
            // return a random selection to satisfy the UX requirement.
            $query->order('RAND()')
                ->limit($limit)
                ->offset($start);

            $posts = $query->all()->toArray();
            
            // Debug log
            $this->log("Query executed, found " . count($posts) . " posts", 'debug');
            if (count($posts) > 0) {
                $postIds = array_map(function($p) { return $p->id; }, $posts);
                $this->log("Post IDs: " . implode(', ', $postIds), 'debug');
            } else {
                $this->log("WARNING: No posts found! Check database and filters.", 'debug');
            }

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
        } catch (\Exception $e) {
            $this->log("ERROR loading posts: " . $e->getMessage(), 'error');
            $this->log("Stack trace: " . $e->getTraceAsString(), 'error');
        }

        //Ensure currentUser is set for template
        $currentUser = $this->request->getAttribute('identity');
        if (!$currentUser) {
            $currentUser = (object)['username' => 'Guest', 'full_name' => 'Guest User'];
        }
        
        $this->set(compact('posts', 'start', 'limit', 'feed', 'currentUser'));
        $this->log("Setting template variables: posts=" . count($posts) . ", start=$start, limit=$limit, feed=$feed", 'debug');
        $this->log("===== Middle Column Request End =====", 'debug');
        // Render the existing template under templates/MiddleColumn/index.php
        return $this->render('/MiddleColumn/index');
    }
}
