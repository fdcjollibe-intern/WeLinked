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
            $isJsonRequest = $this->isJsonRegisterRequest();

            // Log minimal request info for debugging
            error_log('=== REGISTER REQUEST START ===');
            error_log('Request Method: ' . $this->request->getMethod());
            error_log('Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
            error_log('Accept: ' . $this->request->getHeaderLine('Accept'));
            error_log('X-Requested-With: ' . $this->request->getHeaderLine('X-Requested-With'));
            error_log('Detected JSON payload: ' . ($isJsonRequest ? 'YES' : 'NO'));
            error_log('Request Data: ' . json_encode($this->request->getData()));

            if ($isJsonRequest) {
                $this->viewBuilder()->disableAutoLayout();
                $this->autoRender = false;

                $data = $this->request->getData();

                // Validate confirm password and minimal length
                $plainPassword = $data['password'] ?? '';
                if ($plainPassword !== ($data['confirmPassword'] ?? '')) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Passwords do not match'
                    ]);
                    return $this->response
                        ->withStatus(400)
                        ->withType('application/json')
                        ->withStringBody($body);
                }

                if (strlen($plainPassword) < 6) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Password must be at least 6 characters'
                    ]);
                    return $this->response
                        ->withStatus(400)
                        ->withType('application/json')
                        ->withStringBody($body);
                }

                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->newEmptyEntity();
                // Hash password here and store to password_hash column using Argon2id
                $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher([
                    'hashType' => PASSWORD_ARGON2ID,
                ]);
                $hashed = $hasher->hash($plainPassword);

                $user = $usersTable->patchEntity($user, [
                    'full_name' => $data['full_name'] ?? null,
                    'username' => $data['username'] ?? null,
                    'email' => $data['email'] ?? ($data['email'] ?? null),
                    // Some DB schemas use `password` column; include both to be compatible
                    'password' => $hashed,
                    'password_hash' => $hashed,
                ]);

                if ($usersTable->save($user)) {
                    // Auto-login the user
                    $this->Authentication->setIdentity($user);

                    $body = json_encode([
                        'success' => true,
                        'message' => 'Registration successful',
                        'redirect' => '/users/dashboard'
                    ]);
                    error_log('=== REGISTER REQUEST END (SUCCESS) ===');
                    return $this->response
                        ->withType('application/json')
                        ->withStringBody($body);
                }

                // Get validation/save errors
                $errors = $user->getErrors();
                error_log('Register save errors: ' . json_encode($errors));
                $errorMessage = 'Registration failed. Please try again.';
                if (!empty($errors)) {
                    $firstField = array_key_first($errors);
                    $fieldErrors = $errors[$firstField] ?? [];
                    if (is_array($fieldErrors)) {
                        $rawMsg = reset($fieldErrors);
                    } else {
                        $rawMsg = (string)$fieldErrors;
                    }

                    // Map generic unique constraint messages to friendlier text
                    if (is_array($fieldErrors) && array_key_exists('unique', $fieldErrors)) {
                        if ($firstField === 'username') {
                            $errorMessage = 'Username is already taken';
                        } else {
                            $errorMessage = $rawMsg;
                        }
                    } else {
                        $errorMessage = $rawMsg;
                    }
                }

                $body = json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors
                ]);
                error_log('=== REGISTER REQUEST END (FAILED) ===');
                return $this->response
                    ->withStatus(400)
                    ->withType('application/json')
                    ->withStringBody($body);
            }
        }

        $this->viewBuilder()->setLayout('login');
        $this->render('/Login/index');
    }

    /**
     * Determine whether the current request should be treated as JSON/AJAX register.
     */
    private function isJsonRegisterRequest(): bool
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
}
