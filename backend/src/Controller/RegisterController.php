<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Register Controller
 */
class RegisterController extends AppController
{
    /**
     * Before filter callback
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow register action without authentication
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    /**
     * Register action
     */
    public function index()
    {
        $this->request->allowMethod(['get', 'post']);
        
        // If user is already logged in, redirect to dashboard
        $result = $this->Authentication->getResult();
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
            if ($this->request->is('json')) {
                $this->viewBuilder()->disableAutoLayout();
                $this->autoRender = false;
                
                $data = $this->request->getData();
                
                // Validate confirm password
                if ($data['password'] !== $data['confirmPassword']) {
                    $this->response = $this->response->withStatus(400)->withType('application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Passwords do not match'
                    ]);
                    return;
                }
                
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->newEmptyEntity();
                $user = $usersTable->patchEntity($user, [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => $data['password']
                ]);
                
                if ($usersTable->save($user)) {
                    // Auto-login the user
                    $this->Authentication->setIdentity($user);
                    
                    $this->response = $this->response->withType('application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful',
                        'redirect' => '/users/dashboard'
                    ]);
                    return;
                }
                
                // Get first error message
                $errors = $user->getErrors();
                $errorMessage = 'Registration failed. Please try again.';
                if (!empty($errors)) {
                    $firstField = array_key_first($errors);
                    $errorMessage = reset($errors[$firstField]);
                }
                
                $this->response = $this->response->withStatus(400)->withType('application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                return;
            }
        }
        
        $this->viewBuilder()->setLayout('login');
        $this->render('/Login/index');
    }
}
