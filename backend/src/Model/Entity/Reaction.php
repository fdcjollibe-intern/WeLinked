<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Reaction extends Entity
{
    protected array $_accessible = [
        'target_type' => true,
        'target_id' => true,
        'reaction_type' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
    ];
}
