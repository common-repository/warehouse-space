<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Classes\Requests\GetOrderShipmentInfoRequest;
use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\Exceptions\OrderException;

class OrderShipmentInfoImporter extends Exporter
{
    protected static $syncMethod = 'GetOrderShipmentInfo';
    protected $orderIds = [];
    protected static $identifiers = [
        MainController::ACCOUNTKEY,
    ];

    public function setOrderIds($orderIds)
    {
        if (is_array($orderIds) && !empty($orderIds)) {
            foreach ($orderIds as $value) {
                if (!is_numeric($value)) {
                    $this->errors[] = 'Order id is not of integer type';
                    throw new OrderException('Order id is not of integer type', 1);
                }
                $this->orderIds[] = $value;
            }
        } else {
            $this->errors[] = 'Order ids array given to GetOrderShipmentInfoImporter is empty or not of array type';
            throw new OrderException('Order ids array given to GetOrderShipmentInfoImporter is empty or not of array type', 1);
        }
    }

    public function export()
    {
        if (empty($this->errors) && !empty($this->orderIds)) {
            try {
                $requestData = [];
                $requestData['AccountKey'] = $this->config[MainController::ACCOUNTKEY];
                $requestData['ListInvNumbers'] = $this->orderIds;
                $request = new GetOrderShipmentInfoRequest($requestData);
                $response = $this->client->call(static::$syncMethod, $request->getRequestParams());
                $resultVariable = static::$syncMethod.'Result';
                return $response->$resultVariable;
            } catch (\Exception $e) {
                $this->errors[] = "Error during request build: {$e}";
                return false;
            }
        } else {
            throw new OrderException('This ordershipment update request has errors: '.print_r($this->errors, 1), 1);
            return false;
        }
    }
}
