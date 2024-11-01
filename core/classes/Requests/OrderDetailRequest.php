<?php

namespace WarehouseSpace\Classes\Requests;

use WarehouseSpace\Classes\OrderProduct;

class OrderDetailRequest extends Request
{
    protected $schema = [
        'Warehouse'             => [
            'required'  => true,
            'type'      => 'int',
        ],
        'InvNumber'             => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Customer'              => [
            'required'  => false,
            'type'      => 'string',
        ],
        'AccountKey'            => [
            'required'  => true,
            'type'      => 'string',
            'params'    => [
                'notEmpty'   => true,
            ],
        ],
        'InvReference'          => [
            'required'  => false,
            'type'      => 'string',
        ],
        'InvStatus'             => [
            'required'  => true,
            'type'      => 'int',
        ],
        'InvDate'               => [
            'required'  => false,
            'type'      => 'datetime',
        ],
        'InvDueDate'            => [
            'required'  => false,
            'type'      => 'string',
        ],
        'InvTotal'              => [
            'required'  => false,
            'type'      => 'string',
        ],
        'InvAmountDue'          => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ErpTimeStamp'          => [
            'required'  => false,
            'type'      => 'datetime',
        ],
        'DeliverAddress'        => [
            'required'  => false,
            'type'      => 'string',
        ],
        'DeliverAddress2'        => [
            'required'  => false,
            'type'      => 'string',
        ],
        'DeliveryPostCodeZIP'   => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Country'               => [
            'required'  => false,
            'type'      => 'string',
        ],
        'CountryCode'           => [
            'required'  => false,
            'type'      => 'string',
        ],
        'StateOrProvinceCode'   => [
            'required'  => false,
            'type'      => 'string',
        ],
        'City'                  => [
            'required'  => false,
            'type'      => 'string',
        ],
        'EmailAddress'          => [
            'required'  => false,
            'type'      => 'string',
        ],
        'PartnerKey'            => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ContactPersonName'     => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ContactPersonPhone'    => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Shipper'               => [
            'required'  => false,
            'type'      => 'string',
        ],
        'Comments'              => [
            'required'  => false,
            'type'      => 'string',
        ],
        'PaymentMethod'         => [
            'required'  => false,
            'type'      => 'string',
        ],
        'PaymentDescription'    => [
            'required'  => false,
            'type'      => 'string',
        ],
        'OrderTotalWeight'      => [
            'required'  => false,
            'type'      => 'decimal',
        ],
        'OrderType'             => [
            'required'  => true,
            'type'      => 'int',
        ],
        'InvoiceID'     => [
            'required'  => true,
            'type'      => 'string',
        ],
        'ShortCode'     => [
            'required'  => true,
            'type'      => 'string',
        ],
        'CurrencyCode'     => [
            'required'  => false,
            'type'      => 'string',
        ],
        'TaxAmount'     => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ShipmentCost'     => [
            'required'  => false,
            'type'      => 'string',
        ],
        'CompanyName'     => [
            'required'  => false,
            'type'      => 'string',
        ],
        'ArticlesList' => [
            'required'  => true,
            'type'      => 'array',
            'params'    => [
                'type'  => 'object',
                'class' =>  OrderProduct::class,
            ],
        ],
    ];

    protected function transformArticlesList()
    {
        $requestParams = [];
        foreach ($this->data['ArticlesList'] as $product) {
            $requestParams[] = $product->getData();
        }
        return $requestParams;
    }
}
