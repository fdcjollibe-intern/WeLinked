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

                // Manual lookup debug: query the users table directly to see if the ORM resolves the row
                try {
                    $usersTable = $this->fetchTable('Users');
                    $manual = $usersTable->find()
                        ->where(['username' => $username])
                        ->select(['id', 'username', 'email', 'password'])
                        ->first();
                    error_log('Manual user lookup result: ' . ($manual ? json_encode($manual->toArray()) : 'NOT FOUND'));
                    
                    // Also try raw array (no hydration) to ensure ORM isn't hiding fields
                    try {
                        $manualRaw = $usersTable->find()
                            ->where(['username' => $username])
                            ->select(['id', 'username', 'email', 'password'])
                            ->enableHydration(false)
                            ->first();
                        error_log('Manual raw lookup result: ' . ($manualRaw ? json_encode($manualRaw) : 'NOT FOUND'));
                    } catch (\Throwable $e) {
                        error_log('Manual raw lookup failed: ' . $e->getMessage());
                    }
                    if ($manual) {
                        try {
                            $stored = $manual->get('password') ?? null;
                            error_log('Manual stored password present: ' . ($stored ? 'YES' : 'NO'));
                            if (!empty($stored)) {
                                $checkHasher = new \Authentication\PasswordHasher\DefaultPasswordHasher([
                                    'hashType' => PASSWORD_ARGON2ID,
                                ]);
                                $ok = $checkHasher->check($password, $stored);
                                error_log('Manual password verify: ' . ($ok ? 'OK' : 'FAIL'));
                            }
                        } catch (\Throwable $e) {
                            error_log('Manual verify failed: ' . $e->getMessage());
                        }
                    }
                } catch (\Throwable $e) {
                    error_log('Manual user lookup failed: ' . $e->getMessage());
                }

                // Attempt authentication via Authentication plugin
                $result = $this->Authentication->getResult();
                    // If the authentication plugin didn't find an identity, try a safe manual fallback
                    if ((!$result || !$result->isValid()) && empty($errors)) {
                        try {
                            // Raw (no-hydration) lookup so we get the stored hash directly
                            $usersTable = $this->fetchTable('Users');
                            $row = $usersTable->find()
                                ->where(['username' => $username])
                                ->select(['id', 'username', 'email', 'password'])
                                ->enableHydration(false)
                                ->first();

                            if ($row && !empty($row['password'])) {
                                $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher([
                                    'hashType' => PASSWORD_ARGON2ID,
                                ]);
                                if ($hasher->check($password, $row['password'])) {
                                    // Build minimal identity and set it so the rest of the flow proceeds
                                    $identity = [
                                        'id' => $row['id'],
                                        'username' => $row['username'],
                                        'email' => $row['email'],
                                    ];
                                    $this->Authentication->setIdentity($identity);
                                        $result = $this->Authentication->getResult();
                                        error_log('Manual fallback authentication succeeded for: ' . $username);
                                        // Perform the same post-auth success actions (rehash + response)
                                        try {
                                            $identityObj = $this->Authentication->getIdentity();
                                            // Rehash if needed
                                            $storedHash = $row['password'] ?? null;
                                            if (!empty($storedHash)) {
                                                $rehashHasher = new \Authentication\PasswordHasher\DefaultPasswordHasher([
                                                    'hashType' => PASSWORD_ARGON2ID,
                                                ]);
                                                if ($rehashHasher->needsRehash($storedHash)) {
                                                    $newHash = $rehashHasher->hash($password);
                                                    $userEntity = $usersTable->get($identityObj['id']);
                                                    $userEntity->set('password', $newHash);
                                                    $usersTable->save($userEntity);
                                                    error_log('Password rehashed to Argon2id for user id: ' . $identityObj['id']);
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            error_log('Manual fallback post-auth actions failed: ' . $e->getMessage());
                                        }

                                        // Return success response immediately
                                        error_log('=== LOGIN REQUEST END (SUCCESS - FALLBACK) ===');
                                        return $this->response
                                            ->withType('application/json')
                                            ->withStringBody(json_encode([
                                                'success' => true,
                                                'message' => 'Login successful',
                                                'redirect' => '/users/dashboard'
                                            ]));
                                } else {
                                    error_log('Manual fallback password verify failed for: ' . $username);
                                }
                            }
                        } catch (\Throwable $e) {
                            error_log('Manual fallback auth failed: ' . $e->getMessage());
                        }
                    }
                
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

                    // If the stored password needs rehashing (e.g. migrating to Argon2id),
                    // rehash it now using the plaintext password supplied and save.
                    try {
                        $storedHash = $identity ? ($identity->get('password_hash') ?: $identity->get('password')) : null;
                        if (!empty($storedHash)) {
                            $rehashHasher = new \Authentication\PasswordHasher\DefaultPasswordHasher([
                                'hashType' => PASSWORD_ARGON2ID,
                            ]);
                            if ($rehashHasher->needsRehash($storedHash)) {
                                // Rehash and persist the new hash
                                $newHash = $rehashHasher->hash($password);
                                $usersTable = $this->fetchTable('Users');
                                $userEntity = $usersTable->get($identity->get('id'));
                                $userEntity->set('password', $newHash);
                                $usersTable->save($userEntity);
                                error_log('Password rehashed to Argon2id for user id: ' . $identity->get('id'));
                            }
                        }
                    } catch (\Throwable $e) {
                        // Don't block login if rehash fails; log for investigation.
                        error_log('Password rehash failed: ' . $e->getMessage());
                    }

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
