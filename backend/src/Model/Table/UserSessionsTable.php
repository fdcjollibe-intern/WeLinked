<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\I18n\DateTime;

/**
 * UserSessions Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserSession newEmptyEntity()
 * @method \App\Model\Entity\UserSession newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserSession> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserSession get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserSession findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserSession patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserSession> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserSession|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserSession saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserSession> saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserSession> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserSession> deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserSession> deleteManyOrFail(iterable $entities, array $options = [])
 */
class UserSessionsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_sessions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('session_id')
            ->maxLength('session_id', 40)
            ->requirePresence('session_id', 'create')
            ->notEmptyString('session_id');

        $validator
            ->scalar('websocket_id')
            ->maxLength('websocket_id', 255)
            ->allowEmptyString('websocket_id');

        $validator
            ->scalar('device_type')
            ->requirePresence('device_type', 'create')
            ->notEmptyString('device_type');

        $validator
            ->scalar('device_name')
            ->maxLength('device_name', 100)
            ->allowEmptyString('device_name');

        $validator
            ->scalar('browser_name')
            ->maxLength('browser_name', 50)
            ->allowEmptyString('browser_name');

        $validator
            ->scalar('browser_version')
            ->maxLength('browser_version', 20)
            ->allowEmptyString('browser_version');

        $validator
            ->scalar('os_name')
            ->maxLength('os_name', 50)
            ->allowEmptyString('os_name');

        $validator
            ->scalar('os_version')
            ->maxLength('os_version', 20)
            ->allowEmptyString('os_version');

        $validator
            ->scalar('ip_address')
            ->maxLength('ip_address', 45)
            ->requirePresence('ip_address', 'create')
            ->notEmptyString('ip_address');

        $validator
            ->scalar('user_agent')
            ->requirePresence('user_agent', 'create')
            ->notEmptyString('user_agent');

        $validator
            ->scalar('country')
            ->maxLength('country', 100)
            ->allowEmptyString('country');

        $validator
            ->scalar('city')
            ->maxLength('city', 100)
            ->allowEmptyString('city');

        $validator
            ->boolean('is_current')
            ->requirePresence('is_current', 'create')
            ->notEmptyString('is_current');

        $validator
            ->dateTime('last_activity')
            ->requirePresence('last_activity', 'create')
            ->notEmptyDateTime('last_activity');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Create or update a user session record
     *
     * @param int $userId User ID
     * @param string $sessionId Session ID
     * @param array $deviceInfo Device information from WhichBrowser
     * @param string $ipAddress IP address
     * @param string $userAgent User agent string
     * @return \App\Model\Entity\UserSession|false
     */
    public function createOrUpdateSession(int $userId, string $sessionId, array $deviceInfo, string $ipAddress, string $userAgent)
    {
        error_log('[UserSessionsTable] createOrUpdateSession called');
        error_log('[UserSessionsTable] - User ID: ' . $userId);
        error_log('[UserSessionsTable] - Session ID: ' . $sessionId);
        error_log('[UserSessionsTable] - IP: ' . $ipAddress);
        
        $existingSession = $this->find()
            ->where(['session_id' => $sessionId])
            ->first();

        if ($existingSession) {
            // Update existing session
            error_log('[UserSessionsTable] Found existing session, updating...');
            $existingSession = $this->patchEntity($existingSession, [
                'last_activity' => new DateTime(),
                'is_current' => true,
            ]);
            $result = $this->save($existingSession);
            error_log('[UserSessionsTable] Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        }

        // Mark all other sessions for this user as not current
        $this->updateAll(
            ['is_current' => false],
            ['user_id' => $userId, 'is_current' => true]
        );

        // Create new session
        $sessionData = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'device_type' => $deviceInfo['device_type'] ?? 'desktop',
            'device_name' => $deviceInfo['device_name'] ?? null,
            'browser_name' => $deviceInfo['browser_name'] ?? null,
            'browser_version' => $deviceInfo['browser_version'] ?? null,
            'os_name' => $deviceInfo['os_name'] ?? null,
            'os_version' => $deviceInfo['os_version'] ?? null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'is_current' => true,
            'last_activity' => new DateTime(),
        ];

        error_log('[UserSessionsTable] Creating NEW session record');
        error_log('[UserSessionsTable] Session data: ' . json_encode($sessionData));
        
        $session = $this->newEntity($sessionData);
        
        if ($session->hasErrors()) {
            error_log('[UserSessionsTable] ✗ Validation errors: ' . json_encode($session->getErrors()));
        }
        
        $result = $this->save($session);
        
        if ($result) {
            error_log('[UserSessionsTable] ✓ Session saved successfully with ID: ' . $result->id);
        } else {
            error_log('[UserSessionsTable] ✗ Failed to save session');
            if ($session->hasErrors()) {
                error_log('[UserSessionsTable] Errors: ' . json_encode($session->getErrors()));
            }
        }
        
        return $result;
    }

    /**
     * Get user's active sessions
     *
     * @param int $userId User ID
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findActiveByUser(int $userId): SelectQuery
    {
        return $this->find()
            ->where(['user_id' => $userId])
            ->orderBy(['last_activity' => 'DESC']);
    }

    /**
     * Invalidate user session
     *
     * @param int $userId User ID
     * @param string|null $sessionId Optional session ID to invalidate specific session
     * @return int Number of sessions deleted
     */
    public function invalidateSessions(int $userId, ?string $sessionId = null): int
    {
        error_log('[UserSessionsTable] invalidateSessions called');
        error_log('[UserSessionsTable] - User ID: ' . $userId);
        error_log('[UserSessionsTable] - Session ID: ' . ($sessionId ?? 'ALL'));
        
        $conditions = ['user_id' => $userId];
        if ($sessionId) {
            $conditions['session_id'] = $sessionId;
        }
        
        // Check how many sessions match before deleting
        $count = $this->find()->where($conditions)->count();
        error_log('[UserSessionsTable] Found ' . $count . ' session(s) to delete');
        
        $deleted = $this->deleteAll($conditions);
        error_log('[UserSessionsTable] Actually deleted: ' . $deleted . ' session(s)');
        
        // Verify deletion
        $remaining = $this->find()->where($conditions)->count();
        error_log('[UserSessionsTable] Remaining after delete: ' . $remaining . ' session(s)');
        
        return $deleted;
    }

    /**
     * Update WebSocket ID for a session
     *
     * @param string $sessionId Session ID
     * @param string $websocketId WebSocket ID
     * @return bool Success
     */
    public function updateWebSocketId(string $sessionId, string $websocketId): bool
    {
        return $this->updateAll(
            ['websocket_id' => $websocketId],
            ['session_id' => $sessionId]
        ) > 0;
    }

    /**
     * Clean up old sessions (older than 30 days)
     *
     * @return int Number of sessions deleted
     */
    public function cleanupOldSessions(): int
    {
        $cutoffDate = (new DateTime())->modify('-30 days');
        
        return $this->deleteAll([
            'last_activity <' => $cutoffDate
        ]);
    }
}