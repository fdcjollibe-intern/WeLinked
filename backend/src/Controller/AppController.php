<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        // Skip lightweight fragment responses
        if (!$this->viewBuilder()->isAutoLayoutEnabled()) {
            return;
        }

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return;
        }

        $existingUser = $this->viewBuilder()->hasVar('currentUser')
            ? $this->viewBuilder()->getVar('currentUser')
            : null;

        $extractValue = static function ($source, string $key) {
            if (is_array($source)) {
                return $source[$key] ?? null;
            }
            if (is_object($source) && isset($source->{$key})) {
                return $source->{$key};
            }
            return null;
        };

        $userId = $identity->id ?? $identity['id'] ?? null;
        if (!$userId && $existingUser) {
            $userId = $extractValue($existingUser, 'id');
        }
        if (!$userId) {
            return;
        }

        $fullName = $extractValue($existingUser, 'full_name') ?? $extractValue($existingUser, 'fullname');
        $photo = $extractValue($existingUser, 'profile_photo_path');
        $needsHydration = !$existingUser || empty($photo) || empty($fullName);

        $userData = $existingUser;
        if ($needsHydration) {
            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->find()
                ->select(['id', 'username', 'full_name', 'profile_photo_path'])
                ->where(['Users.id' => $userId])
                ->first();

            if ($user) {
                $userData = $user;
            }
        }

        if ($userData) {
            // Convert to stdClass to avoid dynamic property deprecation warnings on Identity/Entity objects
            if (is_object($userData) && !($userData instanceof \stdClass)) {
                if (method_exists($userData, 'toArray')) {
                    $userArray = $userData->toArray();
                } else {
                    $userArray = [
                        'id' => $userData->id ?? null,
                        'username' => $userData->username ?? null,
                        'full_name' => $userData->full_name ?? null,
                        'profile_photo_path' => $userData->profile_photo_path ?? null,
                    ];
                }
                // Mirror fullname accessor some views expect
                if (empty($userArray['fullname']) && !empty($userArray['full_name'])) {
                    $userArray['fullname'] = $userArray['full_name'];
                }
                $userData = (object) $userArray;
            } elseif (is_array($userData)) {
                // Mirror fullname accessor some views expect
                if (empty($userData['fullname']) && !empty($userData['full_name'])) {
                    $userData['fullname'] = $userData['full_name'];
                }
                $userData = (object) $userData;
            }

            $this->set('currentUser', $userData);
        }
    }
}
