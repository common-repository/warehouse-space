<?php

namespace WarehouseSpace\Classes\Requests;

use WarehouseSpace\Classes\Product;

class MaterialBulkRequest extends Request
{
    protected $schema = [
        'ArticlesList' => [
            'required'  => true,
            'type'      => 'array',
            'params'    => [
                'type'  => 'object',
                'class' =>  Product::class,
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
