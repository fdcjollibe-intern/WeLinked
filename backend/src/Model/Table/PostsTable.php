<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class PostsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('posts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

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
            'joinType' => 'INNER',
        ]);

        $this->hasMany('Reactions', [
            'foreignKey' => 'target_id',
            'conditions' => ['Reactions.target_type' => 'post'],
            'dependent' => true,
        ]);

        $this->hasMany('Mentions', [
            'foreignKey' => 'post_id',
            'dependent' => true,
        ]);
    }
}
