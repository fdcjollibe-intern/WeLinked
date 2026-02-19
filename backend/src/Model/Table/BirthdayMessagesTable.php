<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class BirthdayMessagesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('birthday_messages');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always'
                ]
            ]
        ]);

        // Associations
        $this->belongsTo('Senders', [
            'className' => 'Users',
            'foreignKey' => 'sender_id'
        ]);

        $this->belongsTo('Recipients', [
            'className' => 'Users',
            'foreignKey' => 'recipient_id'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('sender_id')
            ->requirePresence('sender_id', 'create')
            ->notEmptyString('sender_id');

        $validator
            ->integer('recipient_id')
            ->requirePresence('recipient_id', 'create')
            ->notEmptyString('recipient_id');

        $validator
            ->scalar('message')
            ->maxLength('message', 500, 'Message cannot exceed 500 characters')
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

        $validator
            ->boolean('is_read')
            ->notEmptyString('is_read');

        return $validator;
    }
}
