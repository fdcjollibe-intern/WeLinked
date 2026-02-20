<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserSession Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $session_id
 * @property string|null $websocket_id
 * @property string $device_type
 * @property string|null $device_name
 * @property string|null $browser_name
 * @property string|null $browser_version
 * @property string|null $os_name
 * @property string|null $os_version
 * @property string $ip_address
 * @property string $user_agent
 * @property string|null $country
 * @property string|null $city
 * @property bool $is_current
 * @property \Cake\I18n\DateTime $last_activity
 * @property \Cake\I18n\DateTime $created_at
 *
 * @property \App\Model\Entity\User $user
 */
class UserSession extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'session_id' => true,
        'websocket_id' => true,
        'device_type' => true,
        'device_name' => true,
        'browser_name' => true,
        'browser_version' => true,
        'os_name' => true,
        'os_version' => true,
        'ip_address' => true,
        'user_agent' => true,
        'country' => true,
        'city' => true,
        'is_current' => true,
        'last_activity' => true,
        'user' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'user_agent',
        'session_id',
    ];
}