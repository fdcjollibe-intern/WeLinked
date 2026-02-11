<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Login Controller
 */
class LoginController extends AppController
{
    /**
     * Before filter callback
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow login action without authentication
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    /**
     * Login action
     */
    public function index()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        
        // If user is already logged in, redirect to dashboard
        if ($result && $result->isValid()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }
        
        // Detect mobile/tablet devices
        $detect = new \Detection\MobileDetect();
        $isMobile = $detect->isMobile();
        $isTablet = $detect->isTablet();
        $hideImage = $isMobile || $isTablet;
        
        $this->set(compact('hideImage'));
        
        // Handle POST request
        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();
            
            // Check if it's JSON request from Vue.js
            if ($this->request->is('json')) {
                $this->viewBuilder()->disableAutoLayout();
                $this->autoRender = false;
                
                if ($result && $result->isValid()) {
                    return $this->response
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => '/users/dashboard'
                        ]));
                }
                
                return $this->response
                    ->withStatus(401)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Invalid username or password'
                    ]));
            }
            
            // Regular form submission
            if ($result && $result->isValid()) {
                $redirect = $this->request->getQuery('redirect', [
                    'controller' => 'Users',
                    'action' => 'dashboard',
                ]);
                return $this->redirect($redirect);
            }
            
            $this->Flash->error(__('Invalid username or password'));
        }
        
        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Logout action
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success('You have been logged out.');
        }
        
        return $this->redirect(['controller' => 'Login', 'action' => 'index']);
    }
}
