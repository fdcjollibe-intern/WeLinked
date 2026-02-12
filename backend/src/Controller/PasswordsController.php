<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Passwords Controller
 *
 * Provides UI pages for password reset flow (client-side only for now).
 */
class PasswordsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // allow these actions without authentication
        if (property_exists($this, 'Authentication')) {
            $this->Authentication->addUnauthenticatedActions(['forgot', 'verify', 'reset']);
        }
    }

    public function forgot()
    {
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('login');

        // If the form is submitted, act as a placeholder: redirect to verify page.
        if ($this->request->is('post')) {
            return $this->redirect(['action' => 'verify']);
        }
    }

    public function verify()
    {
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('login');

        // On POST (OTP submission), continue to reset page (placeholder)
        if ($this->request->is('post')) {
            return $this->redirect(['action' => 'reset']);
        }
    }

    public function reset()
    {
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('login');

        // On POST (new password submission), redirect back to login (placeholder)
        if ($this->request->is('post')) {
            return $this->redirect(['controller' => 'Login', 'action' => 'index']);
        }
    }
}
