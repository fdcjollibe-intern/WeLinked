<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\Date;

class BirthdaysController extends AppController
{
    /**
     * Get birthdays within the specified range
     * Returns upcoming birthdays (7 days) and past birthdays (3 days)
     */
    public function index()
    {
        $this->request->allowMethod(['get']);
        
        $today = Date::now();
        
        // Calculate date ranges
        $pastDaysAgo = $today->subDays(3);
        $upcomingDaysAhead = $today->addDays(7);
        
        $usersTable = $this->fetchTable('Users');
        
        // Get all users with public birthdays
        $users = $usersTable->find()
            ->where([
                'is_birthday_public' => true,
                'birthdate IS NOT' => null
            ])
            ->select(['id', 'full_name', 'username', 'profile_photo_path', 'birthdate'])
            ->toArray();
        
        // Categorize birthdays
        $upcomingBirthdays = [];
        $pastBirthdays = [];
        $todayBirthdays = [];
        
        foreach ($users as $user) {
            if (!$user->birthdate) {
                continue;
            }
            
            // Create a birthday date for this year
            $birthdayThisYear = Date::create(
                $today->year,
                $user->birthdate->month,
                $user->birthdate->day
            );
            
            // Calculate age
            $age = $today->year - $user->birthdate->year;
            
            // Check if birthday already passed this year, if so check next year too
            $birthdayNextYear = $birthdayThisYear->addYears(1);
            
            // Today's birthday
            if ($birthdayThisYear->isSameDay($today)) {
                $todayBirthdays[] = [
                    'user' => $user,
                    'age' => $age,
                    'date' => $birthdayThisYear,
                    'daysAway' => 0
                ];
            }
            // Past birthdays (3 days ago to yesterday)
            elseif ($birthdayThisYear->between($pastDaysAgo, $today->subDay())) {
                $daysAgo = $today->diffInDays($birthdayThisYear, false);
                $pastBirthdays[] = [
                    'user' => $user,
                    'age' => $age,
                    'date' => $birthdayThisYear,
                    'daysAgo' => abs($daysAgo)
                ];
            }
            // Upcoming birthdays (tomorrow to 7 days ahead)
            elseif ($birthdayThisYear->between($today->addDay(), $upcomingDaysAhead)) {
                $daysAway = $birthdayThisYear->diffInDays($today, false);
                $upcomingBirthdays[] = [
                    'user' => $user,
                    'age' => $age,
                    'date' => $birthdayThisYear,
                    'daysAway' => abs($daysAway)
                ];
            }
            // Check if birthday is in next year's range
            elseif ($birthdayNextYear->between($today->addDay(), $upcomingDaysAhead)) {
                $daysAway = $birthdayNextYear->diffInDays($today, false);
                $upcomingBirthdays[] = [
                    'user' => $user,
                    'age' => $age + 1,
                    'date' => $birthdayNextYear,
                    'daysAway' => abs($daysAway)
                ];
            }
        }
        
        // Sort upcoming by days away (closest first)
        usort($upcomingBirthdays, function($a, $b) {
            return $a['daysAway'] <=> $b['daysAway'];
        });
        
        // Sort past by days ago (most recent first)
        usort($pastBirthdays, function($a, $b) {
            return $a['daysAgo'] <=> $b['daysAgo'];
        });
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'today' => $todayBirthdays,
                'upcoming' => $upcomingBirthdays,
                'past' => $pastBirthdays,
                'total' => count($todayBirthdays) + count($upcomingBirthdays) + count($pastBirthdays)
            ]));
    }
    
    /**
     * Get count of birthdays for quick display in right sidebar
     */
    public function getCount()
    {
        $this->request->allowMethod(['get']);
        
        $today = Date::now();
        $upcomingDaysAhead = $today->addDays(7);
        
        $usersTable = $this->fetchTable('Users');
        
        // Get all users with public birthdays
        $users = $usersTable->find()
            ->where([
                'is_birthday_public' => true,
                'birthdate IS NOT' => null
            ])
            ->select(['birthdate'])
            ->toArray();
        
        $count = 0;
        foreach ($users as $user) {
            if (!$user->birthdate) {
                continue;
            }
            
            $birthdayThisYear = Date::create(
                $today->year,
                $user->birthdate->month,
                $user->birthdate->day
            );
            
            $birthdayNextYear = $birthdayThisYear->addYears(1);
            
            // Count if birthday is today or within upcoming 7 days
            if ($birthdayThisYear->between($today, $upcomingDaysAhead) || 
                $birthdayNextYear->between($today, $upcomingDaysAhead)) {
                $count++;
            }
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'count' => $count
            ]));
    }
    
    /**
     * Get follower birthdays for right sidebar display
     */
    public function getSidebarData()
    {
        $this->request->allowMethod(['get']);
        
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'today' => [],
                    'upcoming' => [],
                    'past' => [],
                    'total' => 0
                ]));
        }
        
        $birthdays = $this->getFollowerBirthdays($currentUserId);
        
        // Separate today's birthdays from upcoming ones
        $todayBirthdays = [];
        $upcomingBirthdays = [];
        
        foreach ($birthdays['upcoming'] as $birthday) {
            if ($birthday['daysAway'] == 0) {
                $todayBirthdays[] = $birthday;
            } else {
                $upcomingBirthdays[] = $birthday;
            }
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'today' => $todayBirthdays,
                'upcoming' => $upcomingBirthdays,
                'past' => $birthdays['past'] ?? [],
                'total' => count($todayBirthdays) + count($upcomingBirthdays) + count($birthdays['past'])
            ]));
    }
    
    /**
     * Birthday list page - shows followers' birthdays
     */
    public function list()
    {
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->redirect(['controller' => 'Login', 'action' => 'index']);
        }

        // Handle AJAX requests - return only the middle column content
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $birthdays = $this->getFollowerBirthdays($currentUserId);
            $year = $this->request->getQuery('year', date('Y'));
            $this->set(compact('birthdays', 'year'));
            $this->viewBuilder()->disableAutoLayout();
            return;
        }

        $currentUser = (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? 'User',
            'full_name' => $identity->full_name ?? 'Full Name',
            'profile_photo_path' => $identity->profile_photo_path ?? ''
        ];

        $birthdays = $this->getFollowerBirthdays($currentUserId);
        $year = $this->request->getQuery('year', date('Y'));
        
        // Get friends count and suggestions for sidebars
        $friendshipsTable = $this->fetchTable('Friendships');
        $friendsCount = $friendshipsTable->getFriendsCount($currentUserId);
        $friendSuggestions = $friendshipsTable->getSuggestions($currentUserId, 6);
        
        $suggestions = [];
        foreach ($friendSuggestions as $user) {
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
            $suggestions[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo_path' => $user->profile_photo_path,
                'mutual_count' => $mutualCount
            ];
        }
        
        $detect = new \Detection\MobileDetect();
        $isMobileView = $detect->isMobile() && !$detect->isTablet();
        
        $posts = [];
        $activeSection = 'birthday-list';
        
        $this->set(compact('birthdays', 'currentUser', 'isMobileView', 'suggestions', 'friendsCount', 'posts', 'activeSection', 'year'));
        $this->viewBuilder()->setLayout('dashboard');
    }
    
    /**
     * Sent messages page
     */
    public function sent()
    {
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->redirect(['controller' => 'Login', 'action' => 'index']);
        }

        // Handle AJAX requests - return only the middle column content
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $messagesTable = $this->fetchTable('BirthdayMessages');
            $messages = $messagesTable->find()
                ->contain(['Recipients' => function ($q) {
                    return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
                }])
                ->where([
                    'BirthdayMessages.sender_id' => $currentUserId,
                    'BirthdayMessages.deleted_at IS' => null
                ])
                ->orderBy(['BirthdayMessages.created_at' => 'DESC'])
                ->all();
            $this->set(compact('messages'));
            $this->viewBuilder()->disableAutoLayout();  
            return;
        }

        $currentUser = (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? 'User',
            'full_name' => $identity->full_name ?? 'Full Name',
            'profile_photo_path' => $identity->profile_photo_path ?? ''
        ];

        $messagesTable = $this->fetchTable('BirthdayMessages');
        $messages = $messagesTable->find()
            ->contain(['Recipients' => function ($q) {
                return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
            }])
            ->where([
                'BirthdayMessages.sender_id' => $currentUserId,
                'BirthdayMessages.deleted_at IS' => null
            ])
            ->orderBy(['BirthdayMessages.created_at' => 'DESC'])
            ->all();
        
        // Get friends count and suggestions for sidebars
        $friendshipsTable = $this->fetchTable('Friendships');
        $friendsCount = $friendshipsTable->getFriendsCount($currentUserId);
        $friendSuggestions = $friendshipsTable->getSuggestions($currentUserId, 6);
        
        $suggestions = [];
        foreach ($friendSuggestions as $user) {
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
            $suggestions[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo_path' => $user->profile_photo_path,
                'mutual_count' => $mutualCount
            ];
        }
        
        $detect = new \Detection\MobileDetect();
        $isMobileView = $detect->isMobile() && !$detect->isTablet();
        
        $posts = [];
        $activeSection = 'birthday-sent';
        
        $this->set(compact('messages', 'currentUser', 'isMobileView', 'suggestions', 'friendsCount', 'posts', 'activeSection'));
        $this->viewBuilder()->setLayout('dashboard');
    }
    public function messages()
    {
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->redirect(['controller' => 'Login', 'action' => 'index']);
        }

        // Handle AJAX requests - return only the middle column content
        if ($this->request->is('ajax') || $this->request->getQuery('partial')) {
            $messagesTable = $this->fetchTable('BirthdayMessages');
            $messages = $messagesTable->find()
                ->contain(['Senders' => function ($q) {
                    return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
                }])
                ->where([
                    'BirthdayMessages.recipient_id' => $currentUserId,
                    'BirthdayMessages.deleted_at IS' => null
                ])
                ->orderBy(['BirthdayMessages.created_at' => 'DESC'])
                ->all();
            
            $this->set(compact('messages'));
            $this->viewBuilder()->disableAutoLayout();
            return;
        }

        $currentUser = (object)[
            'id' => $currentUserId,
            'username' => $identity->username ?? 'User',
            'full_name' => $identity->full_name ?? 'Full Name',
            'profile_photo_path' => $identity->profile_photo_path ?? ''
        ];

        $messagesTable = $this->fetchTable('BirthdayMessages');
        $messages = $messagesTable->find()
            ->contain(['Senders' => function ($q) {
                return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
            }])
            ->where([
                'BirthdayMessages.recipient_id' => $currentUserId,
                'BirthdayMessages.deleted_at IS' => null
            ])
            ->orderBy(['BirthdayMessages.created_at' => 'DESC'])
            ->all();
        
        // Get friends count and suggestions for sidebars
        $friendshipsTable = $this->fetchTable('Friendships');
        $friendsCount = $friendshipsTable->getFriendsCount($currentUserId);
        $friendSuggestions = $friendshipsTable->getSuggestions($currentUserId, 6);
        
        $suggestions = [];
        foreach ($friendSuggestions as $user) {
            $mutualCount = $friendshipsTable->getMutualFriendsCount($currentUserId, $user->id);
            $suggestions[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo_path' => $user->profile_photo_path,
                'mutual_count' => $mutualCount
            ];
        }
        
        $detect = new \Detection\MobileDetect();
        $isMobileView = $detect->isMobile() && !$detect->isTablet();
        
        $posts = [];
        $activeSection = 'birthday-messages';
        
        $this->set(compact('messages', 'currentUser', 'isMobileView', 'suggestions', 'friendsCount', 'posts', 'activeSection'));
        $this->viewBuilder()->setLayout('dashboard');
    }
    
    /**
     * Check if current user has birthday today
     */
    public function checkToday()
    {
        $this->request->allowMethod(['get']);
        
        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'is_birthday' => false]));
        }
        
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($currentUserId);
        
        if (!$user->birthdate) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'is_birthday' => false]));
        }
        
        $today = Date::now();
        $isBirthday = $user->birthdate->month == $today->month && $user->birthdate->day == $today->day;
        
        $age = null;
        if ($isBirthday) {
            $age = $today->year - $user->birthdate->year;
        }
        
        $messagesTable = $this->fetchTable('BirthdayMessages');
        $unreadCount = $messagesTable->find()
            ->where(['recipient_id' => $currentUserId, 'is_read' => false, 'deleted_at IS' => null])
            ->count();
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'is_birthday' => $isBirthday,
                'age' => $age,
                'full_name' => $user->full_name,
                'unread_count' => $unreadCount
            ]));
    }
    
    /**
     * Get unread birthday message count for sidebar badge
     */
    public function getMessageCount()
    {
        $this->request->allowMethod(['get']);
        
        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'count' => 0]));
        }
        
        $messagesTable = $this->fetchTable('BirthdayMessages');
        $unreadCount = $messagesTable->find()
            ->where(['recipient_id' => $currentUserId, 'is_read' => false, 'deleted_at IS' => null])
            ->count();
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'count' => $unreadCount
            ]));
    }
    
    /**
     * Mark a specific birthday message as read
     */
    public function markAsRead($id = null)
    {
        $this->autoRender = false;
        $this->request->allowMethod(['post']);
        
        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->response->withType('application/json')
                ->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Unauthorized']));
        }
        
        if (!$id) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Message ID is required']));
        }
        
        $messagesTable = $this->fetchTable('BirthdayMessages');
        $message = $messagesTable->find()
            ->where(['id' => $id, 'recipient_id' => $currentUserId, 'deleted_at IS' => null])
            ->first();
        
        if (!$message) {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Message not found']));
        }
        
        // Only update if not already read
        if (!$message->is_read) {
            $message->is_read = true;
            if ($messagesTable->save($message)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['success' => true, 'message' => 'Message marked as read']));
            }
            
            return $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Failed to mark message as read']));
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'message' => 'Message already read']));
    }
    
    /**
     * Send a birthday message
     */
    public function sendMessage()
    {
        $this->autoRender = false;
        $this->request->allowMethod(['post']);
        
        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();
        
        if (!$currentUserId) {
            return $this->response->withType('application/json')
                ->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Unauthorized']));
        }
        
        $recipientId = $this->request->getData('recipient_id');
        $message = $this->request->getData('message');
        
        if (!$recipientId || !$message) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Missing required fields']));
        }
        
        $messagesTable = $this->fetchTable('BirthdayMessages');
        $birthdayMessage = $messagesTable->newEntity([
            'sender_id' => $currentUserId,
            'recipient_id' => $recipientId,
            'message' => $message
        ]);
        
        if ($messagesTable->save($birthdayMessage)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'message' => 'Birthday message sent successfully']));
        }
        
        return $this->response->withType('application/json')
            ->withStatus(500)
            ->withStringBody(json_encode(['success' => false, 'message' => 'Failed to send message']));
    }
    
    /**
     * Edit a birthday message (only sender can edit)
     */
    public function editMessage($id = null)
    {
        $this->autoRender = false;
        $this->request->allowMethod(['put', 'post']);
        
        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();
        
        if (!$currentUserId || !$id) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Invalid request']));
        }
        
        $messagesTable = $this->fetchTable('BirthdayMessages');
        $message = $messagesTable->find()
            ->where(['id' => $id, 'sender_id' => $currentUserId, 'deleted_at IS' => null])
            ->first();
        
        if (!$message) {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Message not found']));
        }
        
        // Check if message was sent within last 5 minutes
        $now = new \DateTime();
        $createdAt = $message->created_at;
        $diffInMinutes = ($now->getTimestamp() - $createdAt->getTimestamp()) / 60;
        
        if ($diffInMinutes > 5) {
            return $this->response->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Cannot edit message after 5 minutes']));
        }
        
        $newMessage = $this->request->getData('message');
        if (!$newMessage) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Message content is required']));
        }
        
        $message->message = $newMessage;
        
        if ($messagesTable-> save($message)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'message' => 'Message updated successfully']));
        }
        
        return $this->response->withType('application/json')
            ->withStatus(500)
            ->withStringBody(json_encode(['success' => false, 'message' => 'Failed to update message']));
    }
    
    /**
     * Delete a birthday message
     */
    public function deleteMessage($id = null)
    {
        $this->autoRender = false;
        $this->request->allowMethod(['delete', 'post']);
        
        $currentUser = $this->request->getAttribute('identity');
        $currentUserId = $currentUser?->getIdentifier();
        
        if (!$currentUserId || !$id) {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Invalid request']));
        }
        
        $messagesTable = $this->fetchTable('BirthdayMessages');
        $message = $messagesTable->find()
            ->where(['id' => $id, 'sender_id' => $currentUserId, 'deleted_at IS' => null])
            ->first();
        
        if (!$message) {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Message not found']));
        }
        
        // Check if message was sent within last 5 minutes
        $now = new \DateTime();
        $createdAt = $message->created_at;
        $diffInMinutes = ($now->getTimestamp() - $createdAt->getTimestamp()) / 60;
        
        if ($diffInMinutes > 5) {
            return $this->response->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Cannot delete message after 5 minutes']));
        }
        
        $message->deleted_at = new \DateTime();
        
        if ($messagesTable->save($message)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'message' => 'Message deleted successfully']));
        }
        
        return $this->response->withType('application/json')
            ->withStatus(500)
            ->withStringBody(json_encode(['success' => false, 'message' => 'Failed to delete message']));
    }
    
    /**
     * Helper: Get follower birthdays
     */
    private function getFollowerBirthdays($userId)
    {
        $today = Date::now();
        $pastDaysAgo = $today->subDays(3);
        $upcomingDaysAhead = $today->addDays(7);
        
        $friendshipsTable = $this->fetchTable('Friendships');
        $friendIds = $friendshipsTable->find()
            ->select(['following_id'])
            ->where(['follower_id' => $userId])
            ->all()
            ->extract('following_id')
            ->toList();
        
        if (empty($friendIds)) {
            return ['upcoming' => [], 'past' => []];
        }
        
        $usersTable = $this->fetchTable('Users');
        $users = $usersTable->find()
            ->where([
                'id IN' => $friendIds,
                'birthdate IS NOT' => null,
                'is_birthday_public' => true
            ])
            ->select(['id', 'full_name', 'username', 'profile_photo_path', 'birthdate', 'is_birthday_public'])
            ->toArray();
        
        $upcomingBirthdays = [];
        $pastBirthdays = [];
        
        foreach ($users as $user) {
            if (!$user->birthdate) continue;
            
            $birthdayThisYear = Date::create($today->year, $user->birthdate->month, $user->birthdate->day);
            $age = $today->year - $user->birthdate->year;
            $birthdayNextYear = $birthdayThisYear->addYears(1);
            
            if ($birthdayThisYear->between($today, $upcomingDaysAhead)) {
                $daysAway = $birthdayThisYear->diffInDays($today, false);
                $upcomingBirthdays[] = ['user' => $user, 'age' => $age, 'date' => $birthdayThisYear, 'daysAway' => abs($daysAway)];
            }
            elseif ($birthdayNextYear->between($today, $upcomingDaysAhead)) {
                $daysAway = $birthdayNextYear->diffInDays($today, false);
                $upcomingBirthdays[] = ['user' => $user, 'age' => $age + 1, 'date' => $birthdayNextYear, 'daysAway' => abs($daysAway)];
            }
            elseif ($birthdayThisYear->between($pastDaysAgo, $today->subDays(1))) {
                $daysAgo = $today->diffInDays($birthdayThisYear, false);
                $pastBirthdays[] = ['user' => $user, 'age' => $age, 'date' => $birthdayThisYear, 'daysAgo' => abs($daysAgo)];
            }
        }
        
        usort($upcomingBirthdays, fn($a, $b) => $a['daysAway'] <=> $b['daysAway']);
        usort($pastBirthdays, fn($a, $b) => $a['daysAgo'] <=> $b['daysAgo']);
        
        return ['upcoming' => $upcomingBirthdays, 'past' => $pastBirthdays];
    }
}
