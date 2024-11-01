<?php

namespace WarehouseSpace\WP\Controllers;

use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\WP\Classes\PluginRegistrator;
use WarehouseSpace\WP\Classes\ProductSync;
use WarehouseSpace\WP\Classes\WCLoggerAdapter;
use WarehouseSpace\WP\Controllers\RestController;

class AdminController
{
    public $pluginUrl;
    private $credentials = [];
    private $logger;
    protected $debug;

    const PRODUCT_SYNC_TABLE = 'warehousespace_products';
    const ORDER_SYNC_TABLE = 'warehousespace_orders';

    const PRODUCT_SYNC_LIMIT = 4000;

    const DEBUG = false;

    const ORDER_CREATE = 1;
    const ORDER_UPDATE = 2;

    const ORDER_SHIPMENT_PACKED = 'warehouseSpaceShippingPacked';
    const ORDER_SHIPMENT_PICKED = 'warehouseSpaceShippingPicked';
    const ORDER_SHIPMENT_DISPATCHED = 'warehouseSpaceShippingDispatched';
    const ORDER_SHIPMENT_YOUTUBE = 'warehouseSpaceShippingYoutube';
    const ORDER_SHIPMENT_TRACKING_NUMBERS = 'warehouseSpaceShippingTrackingNumbers';

    const TOKEN_OPTION_NAME   = 'warehouseSpaceToken';
    const DEBUG_OPTION_NAME   = 'warehouseSpaceDebug';
    const STOREID_OPTION_NAME = 'warehouseSpaceStoreID';

    public function __construct()
    {
        $this->debug = get_option(static::DEBUG_OPTION_NAME);
        $this->logger = false;
        $this->pluginUrl = dirname(__FILE__).'/../';
    }

    public function activate()
    {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Please install and Activate WooCommerce.', 'warehousespace'), 'Plugin dependency check', array('back_link' => true));
        }

