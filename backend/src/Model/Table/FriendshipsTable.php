<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Validation\Validator;

/**
 * Friendships Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Followers
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Following
 */
class FriendshipsTable extends Table {
    use LocatorAwareTrait;
    
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('friendships');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Followers', [
            'className' => 'Users',
            'foreignKey' => 'follower_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Following', [
            'className' => 'Users',
            'foreignKey' => 'following_id',
            'joinType' => 'INNER'
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
            ->nonNegativeInteger('follower_id')
            ->requirePresence('follower_id', 'create')
            ->notEmptyString('follower_id');

        $validator
            ->nonNegativeInteger('following_id')
            ->requirePresence('following_id', 'create')
            ->notEmptyString('following_id');

        return $validator;
    }

    /**
     * Get friends count for a user
     *
     * @param int $userId User ID
     * @return int
     */
    public function getFriendsCount(int $userId): int
    {
        return $this->find()
            ->where(['follower_id' => $userId])
            ->count();
    }

    /**
     * Get all friends for a user
     *
     * @param int $userId User ID
     * @return \Cake\ORM\Query
     */
    public function getFriends(int $userId)
    {
        return $this->find()
            ->contain(['Following'])
            ->where(['follower_id' => $userId])
            ->orderBy(['Friendships.created_at' => 'DESC']);
    }

    /**
     * Get friend suggestions for a user (users not already followed)
     * Prioritizes users with mutual friends
     *
     * @param int $userId User ID
     * @param int $limit Limit number of suggestions
     * @return array
     */
    public function getSuggestions(int $userId, int $limit = 5): array
    {
        $usersTable = $this->fetchTable('Users');
        
        // Get IDs of users already followed by this user
        $followingIds = $this->find()
            ->select(['following_id'])
            ->where(['follower_id' => $userId])
            ->all()
            ->extract('following_id')
            ->toArray();
        

            

        $followingIds[] = $userId; // Exclude self
        
        // Get potential suggestions with mutual friends count
        $suggestions = $usersTable->find()
            ->select([
                'Users.id',
                'Users.username',
                'Users.full_name',
                'Users.profile_photo_path',
                'mutual_count' => $this->find()
                    ->select(['count' => $this->find()->func()->count('*')])
                    ->where([
                        'Friendships.follower_id IN' => $followingIds,
                        'Friendships.following_id = Users.id'
                    ])
            ])
            ->where(['Users.id NOT IN' => $followingIds])
            ->orderBy([
                'mutual_count' => 'DESC',
                'Users.created_at' => 'DESC'
            ])
            ->limit($limit)
            ->toArray();
        
        return $suggestions;
    }

    /**
     * Get mutual friends count between two users
     *
     * @param int $userId1 First user ID
     * @param int $userId2 Second user ID
     * @return int
     */
    public function getMutualFriendsCount(int $userId1, int $userId2): int
    {
        // Get user1's following
        $user1Following = $this->find()
            ->select(['following_id'])
            ->where(['follower_id' => $userId1])
            ->all()
            ->extract('following_id')
            ->toArray();
        
        if (empty($user1Following)) {
            return 0;
        }
        
        // Count how many of those user2 is also following
        return $this->find()
            ->where([
                'follower_id' => $userId2,
                'following_id IN' => $user1Following
            ])
            ->count();
    }

    /**
     * Check if user1 is following user2
     *
     * @param int $followerId Follower user ID
     * @param int $followingId Following user ID
     * @return bool
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        return $this->exists([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
    }

    /**
     * Follow a user
     *
     * @param int $followerId Follower user ID
     * @param int $followingId Following user ID
     * @return \Cake\Datasource\EntityInterface|false
     */
    public function follow(int $followerId, int $followingId)
    {
        // Check if already following
        if ($this->isFollowing($followerId, $followingId)) {
            return false;
        }
        
        $friendship = $this->newEntity([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
        
        return $this->save($friendship);
    }

    /**
     * Unfollow a user
     *
     * @param int $followerId Follower user ID
     * @param int $followingId Following user ID
     * @return bool
     */
    public function unfollow(int $followerId, int $followingId): bool
    {
        $deleted = $this->deleteAll([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
        
        return (bool)$deleted;
    }

    /**
     * Get all followers for a user (people who follow this user)
     *
     * @param int $userId User ID
     * @return \Cake\ORM\Query
     */
    public function getFollowers(int $userId)
    {
        return $this->find()
            ->contain(['Followers'])
            ->where(['following_id' => $userId])
            ->orderBy(['Friendships.created_at' => 'DESC']);
    }

    /**
     * Get followers count for a user (people who follow this user)
     *
     * @param int $userId User ID
     * @return int
     */
    public function getFollowersCount(int $userId): int
    {
        return $this->find()
            ->where(['following_id' => $userId])
            ->count();
    }

    /**
     * Get following count for a user (people this user follows)
     *
     * @param int $userId User ID
     * @return int
     */
    public function getFollowingCount(int $userId): int
    {
        return $this->find()
            ->where(['follower_id' => $userId])
            ->count();
    }
}
