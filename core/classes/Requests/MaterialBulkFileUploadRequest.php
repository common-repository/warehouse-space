<?php

namespace WarehouseSpace\Classes\Requests;

use WarehouseSpace\Classes\Product;

class MaterialBulkFileUploadRequest extends Request
{
    protected $schema = [
        'ArrayOfMaterialArticle' => [
            'required'  => true,
            'type'      => 'array',
            'child'     => 'MaterialArticle',
            'params'    => [
                'type'  => 'object',
                'class' =>  Product::class,
            ],
        ],
    ];

    protected function transformArrayOfMaterialArticle()
    {
        $requestParams = [];
        foreach ($this->data['ArrayOfMaterialArticle'] as $product) {
            $requestParams[] = [$this->schema['ArrayOfMaterialArticle']['child'] => $product->getData()];
        }
        return $requestParams;
    }
}