        update_option('warehouse_activated', microtime(1), false);
    }

    public function deactivate()
    {
        wp_clear_scheduled_hook('updateAllUnshippedOrdersCronJob2');
        delete_option('warehouse_activated');
        $this->registerWithWarehouseServer<(false);
        delete_option('warehouseSpace_'.MainController::ACCOUNTKEY);
        delete_option('warehouseSpace_'.MainController::WAREHOUSE);
        delete_option(static::TOKEN_OPTION_NAME);
    }

    public function registerWithWarehouseServer($enable = true, $accountkey = false, $warehouse = false)
    {
        if ($accountkey === false || $warehouse === false) {
            $credentials = $this->getCredentials();
        } else {
            $credentials = [
                MainController::ACCOUNTKEY  => $accountkey,
                MainController::WAREHOUSE   => (int)$warehouse,
                'debug'                     => true,
            ];
            $logger = $this->getLogger();
            if ($logger !== false) {
                $credentials['logger'] = $logger;
            }
        }

        if ($credentials === false) {
            return false;
        }

        $registrator = new PluginRegistrator($credentials, $this);
        $success = $registrator->register($enable);

        if (is_object($success) && !$success->Success) {
            if (strlen($success->ErrorMessage) > 0) {
                $this->handleErrors($success->ErrorMessage);
            }
            return false;
        } elseif (is_object($success) && $success->Success) {
            if (strlen($success->Token) > 0) {
                update_option(static::TOKEN_OPTION_NAME, $success->Token, false);
                update_option(static::STOREID_OPTION_NAME, $success->StoreID, false);
            } else {
                return false;
            }
        }

        return $success;
    }

    public function adminMenu()
    {
        add_menu_page(
            'Warehouse Space',
            'Warehouse Space',
            'edit_pages',
            'warehouse-space',
            array($this, 'settingsContent'),
            plugins_url('../imgs/logo_menu.png', __FILE__)
        );
    }

    public function adminScripts($hook)
    {
        if ($hook === 'toplevel_page_warehouse-space') {
            wp_enqueue_script('warehousespace_admin_js', plugins_url('../js/admin.js', __FILE__), ['jquery']);
            wp_enqueue_style('warehousespace_admin_css', plugins_url('../css/admin.css', __FILE__));
        }
    }

    public function saveCronJobFrequency($timePerMinute)
    {
        update_option('warehouseSpaceCronJobFrequency', (int)$timePerMinute);
    }

    public function settingsContent()
    {
        if (!current_user_can('edit_pages')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'warehousespace'));
        }
        $successMsg = [];

        $start = microtime(1);
        $productSync = new ProductSync($this);
        $synchronized = $productSync->getSynchronizedProductsTotal();
        $total = $productSync->getProductsTotal();
        $neededRuns = ceil($total / $productSync->getProductSyncLimit());
        $syncStep = $neededRuns > 1 ?  (1 / $neededRuns) * 100 : 100;
        $successMsg = [];
        $errorMsg = [];

        if (isset($_POST['credentials'])) {
            $requestSuccess = $this->registerWithWarehouseServer(true, $_POST['accountkey'], $_POST['warehouse']);
            if ($requestSuccess !== false) {
                $success = $this->saveCredentials($_POST['accountkey'], $_POST['warehouse']);

                if (isset($_GET['debug']) && $_GET['debug']) {
                    update_option(static::DEBUG_OPTION_NAME, true, false);
                } else {
                    delete_option(static::DEBUG_OPTION_NAME);
                }

                if ($success) {
                    $successMsg[] = __('Your credentials have been saved successfully!', 'warehousespace');
                } else {
                    $errorMsg[] = __('Your credentials have not been saved, please contact service provider', 'warehousespace');
                }
            } else {
                $errorMsg[] = __('Your credentials have not been saved, please contact service provider', 'warehousespace');
            }
        }

        if (isset($_POST['frequency']) && is_numeric($_POST['frequency']) && $_POST['frequency'] >= 1) {
            $this->saveCronJobFrequency($_POST['frequency']);
        }
        $credentials = $this->getCredentials();
        $frequency = get_option('warehouseSpaceCronJobFrequency');

        if (self::DEBUG) {
            echo 'Calculating synchronized and total products time: '.(microtime(1) - $start).'s<br>';
        }

        // Render the settings template
        include(sprintf("%s/admin/settings.php", $this->pluginUrl));
    }

    public function getCredentials($appendStoreId = false)
    {
        if (empty($this->credentials)) {
            if ($appendStoreId) {
                $accountKey = get_option('warehouseSpace_'.MainController::ACCOUNTKEY).'|'.get_option(static::STOREID_OPTION_NAME);
            } else {
                $accountKey = get_option('warehouseSpace_'.MainController::ACCOUNTKEY);
            }
            $warehouse = get_option('warehouseSpace_'.MainController::WAREHOUSE);
            $token = get_option(static::TOKEN_OPTION_NAME);
            if ($accountKey === false || $warehouse === false || empty($accountKey) || empty($warehouse)) {
                $this->credentials = false;
                return false;
            } else {
                $this->credentials = [
                    MainController::ACCOUNTKEY  => $accountKey,
                    MainController::WAREHOUSE   => (int)$warehouse,
                    'token'                     => $token,
                    'debug'                     => $this->debug,
                ];

                $logger = $this->getLogger();
                if ($logger !== false) {
                    $this->credentials['logger'] = $logger;
                }

                return $this->credentials;
            }
        } else {
            return $this->credentials;
        }
    }

    protected function saveCredentials($accountKey, $warehouse)
    {
        update_option('warehouseSpace_'.MainController::ACCOUNTKEY, $accountKey, false);
        update_option('warehouseSpace_'.MainController::WAREHOUSE, $warehouse, false);

        return true;
    }

    public function warehousespaceGetProductSyncTotal()
    {
        $productSync = new ProductSync($this);
        $synchronized = $productSync->getSynchronizedProductsTotal();
        wp_send_json([
            'total'  => $synchronized,
        ]);
    }

    public function warehousespaceProductSync()
    {
        $productSync = new ProductSync($this);
        $productSync->productSyncAjax();
    }

    public function addWarehouseIndexedMeta($query, $query_vars)
    {
        $productSync = new ProductSync($this);
        $query = $productSync->addWarehouseIndexedMeta($query, $query_vars);
        return $query;
    }

    public function exportProductToWarehouseHook($post_id, $post, $update)
    {
        if (!in_array($post->post_status, ['publish', 'update']) || $post->post_type != 'product') {
            return;
        }
        $config = $this->getCredentials();
        $productSync = new ProductSync($this);
        if (get_post_type($post_id) == 'product' && $this->credentials !== false) {
            $productSync->exportProductToWarehouse($post_id, $config);
        }
    }

    public function exportVariationToWarehouseHook($id)
    {
        $config = $this->getCredentials();
        if ($this->credentials !== false) {
            $productSync = new ProductSync($this);
            $productSync->exportProductToWarehouse($id, $config);
        }
    }

    public function bulkEditVariationsHook($bulk_action, $data, $product_id, $variations)
    {
        $config = $this->getCredentials();
        if ($this->credentials !== false) {
            $productSync = new ProductSync($this);
            $productSync->exportProductToWarehouse($product_id, $config);
        }
    }

    private function getLogger()
    {
        if ($this->logger === false && function_exists('wc_get_logger')) {
            $this->logger = new WCLoggerAdapter();
        }
        return $this->logger;
    }

    public function handleErrors($e, $priority = 1)
    {
        $logger = $this->getLogger();
        if ($logger !== false) {
            if (is_array($e)) {
                foreach ($e as $error) {
                    $logger->log($error, $priority);
                }
            } else {
                $logger->log($e, $priority);
            }
        }
        error_log($e);
    }

    public function exportCreatedOrder($orderId)
    {
        // check to prevent thank you page refresh
        if (!$this->orderExported($orderId)) {
            $this->exportOrder($orderId, static::ORDER_CREATE);
        }
    }

    public function exportUpdatedOrder($orderId)
    {
        $this->exportOrder($orderId, static::ORDER_UPDATE);
    }

    public function getInvStatus($status, $allStatuses, $payment)
    {
        if ($payment === 'cod' && $status === 'processing') {
            return 6;
        }

        return $allStatuses[$status];
    }

    public function exportOrder($orderId, $action)
    {
        $config = $this->getCredentials(true);
        if ($this->credentials !== false) {
            $order = wc_get_order($orderId);
            $statuses = $this->exportableOrderStatuses($action);
            $status = $order->get_status();
            if (in_array($status, $statuses)) {
                $paymentMethod = $order->get_payment_method();
                $invStatus = $this->getInvStatus($status, $statuses, $paymentMethod);
                $total = (float) $order->get_total();
                $totalTax = (float) $order->get_total_tax();
                $shippingTotal = (float) $order->get_total_shipping();
                $shippingTotalTax = (float) $order->get_shipping_tax();
                $shipping = $shippingTotal + $shippingTotalTax;
                $totalWithoutTax = $total - $totalTax;
                $currency = get_woocommerce_currency();
                $orderArray = [
                    'id'                    => $orderId,
                    'InvNumber'             => $orderId,
                    'Customer'              => $order->get_billing_first_name().' '.$order->get_billing_last_name(),
                    'InvReference'          => $orderId,
                    'InvStatus'             => $invStatus,
                    'InvDate'               => $order->get_date_created()->format(\DateTime::RFC3339),
                    'InvDueDate'            => '',
                    'InvTotal'              => $totalWithoutTax,
                    'InvAmountDue'          => 0,
                    'DeliverAddress'        => wp_strip_all_tags(str_replace('<br/>', ', ', $order->get_shipping_address_1())),
                    'DeliverAddress2'       => wp_strip_all_tags(str_replace('<br/>', ', ', $order->get_shipping_address_2())),
                    'DeliveryPostCodeZIP'   => $order->get_shipping_postcode(),
                    'Country'               => '',
                    'CountryCode'           => $order->get_shipping_country(),
                    'StateOrProvinceCode'   => $order->get_shipping_state(),
                    'City'                  => $order->get_shipping_city(),
                    'EmailAddress'          => $order->get_billing_email(),
                    'PartnerKey'            => '',
                    'ContactPersonName'     => $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
                    'ContactPersonPhone'    => $order->get_billing_phone(),
                    'Shipper'               => '',
                    'Comments'              => $order->get_customer_note(),
                    'PaymentMethod'         => $paymentMethod,
                    'PaymentDescription'    => '',
                    'OrderTotalWeight'      => 0,
                    'OrderType'             => 3,
                    'InvoiceID'             => $orderId,
                    'ShortCode'             => '',
                    'CurrencyCode'          => $currency,
                    'TaxAmount'             => $totalTax,
                    'ShipmentCost'          => $shipping,
                    'CompanyName'           => $order->get_shipping_company(),
                ];

                $products = [];
                foreach ($order->get_items() as $orderItem) {
                    $product = $orderItem->get_product();
                    $id = $product->get_id();
                    $products[] = [
                        'id'            => $id,
                        'ArticleDescr'  => strip_shortcodes(wp_strip_all_tags($product->get_description())),
                        'Quantity'      => $orderItem->get_quantity(),
                        'ProductID'     => $id,
                        'Article'      => $product->get_sku(),
                    ];
                }
                $config = $this->getCredentials(true);
                try {
                    $core = new MainController($config);
                    $result = $core->exportOrder($products, $orderArray);
                    if ($result['result']) {
                        $this->setOrderExported($orderId);
                        if (!empty($result['result']->CancellationReason)) {
                            $order->add_order_note('Warehouse order shipping error: '.$result['result']->CancellationReason);
                        }
                    } else {
                        $this->handleErrors(implode("\n", $result['data']['errors']));
                    }
                } catch (\Exception $e) {
                    $this->handleErrors($e);
                }
            }
        }
    }

    public function exportableOrderStatuses($action)
    {
        if ($action === static::ORDER_CREATE) {
            return [
                'processing'    => true,
                'on-hold'       => 6,
                'pending'       => 6,
            ];
        } elseif ($action === static::ORDER_UPDATE) {
            return [
                'processing'    => true,
                'on-hold'       => 6,
                'pending'       => 6,
                'completed'     => 4,
                'cancelled'     => false,
                'refunded'      => false,
                'failed'        => false,
            ];
        }
    }

    public function orderExported($orderId)
    {
        $exported = get_post_meta($orderId, 'warehouseSpaceExported', true);

        // get_post_meta returns empty string if postmeta not found
        if ($exported) {
            return true;
        } else {
            return false;
        }
    }

    public function setOrderExported($orderId)
    {
        update_post_meta($orderId, 'warehouseSpaceExported', true);
    }

    public function updateAllUnshippedOrders($context)
    {
        $screen = get_current_screen();
        if ($screen->id === 'edit-shop_order') {
            $orders = $this->getOrdersThatNeedShippingInfoUpdate();
            $orderIds = [];
            foreach ($orders as $order) {
                $orderIds[] = $order->get_id();
            }
            if (!empty($orderIds)) {
                $info = $this->getOrderShipmentInfo($orderIds);
                if (!empty($info)) {
                    $this->updateOrdersFromOrderShipmentInfo($info);
                }
            }
        } elseif ($screen->id === 'shop_order' && $screen->post_type === 'shop_order') {
            global $post;
            $orderId = $post->ID;
            if ($this->needShippingInfoChecking($orderId)) {
                $info = $this->getOrderShipmentInfo([$orderId]);
                if (!empty($info)) {
                    $this->updateOrdersFromOrderShipmentInfo($info);
                }
            }
        }
    }

    public function updateAllUnshippedOrdersCronJob()
    {
        $orders = $this->getOrdersThatNeedShippingInfoUpdate();
        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->get_id();
        }
        if (!empty($orderIds)) {
            $info = $this->getOrderShipmentInfo($orderIds);
            if (!empty($info)) {
                $this->updateOrdersFromOrderShipmentInfo($info);
            }
        }
    }

    public function getOrdersThatNeedShippingInfoUpdate()
    {
        $args = [
            'status'            => array('shop_order-processing', 'shop_order-on-hold', 'shop_order-pending'),
            'orderby'           => array(
                'ID' => 'ASC',
            ),
            'return'            => 'objects',
            'paginate'          => false,
            'wareSpaceShipped'  => '',
            'warehouseSpaceExported' => 1,
        ];
        $orders = wc_get_orders($args);
        return $orders;
    }

    public function needShippingInfoChecking($orderId)
    {
        if (get_post_meta($orderId, 'warehouseSpaceExported', true)) {
            if (!get_post_meta($orderId, 'wareSpaceShipped', true)) {
                return true;
            }
        }
        return false;
    }

    public function updateOrdersFromOrderShipmentInfo($info)
    {
        $uniqueOrders = [];
        foreach ($this->getOrderProductsFromShipmentInfoResponse($info) as $item) {
            if (property_exists($item, 'InvNumber') && !isset($uniqueOrders[$item->InvNumber])) {
                $uniqueOrders[$item->InvNumber] = $item;
            }
        }
        foreach ($uniqueOrders as $item) {
            $this->updateOrderMetaFromOrderShipmentInfo($item);
        }
    }

    public function getOrderProductsFromShipmentInfoResponse($orderDetail)
    {
        if (isset($orderDetail['result']) && !empty($orderDetail['result']) && property_exists($orderDetail['result'], 'OrderShipmentInfo')) {
            if (is_object($orderDetail['result']->OrderShipmentInfo)) {
                return [$orderDetail['result']->OrderShipmentInfo];
            } elseif (is_array($orderDetail['result']->OrderShipmentInfo)) {
                return $orderDetail['result']->OrderShipmentInfo;
            }
        }
        return [];
    }

    public function updateOrderMetaFromOrderShipmentInfo($info)
    {
        $id = $info->InvNumber;
        $data = [];

        if (is_object($info) && property_exists($info, 'Shipments') && is_object($info->Shipments) && property_exists($info->Shipments, 'ShipmentDetail')) {
            $shipments = [];
            if (is_object($info->Shipments->ShipmentDetail)) {
                $shipments = [$info->Shipments->ShipmentDetail];
            } elseif (is_array($info->Shipments->ShipmentDetail)) {
                $shipments = $info->Shipments->ShipmentDetail;
            }

            $data[static::ORDER_SHIPMENT_PACKED]            = [];
            $data[static::ORDER_SHIPMENT_DISPATCHED]        = [];
            $data[static::ORDER_SHIPMENT_TRACKING_NUMBERS]  = [];
            $data[static::ORDER_SHIPMENT_YOUTUBE]           = [];

            foreach ($shipments as $shipmentDetail) {
                $data[static::ORDER_SHIPMENT_PACKED][]            = $shipmentDetail->PackingEndTime;
                $data[static::ORDER_SHIPMENT_DISPATCHED][]        = $shipmentDetail->DispatchTime;
                $data[static::ORDER_SHIPMENT_TRACKING_NUMBERS][]  = $shipmentDetail->TrackingNumber;
                $data[static::ORDER_SHIPMENT_YOUTUBE][]           = $shipmentDetail->YoutubeUrl;
            }
        }

        $status = $this->getOrderStatusFromWarehouseOrderStatus($info->OrderStatus);
        $this->setOrderShippingFields($id, $data, $status);
    }

    public function setOrderShippingFields($orderId, $data, $status)
    {
        foreach ($data as $k => $v) {
            update_post_meta($orderId, $k, $v);
        }
        if ($status === 'completed') {
            update_post_meta($orderId, 'wareSpaceShipped', true);
        }
        if (!$this->isStatusIgnored($status)) {
            $order = wc_get_order($orderId);
            $order->update_status($status);
        }
    }

    public function isStatusIgnored($status)
    {
        if ($status === 'cancelled') {
            return true;
        }
        return false;
    }

    public function getOrderStatusFromWarehouseOrderStatus($status)
    {
        if (is_numeric($status)) {
            switch ($status) {
                case 4:
                    return 'completed';
                    break;

                case 6:
                    return 'on-hold';
                    break;

                case 7:
                    return 'cancelled';
                    break;

                case 2:
                    return 'pending';
                    break;

                case 3:
                    return 'pending';
                    break;

                case 0:
                    return 'processing';
                    break;
            }
        }
        return 'on-hold';
    }

    public function getOrderShipmentInfo($orderIds)
    {
        $config = $this->getCredentials();
        if ($this->credentials !== false) {
            try {
                $core = new MainController($config);
                $result = $core->importOrderStatuses($orderIds);
                if ($result['result']) {
                    return $result;
                } else {
                    $this->handleErrors(implode("\n", $result['data']['errors']));
                }
            } catch (\Exception $e) {
                $this->handleErrors($e);
            }
        }
    }

    public function addWarehouseShippedMeta($query, $query_vars)
    {
        if (isset($query_vars['wareSpaceShipped'])) {
            if ($query_vars['wareSpaceShipped'] === '') {
                $query['meta_query'][] = array(
                    'key' => 'wareSpaceShipped',
                    'value' => '',
                    'compare' => 'NOT EXISTS',
                );
            } else {
                $query['meta_query'][] = array(
                    'key' => 'wareSpaceShipped',
                    'value' => esc_attr($query_vars['wareSpaceShipped']),
                );
            }
        }
        if (isset($query_vars['warehouseSpaceExported'])) {
            if ($query_vars['warehouseSpaceExported'] === '') {
                $query['meta_query'][] = array(
                    'key' => 'warehouseSpaceExported',
                    'value' => '',
                    'compare' => 'NOT EXISTS',
                );
            } else {
                $query['meta_query'][] = array(
                    'key' => 'warehouseSpaceExported',
                    'value' => esc_attr($query_vars['warehouseSpaceExported']),
                );
            }
        }
        return $query;
    }

    public function displayShippingInfo()
    {
        global $post;
        $orderId = $post->ID;
        $labels = [
            static::ORDER_SHIPMENT_PICKED           => 'Picked',
            static::ORDER_SHIPMENT_PACKED           => 'Packed',
            static::ORDER_SHIPMENT_DISPATCHED       => 'Dispatched',
            static::ORDER_SHIPMENT_YOUTUBE          => 'Videos',
            static::ORDER_SHIPMENT_TRACKING_NUMBERS => 'Tracking numbers',
        ];

        foreach ($labels as $fieldName => $label) {
            $values = get_post_meta($orderId, $fieldName);
            if ($values !== false && !empty($values)) {
                echo "<p><strong>{$label}:</strong> ";
                foreach ($values as $value) {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $v) {
                        if ($fieldName === static::ORDER_SHIPMENT_YOUTUBE) {
                            echo "<a href=\"{$v}\">{$v}</a><br>";
                        } elseif ($fieldName === static::ORDER_SHIPMENT_TRACKING_NUMBERS) {
                            echo "{$v}<br>";
                        } else {
                            $date = new \DateTime($v);
                            echo $date->format('Y-m-d H:i:s').'<br>';
                        }
                    }
                }
                echo '</p>';
            }
        }
    }

    public function displayShippingInfoInTable($column, $post_id)
    {
        $labels = [
            static::ORDER_SHIPMENT_DISPATCHED => 'dispatched',
        ];
        $labels = array_flip($labels);
        if (isset($labels[$column])) {
            $values = get_post_meta($post_id, $labels[$column]);
            foreach ($values as $value) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                foreach ($value as $v) {
                    $date = new \DateTime($v);
                    echo $date->format('Y-m-d H:i:s').'<br>';
                }
            }
        }
    }

    public function displayShippingInfoInTableColumns($columns)
    {
        $columns['dispatched'] = __('Dispatched', 'warehousespace');
        return $columns;
    }

    public function bulkEditOrdersHook($redirect_to, $action, $ids)
    {
        // Bail out if this is not a status-changing action.
        if ( false === strpos( $action, 'mark_' ) ) {
            return $redirect_to;
        }

        $order_statuses = wc_get_order_statuses();
        $new_status     = substr( $action, 5 ); // Get the status name from action.
        $report_action  = 'marked_' . $new_status;

        // Sanity check: bail out if this is actually not a status, or is
        // not a registered status.
        if ( ! isset( $order_statuses[ 'wc-' . $new_status ] ) ) {
            return $redirect_to;
        }

        $changed = 0;
        $ids     = array_map( 'absint', $ids );

        foreach ( $ids as $id ) {
            $this->exportUpdatedOrder($id);
        }

        return $redirect_to;
    }

    /*public function customCronScheduleTime($schedules)
    {
        $this->getCredentials();
        if ($this->credentials !== false) {
            $timesPerHour = get_option('warehouseSpaceCronJobFrequency');
            if ($timesPerHour) {
                if (!isset($schedules["warehousespace"])) {
                    $schedules["warehousespace"] = array(
                        'interval' => $timesPerHour*60,
                        'display' => __('Custom warehousespace time'));
                }
            }
        }
        return $schedules;
    }*/
}

