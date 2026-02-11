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
        
        // If user is already logged in via GET, redirect to dashboard
        if ($result && $result->isValid() && !$this->request->is('post')) {
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
            $isJsonRequest = $this->isJsonLoginRequest();
            // Get all request data
            $allData = $this->request->getData();
            $username = $this->request->getData('username');
            $password = $this->request->getData('password');
            
            // Log incoming request
            error_log('=== LOGIN REQUEST START ===');
            error_log('Request Method: ' . $this->request->getMethod());
            error_log('Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
            error_log('Accept: ' . $this->request->getHeaderLine('Accept'));
            error_log('X-Requested-With: ' . $this->request->getHeaderLine('X-Requested-With'));
            error_log('Detected JSON payload: ' . ($isJsonRequest ? 'YES' : 'NO'));
            error_log('Request Data: ' . json_encode($allData));
            error_log('Username received: ' . var_export($username, true));
            error_log('Password length: ' . strlen($password ?? ''));
            
            // Check if it's JSON request from Vue.js
            if ($isJsonRequest) {
                $this->viewBuilder()->disableAutoLayout();
                $this->autoRender = false;
                
                error_log('Processing as JSON request');
                
                // Basic validation
                if (empty($username) || empty($password)) {
                    error_log('VALIDATION FAILED: Missing username or password');
                    return $this->response
                        ->withStatus(400)
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Username and password are required'
                        ]));
                }
                
                error_log('Attempting authentication for user: ' . $username);
                
                // Attempt authentication
                $result = $this->Authentication->getResult();
                
                // Detailed logging
                $errors = $result ? $result->getErrors() : [];
                $status = $result ? $result->getStatus() : 'NO_RESULT';
                
                error_log('Authentication Result:');
                error_log('  - Status: ' . $status);
                error_log('  - Is Valid: ' . ($result && $result->isValid() ? 'YES' : 'NO'));
                error_log('  - Errors: ' . json_encode($errors));
                
                if ($result && $result->isValid()) {
                    $identity = $this->Authentication->getIdentity();
                    error_log('LOGIN SUCCESS for user: ' . $username);
                    error_log('User ID: ' . ($identity ? $identity->get('id') : 'N/A'));
                    error_log('=== LOGIN REQUEST END (SUCCESS) ===');
                    
                    return $this->response
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => '/users/dashboard'
                        ]));
                }
                
                // Get more specific error message
                $errorMessage = 'Invalid username or password';
                if (!empty($errors)) {
                    $errorMessage = implode(', ', $errors);
                }
                
                error_log('LOGIN FAILED: ' . $errorMessage);
                error_log('=== LOGIN REQUEST END (FAILED) ===');
                
                return $this->response
                    ->withStatus(401)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => $errorMessage,
                        'debug' => [
                            'errors' => $errors,
                            'status' => $status,
                            'username' => $username
                        ]
                    ]));
            }
            
            // Regular form submission validation
            if (empty($username) || empty($password)) {
                $this->Flash->error(__('Username and password are required'));
            } else {
                $result = $this->Authentication->getResult();
                
                if ($result && $result->isValid()) {
                    $redirect = $this->request->getQuery('redirect', [
                        'controller' => 'Users',
                        'action' => 'dashboard',
                    ]);
                    return $this->redirect($redirect);
                }
                
                $this->Flash->error(__('Invalid username or password'));
            }
        }
        
        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Determine whether the current request should be treated as JSON/AJAX login.
     */
    private function isJsonLoginRequest(): bool
    {
        if ($this->request->is('json')) {
            return true;
        }

        $contentType = strtolower($this->request->getHeaderLine('Content-Type'));
        if ($contentType !== '' && str_contains($contentType, 'application/json')) {
            return true;
        }

        $acceptHeader = strtolower($this->request->getHeaderLine('Accept'));
        if ($acceptHeader !== '' && str_contains($acceptHeader, 'application/json')) {
            return true;
        }

        $requestedWith = strtolower($this->request->getHeaderLine('X-Requested-With'));
        if ($requestedWith === 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    /**
     * Logout action
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
        }
        
        return $this->redirect(['controller' => 'Login', 'action' => 'index']);
    }
}
