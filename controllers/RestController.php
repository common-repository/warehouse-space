<?php

namespace WarehouseSpace\WP\Controllers;

use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\WP\Controllers\AdminController;

class RestController
{
    public $adminController;
    const RESTNAMESPACE = 'warehousespace/v1';
    const ORDERENDPOINT = 'order';
    const PRODUCT_QUANTITY_ENDPOINT = 'product_quantity';

    public function __construct(AdminController $adminController)
    {
        $this->adminController = $adminController;
    }

    public function addRestRoutes()
    {
        register_rest_route(self::RESTNAMESPACE, '/'.self::ORDERENDPOINT.'/(?P<id>\d+)/(?P<warehouse>\d+)/(?P<token>[a-zA-Z0-9-%]+)', array(
            'methods' => 'GET',
            'callback' => array(&$this, 'updateOrderShipmentInfo'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'required' => true,
                ),
                'warehouse' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'required' => true,
                ),
                'token' => [
                    'required' => true,
                ],
            ),
        ));
        register_rest_route(self::RESTNAMESPACE, '/'.self::PRODUCT_QUANTITY_ENDPOINT.'/(?P<id>\d+)/(?P<qty>\d+)/(?P<warehouse>\d+)/(?P<token>[a-zA-Z0-9-%]+)', array(
            'methods' => 'GET',
            'callback' => array(&$this, 'qtyProductUpdate'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'required' => true,
                ),
                'qty' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'required' => true,
                ),
                'warehouse' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'required' => true,
                ),
                'token' => [
                    'required' => true,
                ],
            ),
        ));
    }

    private function checkCredentials($params)
    {
        $credentials = $this->adminController->getCredentials();
        if (!isset($credentials['token']) && !isset($credentials[MainController::WAREHOUSE])) {
            return false;
        }
        if (strcmp($params['token'], $credentials['token']) !== 0 || (int)$params[MainController::WAREHOUSE] !== $credentials[MainController::WAREHOUSE]) {
            return false;
        }
        return true;
    }

    public function updateOrderShipmentInfo(\WP_REST_Request $request)
    {
        $parameters = $request->get_params();
        if (!isset($parameters['token']) || !isset($parameters[MainController::WAREHOUSE]) || !$this->checkCredentials($parameters)) {
            return [
                'success' => false,
            ];
        }

        $orderId = $parameters['id'];
        //if ($this->adminController->needShippingInfoChecking($orderId)) {
        $info = $this->adminController->getOrderShipmentInfo([$orderId]);
        if (!empty($info)) {
            $this->adminController->updateOrdersFromOrderShipmentInfo($info);
        }
        //}

        return [
            'success' => true,
            'order_id' => $orderId,
        ];
    }

    public function qtyProductUpdate(\WP_REST_Request $request)
    {
        $parameters = $request->get_params();
        if (!isset($parameters['token']) || !isset($parameters[MainController::WAREHOUSE]) || !$this->checkCredentials($parameters)) {
            return [
                'success' => false,
            ];
        }

        $productId = $parameters['id'];
        $qty = $parameters['qty'];

        $result = '';
        $product = wc_get_product($productId);
        if ($product) {
            $result = wc_update_product_stock($product, $qty);
        }

        return [
            'success' => true,
            'result'  => $result,
        ];
    }
}

