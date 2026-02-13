<?php
declare(strict_types=1);

namespace App\Controller;

class ProfileController extends AppController
{
    public function index($username = null)
    {
        $this->viewBuilder()->disableAutoLayout();
        
        $identity = $this->request->getAttribute('identity');
        
        if ($username === null) {
            $username = $identity->username;
        }
        
        $user = $this->fetchTable('Users')
            ->find()
            ->where(['username' => $username])
            ->first();
            
        if (!$user) {
            return $this->response->withStatus(404);
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
            
        $this->set(compact('user', 'postCount', 'followersCount', 'followingCount', 'identity', 'isMobileView'));
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
        $this->viewBuilder()->setOption('serialize', ['success', 'followers']);
        
        $identity = $this->request->getAttribute('identity');
        
        if ($username === null) {
            $username = $identity->username;
        }
        
        $user = $this->fetchTable('Users')
            ->find()
            ->where(['username' => $username])
            ->first();
            
        if (!$user) {
            $this->set(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        $friendshipsTable = $this->fetchTable('Friendships');
        $currentUserId = $identity->id ?? $identity['id'];
        
        // Get followers
        $followersQuery = $friendshipsTable->getFollowers($user->id)->all();
        $followers = [];
        foreach ($followersQuery as $friendship) {
            $follower = $friendship->followers;
            $isFollowing = $friendshipsTable->isFollowing($currentUserId, $follower->id);
            
            $followers[] = [
                'id' => $follower->id,
                'username' => $follower->username,
                'full_name' => $follower->full_name,
                'profile_photo_path' => $follower->profile_photo_path,
                'is_following' => $isFollowing
            ];
        }
        
        $this->set(['success' => true, 'followers' => $followers]);
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
        $this->viewBuilder()->setOption('serialize', ['success', 'following']);
        
        $identity = $this->request->getAttribute('identity');
        
        if ($username === null) {
            $username = $identity->username;
        }
        
        $user = $this->fetchTable('Users')
            ->find()
            ->where(['username' => $username])
            ->first();
            
        if (!$user) {
            $this->set(['success' => false, 'message' => 'User not found']);
            return;
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
        
        $this->set(['success' => true, 'following' => $following]);
    }
}

