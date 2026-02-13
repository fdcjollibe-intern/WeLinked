<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;

class DashboardController extends AppController
{
    public function index()
    {
        // If requested via AJAX (fragment load), render only the middle column element
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            // Render the middle column element as the template for this request
            $this->render('/element/middle_column');
            return;
        }
        $this->set('title', 'Dashboard');
        
        // Get current user from authentication
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity->id ?? $identity['id'] ?? null;
        $currentUser = $identity ? (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? $identity['username'] ?? 'User',
            'fullname' => $identity->full_name ?? $identity['full_name'] ?? 'Full Name'
        ] : (object)['username' => 'Guest', 'fullname' => 'Guest User'];
        
        // Get friends count for sidebar
        $friendshipsTable = $this->fetchTable('Friendships');
        $friendsCount = $currentUserId ? $friendshipsTable->getFriendsCount($currentUserId) : 0;
        
        // Get friend suggestions for right sidebar
        $friendSuggestions = $currentUserId ? $friendshipsTable->getSuggestions($currentUserId, 6) : [];
        
        // Calculate mutual friends for each suggestion
        $suggestions = [];
        foreach ($friendSuggestions as $user) {
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
            $suggestions[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo_path' => $user->profile_photo_path,
                'mutual_count' => $mutualCount
            ];
        }
        
        // Mock posts for testing
        $posts = [];
        
        // Detect mobile devices to render a mobile-optimized UI
        $detect = new \Detection\MobileDetect();
        $isMobile = $detect->isMobile();
        $isTablet = $detect->isTablet();
        $isMobileView = ($isMobile && !$isTablet);

        $this->set(compact('currentUser', 'suggestions', 'friendsCount', 'posts', 'isMobileView'));
    }
}

