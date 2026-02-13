<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class MentionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('mentions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Associations
        $this->belongsTo('Posts', [
            'foreignKey' => 'post_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('MentionedUsers', [
            'className' => 'Users',
            'foreignKey' => 'mentioned_user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('MentionedByUsers', [
            'className' => 'Users',
            'foreignKey' => 'mentioned_by_user_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('post_id')
            ->requirePresence('post_id', 'create')
            ->notEmptyString('post_id');

        $validator
            ->nonNegativeInteger('mentioned_user_id')
            ->requirePresence('mentioned_user_id', 'create')
            ->notEmptyString('mentioned_user_id');

        $validator
            ->nonNegativeInteger('mentioned_by_user_id')
            ->requirePresence('mentioned_by_user_id', 'create')
            ->notEmptyString('mentioned_by_user_id');

        return $validator;
    }
}
