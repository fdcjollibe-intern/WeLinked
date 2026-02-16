<?php
declare(strict_types=1);

namespace App\Controller;

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
            throw new \Cake\Http\Exception\NotFoundException('User not found');
        }
        
        $postsTable = $this->fetchTable('Posts');
        $postCount = $postsTable->find()
            ->where(['user_id' => $user->id])
            ->count();
        
        // Get followers and following counts
        $friendshipsTable = $this->fetchTable('Friendships');
        $followersCount = $friendshipsTable->getFollowersCount($user->id);
        $followingCount = $friendshipsTable->getFollowingCount($user->id);
        
        $detect = new \Detection\MobileDetect();
        $isMobileView = $detect->isMobile() && !$detect->isTablet();
        
        // If requested via AJAX, return only the profile content element
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->set(compact('user', 'postCount', 'followersCount', 'followingCount', 'identity', 'isMobileView'));
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
        $this->set(compact('user', 'postCount', 'followersCount', 'followingCount', 'identity', 'isMobileView', 'currentUser'));
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
}

