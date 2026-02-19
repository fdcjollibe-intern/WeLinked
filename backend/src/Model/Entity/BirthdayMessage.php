<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class BirthdayMessage extends Entity
{
    protected array $_accessible = [
        'sender_id' => true,
        'recipient_id' => true,
        'message' => true,
        'is_read' => true,
        'created_at' => true,
        'updated_at' => true,
        'deleted_at' => true,
        'sender' => true,
        'recipient' => true,
    ];
}
