<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;

class DashboardReactionsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();
    }

    // Toggle or set a reaction on a post/comment
    public function react()
    {
        $type = $this->request->getHeaderLine('Content-Type');
        if (strpos($type, 'application/json') !== false) {
            $data = json_decode((string)$this->request->getInput(), true) ?: [];
        } else {
            $data = $this->request->getData();
        }

        $targetType = $data['target_type'] ?? 'post';
        $targetId = $data['target_id'] ?? ($data['post_id'] ?? null);
        $reactionType = $data['reaction_type'] ?? $data['reaction'] ?? null;

        if (!$targetId || !$reactionType) {
            throw new BadRequestException('Missing target_id or reaction_type');
        }

        // Resolve current user id from common auth locations
        $identity = $this->request->getAttribute('identity') ?: null;
        if (!$identity && isset($this->Authentication) && method_exists($this->Authentication, 'getIdentity')) {
            $identity = $this->Authentication->getIdentity();
        }
        if (!$identity) {
            $sessionUser = $this->request->getSession()->read('Auth.User');
            $identity = $sessionUser ?: null;
        }

        $userId = null;
        if (is_object($identity)) {
            $userId = $identity->id ?? null;
        } elseif (is_array($identity)) {
            $userId = $identity['id'] ?? null;
        }

        if (!$userId) {
            $resp = ['success' => false, 'error' => 'Authentication required'];
            return $this->response->withType('application/json')->withStringBody(json_encode($resp));
        }

        // If Reactions table exists, persist changes.
        if ($this->getTableLocator()->exists('Reactions')) {
            $reactions = $this->getTableLocator()->get('Reactions');

            $existing = $reactions->find()
                ->where([
                    'user_id' => $userId,
                    'target_type' => $targetType,
                    'target_id' => $targetId
                ])->first();

            if ($existing) {
                if ($existing->reaction_type === $reactionType) {
                    // same reaction clicked -> remove (toggle off)
                    $reactions->delete($existing);
                    $action = 'removed';
                    $userReaction = null;
                } else {
                    $existing->reaction_type = $reactionType;
                    $reactions->save($existing);
                    $action = 'updated';
                    $userReaction = $reactionType;
                }
            } else {
                $entity = $reactions->newEmptyEntity();
                $entity->user_id = $userId;
                $entity->target_type = $targetType;
                $entity->target_id = $targetId;
                $entity->reaction_type = $reactionType;
                if ($reactions->save($entity)) {
                    $action = 'added';
                    $userReaction = $reactionType;
                } else {
                    $resp = ['success' => false, 'errors' => $entity->getErrors()];
                    return $this->response->withType('application/json')->withStringBody(json_encode($resp));
                }
            }

            // Build reaction counts
            $countsQuery = $reactions->find()
                ->select(['reaction_type', 'count' => 'COUNT(*)'])
                ->where(['target_type' => $targetType, 'target_id' => $targetId])
                ->group('reaction_type');

            $counts = [];
            foreach ($countsQuery as $row) {
                $counts[$row->reaction_type] = (int)$row->count;
            }

            $resp = [
                'success' => true,
                'action' => $action,
                'counts' => $counts,
                'user_reaction' => $userReaction
            ];
        } else {
            // Table not present: return a synthetic response so front-end can continue during development
            $resp = [
                'success' => true,
                'action' => 'noop',
                'counts' => [],
                'user_reaction' => $reactionType
            ];
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode($resp));
    }
}
