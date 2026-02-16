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
        
        // Allow register actions without authentication
        $this->Authentication->addUnauthenticatedActions(['index', 'checkUsername', 'checkEmail']);
    }

    /**
     * AJAX: check username availability
     */
    public function checkUsername()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();
        $this->autoRender = false;

        $data = $this->request->getData();
        $username = isset($data['username']) ? trim($data['username']) : '';

        if ($username === '' || strlen($username) < 3) {
            $body = json_encode(['available' => false, 'message' => 'Invalid username']);
            return $this->response->withStatus(400)->withType('application/json')->withStringBody($body);
        }

        try {
            $usersTable = $this->fetchTable('Users');
            $exists = (bool)$usersTable->find()->where(['username' => $username])->select(['id'])->enableHydration(false)->first();
            $available = !$exists;
            $body = json_encode(['available' => $available]);
            return $this->response->withType('application/json')->withStringBody($body);
        } catch (\Throwable $e) {
            error_log('checkUsername error: ' . $e->getMessage());
            $body = json_encode(['available' => false, 'message' => 'error']);
            return $this->response->withStatus(500)->withType('application/json')->withStringBody($body);
        }
    }

    /**
     * AJAX: check email availability and format
     */
    public function checkEmail()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();
        $this->autoRender = false;

        $data = $this->request->getData();
        $email = isset($data['email']) ? trim($data['email']) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $body = json_encode(['available' => false, 'message' => 'invalid']);
            return $this->response->withStatus(400)->withType('application/json')->withStringBody($body);
        }

        try {
            $usersTable = $this->fetchTable('Users');
            $exists = (bool)$usersTable->find()->where(['email' => $email])->select(['id'])->enableHydration(false)->first();
            $available = !$exists;
            $body = json_encode(['available' => $available]);
            return $this->response->withType('application/json')->withStringBody($body);
        } catch (\Throwable $e) {
            error_log('checkEmail error: ' . $e->getMessage());
            $body = json_encode(['available' => false, 'message' => 'error']);
            return $this->response->withStatus(500)->withType('application/json')->withStringBody($body);
        }
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
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
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
                    'gender' => 'Prefer not to say',
                    'profile_photo_path' => null,
                ]);

                if ($usersTable->save($user)) {
                    // Auto-login the user
                    $this->Authentication->setIdentity($user);

                    $body = json_encode([
                        'success' => true,
                        'message' => 'Registration successful',
                        'redirect' => '/dashboard'
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

            // Handle regular form POST submissions (non-JSON)
            $data = $this->request->getData();
            $plainPassword = $data['password'] ?? '';
            $confirm = $data['confirmPassword'] ?? '';

            if ($plainPassword !== $confirm) {
                $this->Flash->error(__('Passwords do not match'));
            } elseif (strlen($plainPassword) < 6) {
                $this->Flash->error(__('Password must be at least 6 characters'));
            } else {
                try {
                    $usersTable = $this->fetchTable('Users');
                    $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher([
                        'hashType' => PASSWORD_ARGON2ID,
                    ]);
                    $hashed = $hasher->hash($plainPassword);

                    $user = $usersTable->newEmptyEntity();
                    $user = $usersTable->patchEntity($user, [
                        'full_name' => $data['full_name'] ?? null,
                        'username' => $data['username'] ?? null,
                        'email' => $data['email'] ?? null,
                        'password' => $hashed,
                        'password_hash' => $hashed,
                        'gender' => 'Prefer not to say',
                        'profile_photo_path' => null,
                    ]);

                    if ($usersTable->save($user)) {
                        // Auto-login and redirect to dashboard
                        $this->Authentication->setIdentity($user);
                        return $this->redirect('/dashboard');
                    }

                    $errors = $user->getErrors();
                    if (!empty($errors)) {
                        $firstField = array_key_first($errors);
                        $fieldErrors = $errors[$firstField] ?? [];
                        if (is_array($fieldErrors)) {
                            $rawMsg = reset($fieldErrors);
                        } else {
                            $rawMsg = (string)$fieldErrors;
                        }
                        $this->Flash->error($rawMsg ?: __('Registration failed'));
                    } else {
                        $this->Flash->error(__('Registration failed. Please try again.'));
                    }
                } catch (\Throwable $e) {
                    error_log('Register (form) error: ' . $e->getMessage());
                    $this->Flash->error(__('Registration failed. Please try again.'));
                }
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
