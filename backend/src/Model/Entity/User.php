<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $full_name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $profile_photo_path
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'full_name' => true,
        'username' => true,
        'email' => true,
        'password' => true,
        'password_hash' => true,
        'profile_photo_path' => true,
        'created_at' => true,
        'updated_at' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
        'password_hash',
    ];

    /**
     * Automatically hash passwords when they are changed and store
     * the hashed value in the `password` column.
     */
    protected function _setPassword(?string $password): ?string
    {
        if ($password === null) {
            return null;
        }

        // If value already looks like a hash (bcrypt or argon), return it
        if (str_starts_with($password, '$2y$') || str_starts_with($password, '$argon')) {
            // Ensure legacy hashed values are kept in both columns when possible
            $this->set('password_hash', $password);
            return $password;
        }

        $hash = (new DefaultPasswordHasher([
            'hashType' => PASSWORD_ARGON2ID,
        ]))->hash($password);

        // Keep the hashed value in both `password` and `password_hash` properties
        $this->set('password_hash', $hash);
        return $hash;
    }

    protected function _getPassword(): ?string
    {
        // Safely check internal properties array (may be null in some contexts)
        if (is_array($this->_properties) && array_key_exists('password', $this->_properties) && $this->_properties['password'] !== null) {
            return $this->_properties['password'];
        }

        if (is_array($this->_properties) && array_key_exists('password_hash', $this->_properties)) {
            return $this->_properties['password_hash'];
        }

        return null;
    }
}
