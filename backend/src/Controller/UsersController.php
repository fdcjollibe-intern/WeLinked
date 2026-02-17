<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

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

    /**
     * Lightweight JSON payload describing the authenticated user.
     *
     * @return \Cake\Http\Response|null
     */
    public function currentProfile(): ?Response
    {
        $this->request->allowMethod(['get']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->response->withType('application/json')
                ->withStatus(401)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Not authenticated',
                ]));
        }

        $userId = $identity->id ?? $identity['id'] ?? null;
        if (!$userId) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Missing user id',
                ]));
        }

        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->find()
            ->select([
                'id',
                'username',
                'full_name',
                'profile_photo_path',
            ])
            ->where(['Users.id' => $userId])
            ->first();

        if (!$user) {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'User not found',
                ]));
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'profile_photo_path' => $user->profile_photo_path,
                ],
            ]));
    }
}
