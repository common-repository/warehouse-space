<?php

namespace WarehouseSpace\Classes\Requests;

class RegisterStoreRequest extends Request
{
    protected $schema = [
        'AccountKey'             => [
            'required'  => true,
            'type'      => 'string',
        ],
        'Warehouse'             => [
            'required'  => true,
            'type'      => 'int',
        ],
        'ShopName'             => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ShopURL'             => [
            'required'  => true,
            'type'      => 'string',
        ],
        'OrderServiceURL' => [
            'required'  => false,
            'type'      => 'string',
        ],
        'StockAdjustmentURL' => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ShopIP' => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ShopLanguage' => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Enable' => [
            'required'  => true,
            'type'      => 'bool',
        ],
        'AdminEmail' => [
            'required'  => false,
            'type'      => 'string',
        ],
    ];
}
