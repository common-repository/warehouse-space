<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Controllers\MainController;

class Product extends WarehouseObject
{
    protected $schema = [
        'AccountKey'    => [
            'required'  => true,
            'type'      => 'string',
            'params'    => [
                'notEmpty'   => true,
            ],
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
        'Barcode'       => [
            'required'  => false,
            'type'      => 'string'
        ],
        'BuyPrice'      => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'Category'      => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Description'   => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Title'   => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ErpTimeStamp'  => [
            'required'  => false,
            'type'      => 'datetime',
        ],
        'Images'        => [
            'required'  => false,
            'type'      => 'array',
            'params'    => [
                'type'  => 'string',
            ],
        ],
        'ItemDepth'     => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'ItemHeight'    => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'ItemWeight'    => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'ItemWidth'     => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'Manufacturer'  => [
            'required'  => false,
            'type'      => 'string',
        ],
        'MinQuantity'   => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'Model'         => [
            'required'  => false,
            'type'      => 'string',
        ],
        'SellPrice'     => [
            'required'  => true,
            'type'      => 'decimal',
        ],
        'Supplier'      => [
            'required'  => false,
            'type'      => 'string',
        ],
        'TimeStamp'     => [
            'required'  => false,
            'type'      => 'datetime',
        ],
        'UOM'           => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Warehouse'     => [
            'required'  => true,
            'type'      => 'int',
        ],
        'WeightCat'     => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Currency'      => [
            'required'  => false,
            'type'      => 'string',
        ],
    ];

    public static $identifiers = [
        'Warehouse'     => MainController::WAREHOUSE,
        'AccountKey'    => MainController::ACCOUNTKEY,
    ];

    protected static $defaults = [
        'Description'   => '',
        'Images'        => [],
        'Article'       => '',
    ];
}
