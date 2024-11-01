<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Classes\Order;
use WarehouseSpace\Classes\OrderProduct;
use WarehouseSpace\Classes\Requests\OrderDetailRequest;
use WarehouseSpace\Exceptions\OrderException;

class OrderExporter extends Exporter
{
    protected $products = [];
    protected $order;
    protected static $syncMethod = 'OrderDetail';

    public function setOrder($order)
    {
        if (is_array($order) && !empty($order)) {
            if (!isset($order['id'])) {
                $this->errors[] = "Order does not have an id.";
                return false;
            }
            $id = $order['id'];
            unset($order['id']);
            if ($order['InvStatus'] === true) {
                $order['InvStatus'] = 0;
            } elseif ($order['InvStatus'] === false) {
                $order['InvStatus'] = 7;
            }
            try {
                $order = $this->setIdentifiers($order);
                $this->order = new Order($order);
            } catch (OrderException $e) {
                $this->errors[] = "Order with id {$id} was skipped due to following exception: {$e}";
                return false;
            }
        } else {
            $this->errors[] = "Order array given to OrderExporter is empty or not of array type";
            throw new OrderException('Order array given to OrderExporter is empty or not of array type', 1);
        }
    }

    public function setProducts($products)
    {
        if (is_array($products) && !empty($products)) {
            foreach ($products as $index => $product) {
                if (!isset($product['id'])) {
                    $this->errors[] = "Product does not have an id.";
                    return false;
                }
                $id = $product['id'];
                unset($product['id']);
                try {
                    $this->products[] = new OrderProduct($product);
                } catch (OrderException $e) {
                    $this->errors[] = "Product with id {$id} was skipped due to following exception: {$e}";
                    return false;
                }
            }
        } else {
            $this->errors[] = "Product array given to OrderExporter is empty or not of array type";
            throw new OrderException('Product array given to ProductBulkExporter is empty or not of array type', 1);
        }
    }

    public function export()
    {
        if (empty($this->errors) && !empty($this->order) && !empty($this->products)) {
            try {
                $order = $this->order->getData();
                $order['ArticlesList'] = $this->products;
                $request = new OrderDetailRequest($order);
                $response = $this->client->call(static::$syncMethod, $request->getRequestParams());
                $resultVariable = static::$syncMethod.'Result';
                return $response->$resultVariable;
            } catch (\Exception $e) {
                $this->errors[] = "Error during request build: {$e}";
                return false;
            }
        } else {
            throw new OrderException('This order export has errors: '.print_r($this->errors, 1), 1);
            return false;
        }
    }

    protected function setIdentifiers($product)
    {
        foreach (Order::$identifiers as $identifier => $configName) {
            $product[$identifier] = $this->config[$configName];
        }

        return $product;
    }
}
