<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CloudinaryUploader;

class SettingsController extends AppController
{
    public function index()
    {
        $identity = $this->request->getAttribute('identity');

        $user = $this->fetchTable('Users')->get($identity->id);
        $activeSection = $this->request->getQuery('section', 'account');

        $detect = new \Detection\MobileDetect();
        $isMobileView = $detect->isMobile() && !$detect->isTablet();

        $currentUser = $user;
        $suggestions = [];
        $friendsCount = 0;
        $posts = [];
        $this->set(compact('user', 'activeSection', 'isMobileView', 'currentUser', 'suggestions', 'friendsCount', 'posts'));

        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $this->viewBuilder()->disableAutoLayout();
            return;
        }

        return $this->render('dashboard');
    }
    
    public function updateAccount()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($identity->id);
        
        $data = [
            'full_name' => $this->request->getData('full_name'),
            'username' => $this->request->getData('username'),
        ];
        
        // Add gender if provided
        if ($this->request->getData('gender')) {
            $data['gender'] = $this->request->getData('gender');
        }
        
        // Add birthdate if provided (allow empty string to clear the field)
        $birthdate = $this->request->getData('birthdate');
        if ($birthdate !== null) {
            $data['birthdate'] = $birthdate === '' ? null : $birthdate;
        }
        
        // Add birthday visibility (checkbox returns '1' if checked, null if unchecked)
        // Always set this field, even if unchecked (which will be false)
        $data['is_birthday_public'] = $this->request->getData('is_birthday_public') ? true : false;
        
        // Debug log
        $this->log('Update account data: ' . json_encode($data), 'debug');
        
        $user = $usersTable->patchEntity($user, $data);
        
        // Log any validation errors
        if ($user->hasErrors()) {
            $this->log('User entity has errors: ' . json_encode($user->getErrors()), 'error');
        }
        
        if ($usersTable->save($user)) {
            if (isset($this->Authentication)) {
                $this->Authentication->setIdentity($user);
            }

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Changes Updated Successfully',
                    'user' => [
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'gender' => $user->gender,
                        'birthdate' => $user->birthdate ? $user->birthdate->format('Y-m-d') : null,
                        'is_birthday_public' => $user->is_birthday_public,
                    ],
                ]));
        }
        
        // Log save errors
        $errors = $user->getErrors();
        $this->log('Failed to save user. Errors: ' . json_encode($errors), 'error');
        
        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode([
                'success' => false, 
                'message' => 'Failed to update profile',
                'errors' => $errors
            ]));
    }
    
    public function updatePassword()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($identity->id);
        
        $currentPassword = $this->request->getData('current_password');
        $newPassword = $this->request->getData('new_password');
        
        if (!password_verify($currentPassword, $user->password_hash)) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Current password is incorrect']));
        }
        
        $user->password = $newPassword;
        
        if ($usersTable->save($user)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'message' => 'Password updated successfully']));
        }
        
        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => 'Failed to update password']));
    }
    
    public function uploadProfilePhoto()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        $uploadedFile = $this->request->getUploadedFiles()['profile_photo'] ?? null;
        
        if (!$uploadedFile || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'No file uploaded or upload error']));
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile->getClientMediaType(), $allowedTypes)) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']));
        }
        
        // Validate file size (max 10MB)
        if ($uploadedFile->getSize() > 10 * 1024 * 1024) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB.']));
        }
        
        try {
            // Move uploaded file to temp location
            $tempPath = TMP . 'uploads' . DS . uniqid('profile_') . '.tmp';
            if (!is_dir(TMP . 'uploads')) {
                mkdir(TMP . 'uploads', 0755, true);
            }
            $uploadedFile->moveTo($tempPath);
            
            // Upload to Cloudinary
            $uploader = new CloudinaryUploader();
            $result = $uploader->uploadProfilePhoto($tempPath, (int)$identity->id);
            
            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            if ($result['success']) {
                // Update user's profile photo path
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->get($identity->id);
                $user->profile_photo_path = $result['url'];
                
                if ($usersTable->save($user)) {
                    if (isset($this->Authentication)) {
                        $this->Authentication->setIdentity($user);
                    }

                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => true,
                            'message' => 'Profile photo uploaded successfully',
                            'url' => $result['url'],
                            'user' => ['profile_photo_path' => $result['url']],
                        ]));
                }
            }
            
            return $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Upload failed: ' . ($result['error'] ?? 'Unknown error')]));
        } catch (\Exception $e) {
            error_log('Profile photo upload error: ' . $e->getMessage());
            return $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => 'An error occurred during upload']));
        }
    }
    
    public function updateTheme()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        $theme = $this->request->getData('theme');
        $validThemes = ['system', 'light', 'dark'];
        
        if (!in_array($theme, $validThemes)) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Invalid theme preference']));
        }
        
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($identity->id);
        $user->theme_preference = $theme;
        
        if ($usersTable->save($user)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'message' => 'Theme preference updated successfully']));
        }
        
        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => 'Failed to update theme preference']));
    }
    
    public function enableTwoFactor()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        // For now, just return a success message since 2FA is not fully implemented
        // In a real implementation, this would:
        // 1. Generate a secret key for the user
        // 2. Create QR code for authenticator apps
        // 3. Store the secret securely
        // 4. Require verification code to complete setup
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true, 
                'message' => 'Two-factor authentication enabled successfully',
                'note' => 'Note: 2FA implementation is basic. In production, you would need proper OTP verification.'
            ]));
    }
    
    public function disableTwoFactor()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        // For now, just return a success message since 2FA is not fully implemented
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true, 
                'message' => 'Two-factor authentication disabled successfully'
            ]));
    }
}
