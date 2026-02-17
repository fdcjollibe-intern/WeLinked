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
        $currentUser = $identity ? (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? $identity['username'] ?? 'User',
            'fullname' => $identity->full_name ?? $identity['full_name'] ?? 'Full Name'
        ] : (object)['username' => 'Guest', 'fullname' => 'Guest User'];
        
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
            
            // Skip if follower data is null
            if (!$follower || !$follower->id) {
                $this->log("Skipping null follower in friendship ID: " . $friendship->id, 'warning');
                continue;
            }
            
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
        
        // If requested via AJAX, return only the friends list element
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->set(compact('following', 'followers'));
            $this->render('/element/Friends/friends_list');
            return;
        }
        
        // Render full dashboard layout with friends in middle column
        $this->set(compact('following', 'followers', 'currentUser'));
        $this->render('dashboard');
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
        $this->autoRender = false;
        $this->request->allowMethod(['post']);
        $friendshipsTable = $this->fetchTable('Friendships');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $data = $this->request->getData();
        $followingId = (int)($data['user_id'] ?? 0);
        
        if ($followingId === 0 || $followingId === $currentUserId) {
            return $this->response->withType('application/json')->withStatus(400)->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid user ID'
            ]));
        }
        
        try {
            $result = $friendshipsTable->follow($currentUserId, $followingId);
            
            $this->log("Follow action: user $currentUserId -> $followingId, result: " . ($result ? 'success' : 'already following'), 'debug');
            
            if ($result) {
                return $this->response->withType('application/json')->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Successfully followed user'
                ]));
            } else {
                return $this->response->withType('application/json')->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Already following this user'
                ]));
            }
        } catch (\Exception $e) {
            $this->log("Follow error: " . $e->getMessage(), 'error');
            return $this->response->withType('application/json')->withStatus(500)->withStringBody(json_encode([
                'success' => false,
                'message' => 'An error occurred while following user'
            ]));
        }
    }

    /**
     * Unfollow method - Unfollow a user
     *
     * @return \Cake\Http\Response|null JSON response
     */
    public function unfollow()
    {
        $this->autoRender = false;
        $this->request->allowMethod(['post']);
        $friendshipsTable = $this->fetchTable('Friendships');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        $data = $this->request->getData();
        $followingId = (int)($data['user_id'] ?? 0);
        
        if ($followingId === 0) {
            return $this->response->withType('application/json')->withStatus(400)->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid user ID'
            ]));
        }
        
        try {
            $result = $friendshipsTable->unfollow($currentUserId, $followingId);
            
            $this->log("Unfollow action: user $currentUserId unfollowed $followingId, result: " . ($result ? 'success' : 'not following'), 'debug');
            
            if ($result) {
                return $this->response->withType('application/json')->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Successfully unfollowed user'
                ]));
            } else {
                return $this->response->withType('application/json')->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Not following this user'
                ]));
            }
        } catch (\Exception $e) {
            $this->log("Unfollow error: " . $e->getMessage(), 'error');
            return $this->response->withType('application/json')->withStatus(500)->withStringBody(json_encode([
                'success' => false,
                'message' => 'An error occurred while unfollowing user'
            ]));
        }
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
