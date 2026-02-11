<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Users Controller
 */
class UsersController extends AppController
{
    /**
     * Dashboard action
     */
    public function dashboard()
    {
        // User must be authenticated to access dashboard
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            return $this->redirect(['controller' => 'Login', 'action' => 'index']);
        }
        
        $this->set('user', $user);
    }
}
