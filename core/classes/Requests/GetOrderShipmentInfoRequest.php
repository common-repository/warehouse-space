<?php

namespace WarehouseSpace\Classes\Requests;

class GetOrderShipmentInfoRequest extends Request
{
    protected $schema = [
        'AccountKey'             => [
            'required'  => true,
            'type'      => 'string',
        ],
        'ListInvNumbers' => [
            'required'  => true,
            'type'      => 'array',
            'params'    => [
                'type'  => 'string',
            ],
        ],
    ];
}
