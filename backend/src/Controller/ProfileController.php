<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\Query;

class ProfileController extends AppController
{
    public function index($username = null)
    {
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        // Debug logging
        $isAjax = $this->request->is('ajax');
        $hasPartial = $this->request->getQuery('partial');
        $this->log("ProfileController::index - initial username param: " . ($username ?? 'NULL') . ", isAjax: " . ($isAjax ? 'yes' : 'no') . ", hasPartial: " . ($hasPartial ? 'yes' : 'no'), 'debug');

        // Fallback for wildcard routes: pull username from pass params if not provided explicitly
        if ($username === null) {
            $passSegments = (array)$this->request->getParam('pass', []);
            if (!empty($passSegments)) {
                $username = $passSegments[0];
                $this->log("ProfileController::index - username resolved from pass segment: {$username}", 'debug');
            }
        }
        
        // If no username provided and not AJAX, redirect to own profile with username
        if ($username === null && !$this->request->is('ajax')) {
            return $this->redirect('/profile/' . $identity->username);
        }
        
        // For AJAX requests without username, use current user's username
        if ($username === null) {
            $username = $identity->username;
        }
        
        $user = $this->fetchTable('Users')
            ->find()
            ->where(['username' => $username])
            ->first();
            
        if (!$user) {
            // Instead of throwing exception, render user not found page with layout
            $this->set('username', $username);
            
            // If AJAX request, return just the error content
            if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
                $this->viewBuilder()->disableAutoLayout();
                $this->render('/element/Profile/user_not_found');
                return;
            }
            
            // For full page request, render with dashboard layout
            $currentUser = $identity ? (object)[
                'id' => $currentUserId,
                'username' => $identity->username ?? $identity['username'] ?? 'User',
                'fullname' => $identity->full_name ?? $identity['full_name'] ?? 'Full Name'
            ] : (object)['username' => 'Guest', 'fullname' => 'Guest User'];
            
            $this->set(compact('currentUser', 'identity'));
            $this->viewBuilder()->setTemplate('user_not_found');
            $this->viewBuilder()->setLayout('default');
            return;
        }
        
        $postsTable = $this->fetchTable('Posts');
        $postCount = $postsTable->find()
            ->where(['user_id' => $user->id])
            ->count();
        
        // Get followers and following counts
        $friendshipsTable = $this->fetchTable('Friendships');
        $followersCount = $friendshipsTable->getFollowersCount($user->id);
        $followingCount = $friendshipsTable->getFollowingCount($user->id);

        $posts = $this->fetchUserPosts($user->id, $currentUserId);
        
        $detect = new \Detection\MobileDetect();
        $isMobileView = $detect->isMobile() && !$detect->isTablet();
        
