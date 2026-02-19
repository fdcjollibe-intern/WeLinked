<?php
declare(strict_types=1);

namespace App\Controller;

class NotificationsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
    }

    /**
     * Get user's notifications
     */
    public function index()
    {
        $this->request->allowMethod(['get']);
        
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $notificationsTable = $this->fetchTable('Notifications');
            
            $notifications = $notificationsTable->find()
                ->contain(['Actors' => ['fields' => ['id', 'username', 'full_name', 'profile_photo_path']]])
                ->where(['Notifications.user_id' => $identity->id])
                ->orderBy(['Notifications.created_at' => 'DESC'])
                ->limit(50)
                ->toArray();
            
            $formattedNotifications = array_map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at ? $notification->created_at->format('c') : null,
                    'actor' => $notification->actor ? [
                        'id' => $notification->actor->id,
                        'username' => $notification->actor->username,
                        'full_name' => $notification->actor->full_name,
                        'profile_photo' => $notification->actor->profile_photo_path,
                    ] : null,
                    'target' => [
                        'type' => $notification->target_type,
                        'id' => $notification->target_id,
                    ],
                ];
            }, $notifications);
            
            return $this->jsonResponse([
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $notificationsTable->getUnreadCount($identity->id)
            ]);
        } catch (\Exception $e) {
            error_log('Notifications fetch error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function markAsRead($id = null)
    {
        $this->request->allowMethod(['post', 'put']);
        
        if (!$id) {
            return $this->jsonResponse(['success' => false, 'message' => 'Notification ID required'], 400);
        }

        $identity = $this->request->getAttribute('identity');
        
        try {
            $notificationsTable = $this->fetchTable('Notifications');
            $notification = $notificationsTable->find()
                ->where(['id' => $id, 'user_id' => $identity->id])
                ->first();
            
            if (!$notification) {
                return $this->jsonResponse(['success' => false, 'message' => 'Notification not found'], 404);
            }
            
            if ($notificationsTable->markAsRead((int)$id)) {
                return $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update notification'], 400);
        } catch (\Exception $e) {
            error_log('Mark notification error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }


    public function markAllAsRead()
    {
        $this->request->allowMethod(['post']);
        
        $identity = $this->request->getAttribute('identity');
        
        try {
            $notificationsTable = $this->fetchTable('Notifications');
            
            if ($notificationsTable->markAllAsRead($identity->id)) {
                return $this->jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update notifications'], 400);
        } catch (\Exception $e) {
            error_log('Mark all notifications error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

   
    public function unreadCount()
    {
        $this->request->allowMethod(['get']);
        
        $identity = $this->request->getAttribute('identity');
        
        try {
            $notificationsTable = $this->fetchTable('Notifications');
            $count = $notificationsTable->getUnreadCount($identity->id);
            
            return $this->jsonResponse([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            error_log('Unread count error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

  
    private function jsonResponse(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}
