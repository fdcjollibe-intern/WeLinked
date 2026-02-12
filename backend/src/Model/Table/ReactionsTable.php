<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ReactionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('reactions');
        $this->setPrimaryKey('id');
        $this->setDisplayField('reaction_type');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always'
                ]
            ]
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('reaction_type')
            ->requirePresence('reaction_type', 'create')
            ->notEmptyString('reaction_type')
            ->inList('reaction_type', ['like','haha','love','wow','sad','angry']);

        $validator
            ->scalar('target_type')
            ->requirePresence('target_type', 'create')
            ->notEmptyString('target_type');

        $validator
            ->integer('target_id')
            ->requirePresence('target_id', 'create')
            ->notEmptyString('target_id');

        return $validator;
    }
}
