<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Friends Controller
 *
 * Handles friend management and friend suggestions
 */
class FriendsController extends AppController
{
    /**
     * Index method - Display friends list
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $friendshipsTable = $this->fetchTable('Friendships');
        $usersTable = $this->fetchTable('Users');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        // Get following (people I follow)
        $followingQuery = $friendshipsTable->getFriends($currentUserId)->all();
        $following = [];
        foreach ($followingQuery as $friendship) {
            $friend = $friendship->following;
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $friend->id);
            
            $following[] = [
                'id' => $friend->id,
                'username' => $friend->username,
                'full_name' => $friend->full_name,
                'profile_photo_path' => $friend->profile_photo_path,
                'mutual_count' => $mutualCount,
                'friendship_date' => $friendship->created_at
            ];
        }
        
        // Get followers (people who follow me)
        $followersQuery = $friendshipsTable->getFollowers($currentUserId)->all();
        $followers = [];
        foreach ($followersQuery as $friendship) {
            $follower = $friendship->followers;
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $follower->id);
            $isFollowingBack = $friendshipsTable->isFollowing($currentUserId, $follower->id);
            
            $followers[] = [
                'id' => $follower->id,
                'username' => $follower->username,
                'full_name' => $follower->full_name,
                'profile_photo_path' => $follower->profile_photo_path,
                'mutual_count' => $mutualCount,
                'friendship_date' => $friendship->created_at,
                'is_following_back' => $isFollowingBack
            ];
        }
        
        // If requested via AJAX, return only the middle column
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->set(compact('following', 'followers'));
            $this->render('/Friends/index');
            return;
        }
        
        $this->set(compact('following', 'followers'));
    }

    /**
     * Suggestions method - Get friend suggestions
     *
     * @return \Cake\Http\Response|null JSON response
     */
    public function suggestions()
    {
        $friendshipsTable = $this->fetchTable('Friendships');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $limit = (int)($this->request->getQuery('limit') ?? 5);
        $suggestions = $friendshipsTable->getSuggestions($currentUserId, $limit);
        
        // Calculate mutual friends count for each suggestion
        $result = [];
        foreach ($suggestions as $user) {
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
            
            $result[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo_path' => $user->profile_photo_path,
                'mutual_count' => $mutualCount
            ];
        }
        
        $this->set([
            'success' => true,
            'suggestions' => $result
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'suggestions']);
    }

    /**
     * Follow method - Follow a user
     *
     * @return \Cake\Http\Response|null JSON response
     */
    public function follow()
    {
        $this->request->allowMethod(['post']);
        $friendshipsTable = $this->fetchTable('Friendships');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $data = $this->request->getData();
        $followingId = (int)($data['user_id'] ?? 0);
        
        if ($followingId === 0 || $followingId === $currentUserId) {
            $this->set([
                'success' => false,
                'message' => 'Invalid user ID'
            ]);
            $this->viewBuilder()->setOption('serialize', ['success', 'message']);
            return;
        }
        
        $result = $friendshipsTable->follow($currentUserId, $followingId);
        
        if ($result) {
            $this->set([
                'success' => true,
                'message' => 'Successfully followed user'
            ]);
        } else {
            $this->set([
                'success' => false,
                'message' => 'Already following this user'
            ]);
        }
        
        $this->viewBuilder()->setOption('serialize', ['success', 'message']);
    }

    /**
     * Unfollow method - Unfollow a user
     *
     * @return \Cake\Http\Response|null JSON response
     */
    public function unfollow()
    {
        $this->request->allowMethod(['post']);
        $friendshipsTable = $this->fetchTable('Friendships');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $data = $this->request->getData();
        $followingId = (int)($data['user_id'] ?? 0);
        
        if ($followingId === 0) {
            $this->set([
                'success' => false,
                'message' => 'Invalid user ID'
            ]);
            $this->viewBuilder()->setOption('serialize', ['success', 'message']);
            return;
        }
        
        $result = $friendshipsTable->unfollow($currentUserId, $followingId);
        
        if ($result) {
            $this->set([
                'success' => true,
                'message' => 'Successfully unfollowed user'
            ]);
        } else {
            $this->set([
                'success' => false,
                'message' => 'Not following this user'
            ]);
        }
        
        $this->viewBuilder()->setOption('serialize', ['success', 'message']);
    }

    /**
     * Count method - Get friends count
     *
     * @return \Cake\Http\Response|null JSON response
     */
    public function count()
    {
        $friendshipsTable = $this->fetchTable('Friendships');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $count = $friendshipsTable->getFriendsCount($currentUserId);
        
        $this->set([
            'success' => true,
            'count' => $count
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'count']);
    }
}
