<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Classes\Requests\MaterialBulkFileUploadRequest;
use WarehouseSpace\Classes\Requests\MaterialBulkRequest;
use WarehouseSpace\Classes\XmlGenerator;
use WarehouseSpace\Exceptions\ProductException;

class ProductBulkExporter extends Exporter
{
    protected static $syncMethod = 'MaterialBulk';
    protected static $syncMethod2 = 'UploadProductsFile';
    protected $missed = [];
    protected $hit = [];
    protected $products = [];

    public function setProducts($products)
    {
        if (is_array($products) && !empty($products)) {
            foreach ($products as $index => $product) {
                if (!isset($product['id'])) {
                    $this->errors[] = "Product with index {$index} does not have an id.";
                    if ($this->productFailureIsBlocking()) {
                        break;
                    } else {
                        continue;
                    }
                }
                $id = $product['id'];
                unset($product['id']);
                try {
                    $product = $this->setIdentifiers($product);
                    $this->products[] = new Product($product);
                    $this->hit[] = $id;
                } catch (ProductException $e) {
                    $this->errors[] = "Product with id {$id} was skipped due to following exception: {$e}";
                    $this->missed[] = $id;
                    if ($this->productFailureIsBlocking()) {
                        break;
                    }
                }
            }
        } else {
            throw new ProductException('Product array given to ProductBulkExporter is empty or not of array type', 1);
        }
    }

    protected function productFailureIsBlocking()
    {
        return isset($this->config['productImportNotBlocking']) && $this->config['productImportNotBlocking'] === false;
    }

    protected function setIdentifiers($product)
    {
        foreach (Product::$identifiers as $identifier => $configName) {
            $product[$identifier] = $this->config[$configName];
        }

        return $product;
    }

    public function export()
    {
        try {
            $request = new MaterialBulkRequest(['ArticlesList' => $this->products]);
            $response = $this->client->call(static::$syncMethod, $request->getRequestParams());
            $resultVariable = static::$syncMethod.'Result';
            return $response->$resultVariable;
        } catch (\Exception $e) {
            $this->errors[] = "Error during request build: {$e}";
            return false;
        }
    }

    public function exportByFileUpload($tempDir)
    {
        try {
            $request = new MaterialBulkFileUploadRequest(['ArrayOfMaterialArticle' => $this->products]);
            $xmlGenerator = new XmlGenerator();
            $xml = $xmlGenerator->generateFromRequest($request);
            $tmpfile = tempnam('tmp', 'zip');
            rename($tmpfile, substr($tmpfile, 0, strlen($tmpfile) - 4).'.zip');
            $tmpfile = substr($tmpfile, 0, strlen($tmpfile) - 4).'.zip';
            $zip = new \ZipArchive();
            $zip->open($tmpfile, \ZipArchive::OVERWRITE);
            $zip->addFromString('Articles.xml', $xml);
            $zip->close();
            $h = fopen($tmpfile, 'r');
            $file = fread($h, filesize($tmpfile));
            $response = $this->client->call(static::$syncMethod2, ['data' => $file]);
            fclose($h);
            unset($file);
            unlink($tmpfile);
            $resultVariable = static::$syncMethod2.'Result';
            return $response->$resultVariable;
        } catch (\Exception $e) {
            $this->errors[] = "Error during request build: {$e}";
            return false;
        }
    }

    public function getHitProductIds()
    {
        return $this->hit;
    }

    public function getMissedProductIds()
    {
        return $this->missed;
    }
}