        // If requested via AJAX, return only the profile content element
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->set(compact('user', 'postCount', 'followersCount', 'followingCount', 'identity', 'isMobileView', 'posts'));
            $this->render('/element/Profile/profile_content');
            return;
        }
        
        // Create currentUser object for navigation
        $currentUser = $identity ? (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? $identity['username'] ?? 'User',
            'fullname' => $identity->full_name ?? $identity['full_name'] ?? 'Full Name'
        ] : (object)['username' => 'Guest', 'fullname' => 'Guest User'];
        
        $this->log("ProfileController::index - Rendering full dashboard layout for non-AJAX request", 'debug');
        
        // Render full dashboard layout with profile in middle column
        $this->set(compact('user', 'postCount', 'followersCount', 'followingCount', 'identity', 'isMobileView', 'currentUser', 'posts'));
        $this->viewBuilder()->setTemplate('dashboard');
        $this->viewBuilder()->setLayout('default');
    }
    
    /**
     * Get followers for a user (AJAX endpoint)
     *
     * @param string|null $username Username
     * @return void
     */
    public function followers($username = null)
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);
        
        $identity = $this->request->getAttribute('identity');
        
        if ($username === null) {
            $username = $identity->username;
        }
        
        $user = $this->fetchTable('Users')
            ->find()
            ->where(['username' => $username])
            ->first();
            
        if (!$user) {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode(['success' => false, 'message' => 'User not found']));
        }
        
        $friendshipsTable = $this->fetchTable('Friendships');
        $currentUserId = $identity->id ?? $identity['id'];
        
        // Get followers
        $followersQuery = $friendshipsTable->getFollowers($user->id)->all();
        $followers = [];
        foreach ($followersQuery as $friendship) {
            $follower = $friendship->followers;
            
            // Skip if follower data is null
            if (!$follower || !$follower->id) {
                $this->log("Skipping null follower in friendship ID: " . $friendship->id, 'warning');
                continue;
            }
            
            $isFollowing = $friendshipsTable->isFollowing($currentUserId, $follower->id);
            
            $followers[] = [
                'id' => $follower->id,
                'username' => $follower->username,
                'full_name' => $follower->full_name,
                'profile_photo_path' => $follower->profile_photo_path,
                'is_following' => $isFollowing
            ];
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'followers' => $followers]));
    }
    
    /**
     * Get following for a user (AJAX endpoint)
     *
     * @param string|null $username Username
     * @return void
     */
    public function following($username = null)
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);
        
        $identity = $this->request->getAttribute('identity');
        
        if ($username === null) {
            $username = $identity->username;
        }
        
        $user = $this->fetchTable('Users')
            ->find()
            ->where(['username' => $username])
            ->first();
            
        if (!$user) {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode(['success' => false, 'message' => 'User not found']));
        }
        
        $friendshipsTable = $this->fetchTable('Friendships');
        $currentUserId = $identity->id ?? $identity['id'];
        
        // Get following
        $followingQuery = $friendshipsTable->getFriends($user->id)->all();
        $following = [];
        foreach ($followingQuery as $friendship) {
            $friend = $friendship->following;
            $isFollowing = $friendshipsTable->isFollowing($currentUserId, $friend->id);
            
            $following[] = [
                'id' => $friend->id,
                'username' => $friend->username,
                'full_name' => $friend->full_name,
                'profile_photo_path' => $friend->profile_photo_path,
                'is_following' => $isFollowing
            ];
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'following' => $following]));
    }

    private function fetchUserPosts(int $profileUserId, ?int $viewerId): array
    {
        try {
            $postsTable = $this->fetchTable('Posts');
        } catch (\Exception $exception) {
            $this->log('Profile posts fetch failed: ' . $exception->getMessage(), 'error');
            return [];
        }

        $query = $postsTable->find()
            ->contain([
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
                },
            ])
            ->where([
                'Posts.user_id' => $profileUserId,
                'Posts.deleted_at IS' => null,
            ])
            ->orderByDesc('Posts.created_at')
            ->limit(20);

        $posts = $query->all()->toArray();

        return $this->decoratePosts($posts, $viewerId);
    }

    private function decoratePosts(array $posts, ?int $viewerId): array
    {
        if (empty($posts)) {
            return [];
        }

        $postIds = array_map(fn($post) => $post->id, $posts);
        $commentCounts = [];

        if (!empty($postIds)) {
            $commentsTable = $this->fetchTable('Comments');
            $countsQuery = $commentsTable->find()
                ->select([
                    'post_id',
                    'count' => $commentsTable->find()->func()->count('*'),
                ])
                ->where([
                    'post_id IN' => $postIds,
                    'deleted_at IS' => null,
                ])
                ->groupBy('post_id');

            foreach ($countsQuery as $row) {
                $commentCounts[$row->post_id] = (int)$row->count;
            }
        }

        foreach ($posts as $post) {
            $reactionCounts = [];
            $userReaction = null;

            foreach ($post->reactions ?? [] as $reaction) {
                $type = $reaction->reaction_type;
                $reactionCounts[$type] = ($reactionCounts[$type] ?? 0) + 1;
                if ($viewerId && (int)$reaction->user_id === (int)$viewerId) {
                    $userReaction = $type;
                }
            }

            $post->reaction_counts = $reactionCounts;
            $post->user_reaction = $userReaction;
            $post->total_reactions = array_sum($reactionCounts);

            $post->attachments = [];
            if (!empty($post->content_image_path)) {
                $decoded = json_decode($post->content_image_path, true);
                if (is_array($decoded)) {
                    $post->attachments = $decoded;
                } else {
                    $post->attachments = array_filter(array_map('trim', explode(',', $post->content_image_path)));
                }
            }

            $post->comments_count = $commentCounts[$post->id] ?? 0;
            $post->mention_palette = $this->buildMentionPalette($post->mentions ?? []);
        }

        return $posts;
    }

    private function buildMentionPalette(iterable $mentions): array
    {
        $palette = [];
        foreach ($mentions as $mention) {
            $mentionedUser = $mention->mentioned_user ?? null;
            if (!$mentionedUser || empty($mentionedUser->username)) {
                continue;
            }

            $palette[] = [
                'username' => $mentionedUser->username,
                'color' => $this->mapGenderToColor($mentionedUser->gender ?? null),
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
     * Update user profile
     *
     * @return \Cake\Http\Response|null
     */
    public function update()
    {
        $this->autoRender = false;
        $this->request->allowMethod(['post']);
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($currentUserId);
        
        // Get form data
        $data = [
            'full_name' => $this->request->getData('full_name'),
            'username' => $this->request->getData('username'),
            'bio' => $this->request->getData('bio'),
            'website' => $this->request->getData('website'),
            'gender' => $this->request->getData('gender'),
        ];
        
        // Patch entity with validation
        $user = $usersTable->patchEntity($user, $data);
        
        if ($user->hasErrors()) {
            $errors = [];
            foreach ($user->getErrors() as $field => $error) {
                $errors[$field] = is_array($error) ? reset($error) : $error;
            }
            
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]));
        }
        
        if ($usersTable->save($user)) {
            // Update identity/session
            if (isset($this->Authentication)) {
                $this->Authentication->setIdentity($user);
            }
            
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'bio' => $user->bio,
                        'website' => $user->website,
                        'gender' => $user->gender,
                    ],
                ]));
        }
        
        return $this->response->withType('application/json')
            ->withStatus(500)
            ->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to update profile'
            ]));
    }
}

