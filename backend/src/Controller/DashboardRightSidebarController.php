<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;

class DashboardRightSidebarController extends AppController
{
    public function index()
    {
        $this->viewBuilder()->disableAutoLayout();

        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();

        // Load suggested users (users that current user doesn't follow)
        $suggested = [];
        if ($this->getTableLocator()->exists('Users')) {
            $usersTable = $this->getTableLocator()->get('Users');
            
            // Get list of users the current user already follows
            $followingIds = [];
            if ($currentUserId && $this->getTableLocator()->exists('Friendships')) {
                $friendshipsTable = $this->getTableLocator()->get('Friendships');
                $followingIds = $friendshipsTable->find()
                    ->select(['following_id'])
                    ->where(['follower_id' => $currentUserId])
                    ->all()
                    ->extract('following_id')
                    ->toList();
            }
            
            // Add current user to exclusion list
            $excludeIds = array_merge($followingIds, [$currentUserId]);
            
            // Find users to suggest (excluding followed users and current user)
            $query = $usersTable->find()
                ->select(['id', 'username', 'full_name', 'profile_photo_path'])
                ->where(['id NOT IN' => $excludeIds])
                ->orderBy($usersTable->find()->func()->rand())
                ->limit(5);
            
            $suggested = $query->toArray();
        }

        $this->set(compact('suggested', 'currentUser'));
        // Render the existing template under templates/RightSidebar/index.php
        return $this->render('/RightSidebar/index');
    }
}
