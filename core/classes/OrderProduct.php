<?php

namespace WarehouseSpace\Classes;

class OrderProduct extends WarehouseObject
{
    protected $schema = [
        'ArticleDescr'  => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Quantity'      => [
            'required'  => false,
            'type'      => 'int',
        ],
        'ProductID'     => [
            'required'  => true,
            'type'      => 'string',
            'params'    => [
                'max'   => 100,
            ],
        ],
        'Article'       => [
            'required'  => false,
            'type'      => 'string',
            'params'    => [
                'max'   => 100,
            ],
        ],
    ];
    protected static $defaults = [
        'ArticleDescr'   => '',
        'Article'        => '',
    ];
}
