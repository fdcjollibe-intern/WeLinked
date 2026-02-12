<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;

class DashboardPostsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->request->allowMethod(['post', 'get']);
        $this->viewBuilder()->disableAutoLayout();
    }

    // Create a post. Expects JSON body: { body: string, attachments: [url,...] }
    public function create()
    {
        $data = [];
        $type = $this->request->getHeaderLine('Content-Type');
        if (strpos($type, 'application/json') !== false) {
            $data = json_decode((string)$this->request->getInput(), true) ?: [];
        } else {
            $data = $this->request->getData();
        }

        $body = $data['body'] ?? '';
        $attachments = $data['attachments'] ?? [];

        // If Posts table exists, persist; otherwise return a synthetic response
        if ($this->getTableLocator()->exists('Posts')) {
            $posts = $this->getTableLocator()->get('Posts');
            $entity = $posts->newEmptyEntity();
            $entity->body = $body;
            $entity->attachments = json_encode($attachments);
            $identity = $this->request->getAttribute('identity');
            if ($identity) {
                $entity->user_id = $identity->id ?? null;
            }
            if ($posts->save($entity)) {
                $resp = ['success' => true, 'post' => $entity];
            } else {
                $resp = ['success' => false, 'errors' => $entity->getErrors()];
            }
        } else {
            // Synthetic response for dev
            $resp = [
                'success' => true,
                'post' => [
                    'id' => uniqid('p_'),
                    'body' => $body,
                    'attachments' => $attachments,
                    'created' => date('c'),
                    'user' => [ 'username' => $this->request->getAttribute('identity')->username ?? 'you' ]
                ]
            ];
        }

        $this->response = $this->response->withType('application/json')
            ->withStringBody(json_encode($resp));
        return $this->response;
    }
}
