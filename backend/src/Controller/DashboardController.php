<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;

class DashboardController extends AppController
{
    public function index()
    {
        $this->set('title', 'Dashboard');
        
        // Get current user from authentication
        $identity = $this->request->getAttribute('identity');
        $currentUser = $identity ? (object)[
            'id' => $identity->id ?? $identity['id'] ?? null,
            'username' => $identity->username ?? $identity['username'] ?? 'User',
            'fullname' => $identity->full_name ?? $identity['full_name'] ?? 'Full Name'
        ] : (object)['username' => 'Guest', 'fullname' => 'Guest User'];
        
        // Mock suggested users for now
        $suggested = [
            (object)['username' => 'alice_dev', 'fullname' => 'Alice Developer'],
            (object)['username' => 'bob_designer', 'fullname' => 'Bob Designer'],
            (object)['username' => 'carol_pm', 'fullname' => 'Carol Manager']
        ];
        
        // Mock posts for testing
        $posts = [];
        
        $this->set(compact('currentUser', 'suggested', 'posts'));
    }
}

