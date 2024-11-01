<?php

namespace WarehouseSpace\WP\Classes;

use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\WP\Controllers\RestController;

class PluginRegistrator
{
    public $credentials;
    public function __construct($credentials, $controller)
    {
        $this->credentials = $credentials;
        $this->controller = $controller;
    }

    public function register($enable = true)
    {
        $orderServerUrl = RestController::RESTNAMESPACE.'/'.RestController::ORDERENDPOINT.'/';
        $storeInfo = [
            'ShopName'              => get_bloginfo('name'),
            'ShopURL'               => get_bloginfo('url'),
            'OrderServiceURL'       => get_rest_url(null, $orderServerUrl),
            'StockAdjustmentURL'    => '',
            'ShopIP'                => $_SERVER['REMOTE_ADDR'],
            'ShopLanguage'          => get_bloginfo('language'),
            'Enable'                => $enable,
            'AdminEmail'            => get_bloginfo('admin_email'),
        ];

        try {
            $core = new MainController($this->credentials);
            $result = $core->registerStore($storeInfo);
            return $result;
        } catch (\Exception $e) {
            $this->controller->handleErrors($e);
            return false;
        }
    }
}
