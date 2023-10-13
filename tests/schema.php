<?php
declare(strict_types=1);

return [
    [
        'table' => 'files',
        'columns' => [
            'id' => ['type' => 'integer'],
            'filename' => ['type' => 'string'],
            'created' => ['type' => 'datetime', 'null' => true],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
];
