<?php
return [
    8 => [
        'name' => 'АРМ Логопеда',
        'tableName' => 'lgp_increports',
        'columns' => [
            [
                'name' => 'docType',
                'description' => 'Тип нарушения',
                'type' => \PDO::PARAM_INT,
                'default' => 0,
                'tag' => 'select',
                'options' => [
                    0 => 'Нет нарушений',
                    1 => 'Нарушения речи',
                    2 => 'Нарушения письма',
                    3 => 'Нарушения речи и письма'
                ]
            ]
        ]
    ],
];