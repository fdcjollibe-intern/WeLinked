<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Search Controller
 *
 * Handles search functionality for users and posts
 */
class SearchController extends AppController
{
    /**
     * Index method - Display search results page
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->request->getQuery('q', '');
        $type = $this->request->getQuery('type', 'users'); // users or posts
        
        $results = [];
        
        if (!empty($query)) {
            if ($type === 'posts') {
                $results = $this->searchPosts($query);
            } else {
                $results = $this->searchUsers($query);
            }
        }
        
        // If requested via AJAX, return only the middle column
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->set(compact('query', 'type', 'results'));
            $this->render('/Search/index');
            return;
        }
        
        $this->set(compact('query', 'type', 'results'));
    }

    /**
     * API method for instant search suggestions
     *
     * @return \Cake\Http\Response|null JSON response
     */
    public function suggest()
    {
        $this->request->allowMethod(['get']);
        
        $query = $this->request->getQuery('q', '');
        $type = $this->request->getQuery('type', 'users');
        $limit = (int)($this->request->getQuery('limit') ?? 5);
        
        if (empty($query)) {
            $this->set([
                'success' => true,
                'results' => []
            ]);
            $this->viewBuilder()->setOption('serialize', ['success', 'results']);
            return;
        }
        
        $results = [];
        if ($type === 'posts') {
            $results = $this->searchPosts($query, $limit);
        } else {
            $results = $this->searchUsers($query, $limit);
        }
        
        $this->set([
            'success' => true,
            'results' => $results
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'results']);
    }

    /**
     * Search for users
     *
     * @param string $query Search query
     * @param int $limit Result limit
     * @return array
     */
    private function searchUsers(string $query, int $limit = 20): array
    {
        $usersTable = $this->fetchTable('Users');
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'];
        
        // Search by username, full_name, or email
        $users = $usersTable->find()
            ->select(['id', 'username', 'full_name', 'profile_photo_path'])
            ->where(['id !=' => $currentUserId]) // Exclude self
            ->where([
                'OR' => [
                    ['username LIKE' => '%' . $query . '%'],
                    ['full_name LIKE' => '%' . $query . '%'],
                    ['email LIKE' => '%' . $query . '%']
                ]
            ])
            ->orderBy([
                // Prioritize exact matches
                'CASE 
                    WHEN username = :query THEN 1 
                    WHEN username LIKE :query_start THEN 2
                    WHEN full_name LIKE :query_start THEN 3
                    ELSE 4 
                END' => 'ASC',
                'username' => 'ASC'
            ])
            ->bind(':query', $query, 'string')
            ->bind(':query_start', $query . '%', 'string')
            ->limit($limit)
            ->toArray();
        
        // Check if current user is following each result
        $friendshipsTable = $this->fetchTable('Friendships');
        $results = [];
        
        foreach ($users as $user) {
            $isFollowing = $friendshipsTable->isFollowing($currentUserId, $user->id);
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
            
            $results[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo_path' => $user->profile_photo_path,
                'is_following' => $isFollowing,
                'mutual_count' => $mutualCount
            ];
        }
        
        return $results;
    }

    /**
     * Search for posts
     *
     * @param string $query Search query
     * @param int $limit Result limit
     * @return array
     */
    private function searchPosts(string $query, int $limit = 20): array
    {
        $postsTable = $this->fetchTable('Posts');
        
        // Search in post content_text and location
        $posts = $postsTable->find()
            ->contain(['Users' => function ($q) {
                return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
            }])
            ->where(['Posts.deleted_at IS' => null])
            ->where([
                'OR' => [
                    ['Posts.content_text LIKE' => '%' . $query . '%'],
                    ['Posts.location LIKE' => '%' . $query . '%']
                ]
            ])
            ->orderBy([
                // Prioritize posts with query in the beginning
                'CASE 
                    WHEN content_text LIKE :query_start THEN 1
                    WHEN location LIKE :query_start THEN 2
                    ELSE 3
                END' => 'ASC',
                'Posts.created_at' => 'DESC'
            ])
            ->bind(':query_start', $query . '%', 'string')
            ->limit($limit)
            ->toArray();
        
        $results = [];
        foreach ($posts as $post) {
            // Highlight the search term in content
            $highlightedContent = $this->highlightSearchTerm($post->content_text, $query);
            
            $results[] = [
                'id' => $post->id,
                'content_text' => $post->content_text,
                'highlighted_content' => $highlightedContent,
                'location' => $post->location,
                'content_image_path' => $post->content_image_path,
                'created_at' => $post->created_at,
                'user' => [
                    'id' => $post->user->id,
                    'username' => $post->user->username,
                    'full_name' => $post->user->full_name,
                    'profile_photo_path' => $post->user->profile_photo_path
                ]
            ];
        }
        
        return $results;
    }

    /**
     * Highlight search term in text
     *
     * @param string|null $text Text to highlight
     * @param string $term Search term
     * @return string
     */
    private function highlightSearchTerm(?string $text, string $term): string
    {
        if (empty($text) || empty($term)) {
            return $text ?? '';
        }
        
        // Case-insensitive highlighting
        return preg_replace(
            '/(' . preg_quote($term, '/') . ')/i',
            '<mark class="bg-yellow-200 font-semibold">$1</mark>',
            $text
        );
    }
}
