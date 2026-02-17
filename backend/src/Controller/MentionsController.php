<?php
declare(strict_types=1);

namespace App\Controller;

class MentionsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
    }

    /**
     * Autocomplete search for users to mention
     * Returns friends/followers matching the search query
     */
    public function search()
    {
        $this->request->allowMethod(['get']);
        
        $query = trim((string)$this->request->getQuery('q', ''));
        $identity = $this->request->getAttribute('identity');
        
        if (!$identity) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $usersTable = $this->fetchTable('Users');
            $friendshipsTable = $this->fetchTable('Friendships');
            
            // Get list of users the current user is following
            $followingIds = $friendshipsTable->find()
                ->select(['following_id'])
                ->where(['follower_id' => $identity->id])
                ->all()
                ->extract('following_id')
                ->toArray();
            
            $baseConditions = [];
            if (!empty($followingIds)) {
                $baseConditions['id IN'] = $followingIds;
            } else {
                // No friends yet, allow searching the rest of the community
                $baseConditions['id !='] = $identity->id;
            }

            if ($query !== '') {
                $baseConditions[] = [
                    'OR' => [
                        'username LIKE' => '%' . $query . '%',
                        'full_name LIKE' => '%' . $query . '%',
                    ]
                ];
            }

            $usersQuery = $usersTable->find()
                ->select(['id', 'username', 'full_name', 'gender', 'profile_photo_path'])
                ->where($baseConditions)
                ->orderBy(['username' => 'ASC'])
                ->limit(10);

            // If user has friends but query returned empty (e.g., typing text not matching)
            // fall back to showing first few following profiles to avoid empty dropdowns.
            $users = $usersQuery->toArray();
            if (empty($users) && !empty($followingIds) && $query === '') {
                $users = $usersTable->find()
                    ->select(['id', 'username', 'full_name', 'gender', 'profile_photo_path'])
                    ->where(['id IN' => $followingIds])
                    ->orderBy(['username' => 'ASC'])
                    ->limit(10)
                    ->toArray();
            }
            
            // Format response with gender color hints
            $formattedUsers = array_map(function($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'gender' => $user->gender ?? 'Prefer not to say',
                    'color' => $this->getGenderColor($user->gender ?? 'Prefer not to say'),
                    'profile_photo' => $user->profile_photo_path,
                ];
            }, $users);
            
            return $this->jsonResponse([
                'success' => true,
                'users' => $formattedUsers
            ]);
        } catch (\Exception $e) {
            error_log('Mentions search error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Get color based on gender
     */
    private function getGenderColor(string $gender): string
    {
        return match($gender) {
            'Male' => 'blue',
            'Female' => 'pink',
            default => 'green',
        };
    }

    /**
     * Helper to return JSON response
     */
    private function jsonResponse(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}
