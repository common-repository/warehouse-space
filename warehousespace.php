<?php
/*
Plugin Name: Warehouse Space
Plugin URI: https://warehouse.space/
Description: Integration with Warehouse.Space webservice
Version: 19.8.1
Author: Oops.EE
Author URI: http://oops.ee
License: MIT
*/

use WarehouseSpace\WP\Controllers\AdminController;
use WarehouseSpace\WP\Controllers\RestController;

defined('ABSPATH') or die('No script kiddies please!');

require_once(dirname(__FILE__).'/core/controllers/MainController.php');
require_once(dirname(__FILE__).'/core/vendor/autoload.php');
require_once(dirname(__FILE__).'/controllers/AdminController.php');
require_once(dirname(__FILE__).'/controllers/RestController.php');
require_once(dirname(__FILE__).'/classes/ProductSync.php');
require_once(dirname(__FILE__).'/classes/PluginRegistrator.php');
require_once(dirname(__FILE__).'/classes/WCLoggerAdapter.php');

if (class_exists('WarehouseSpace\WP\Controllers\AdminController')) {
    $pluginUrl = plugins_url('', __FILE__).'/';
    global $warehouseSpaceAdmin;
    $admin = new AdminController();
    $rest = new RestController($admin);
    $warehouseSpaceAdmin = $admin;

    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array(&$admin, 'activate'));
    register_deactivation_hook(__FILE__, array(&$admin, 'deactivate'));

    add_action('admin_enqueue_scripts', array(&$admin, 'adminScripts'));
    add_action('admin_menu', array(&$admin, 'adminMenu'));

    // Ajax actions
    add_action('wp_ajax_warehousespace_product_sync', array(&$admin, 'warehousespaceProductSync'));
    add_action('wp_ajax_warehousespace_get_product_sync_total', array(&$admin, 'warehousespaceGetProductSyncTotal'));

    // Woocommerce filters
    add_filter('woocommerce_product_data_store_cpt_get_products_query', array(&$admin, 'addWarehouseIndexedMeta'), 10, 2);
    add_filter('woocommerce_order_data_store_cpt_get_orders_query', array(&$admin, 'addWarehouseShippedMeta'), 10, 2);

    // Product create and update hooks
    add_action('save_post', array($admin, 'exportProductToWarehouseHook'), 10, 3);
    //add_action('added_post_meta', array(&$admin, 'exportProductToWarehouseHook'), 10, 4);
    //add_action('updated_post_meta', array(&$admin, 'exportProductToWarehouseHook'), 10, 4);
    add_action('woocommerce_ajax_save_product_variations', array(&$admin, 'exportVariationToWarehouseHook'), 10, 4);
    add_action('woocommerce_bulk_edit_variations', array(&$admin, 'bulkEditVariationsHook'), 10, 4);

    // Order create hook
    add_action('woocommerce_thankyou', array(&$admin, 'exportCreatedOrder'), 10, 1);
    add_action('woocommerce_process_shop_order_meta', array(&$admin, 'exportUpdatedOrder'), 99, 1);
    add_filter('handle_bulk_actions-edit-shop_order', array(&$admin, 'bulkEditOrdersHook'), 11, 3);

    // Order update shipped orders hook
    add_action('admin_head', array(&$admin, 'updateAllUnshippedOrders'), 10, 1);
    add_action('updateAllUnshippedOrdersCronJob2', array(&$admin, 'updateAllUnshippedOrdersCronJob'), 10);

    // Custom order data display hooks
    add_action('woocommerce_admin_order_data_after_shipping_address', array(&$admin, 'displayShippingInfo'), 10, 0);
    add_action('manage_shop_order_posts_custom_column', array(&$admin, 'displayShippingInfoInTable'), 99, 2);
    add_action('manage_shop_order_posts_columns', array(&$admin, 'displayShippingInfoInTableColumns'), 99, 1);

    // Custom cron schedule times
    //add_filter('cron_schedules', array(&$admin, 'customCronScheduleTime'));
    add_action('init', 'warehouseSpaceScheduleCron', 10);

    function warehouseSpaceScheduleCron()
    {
        if (!wp_get_schedule('updateAllUnshippedOrdersCronJob2')) {
            $admin = new AdminController();
            wp_schedule_event(time(), 'daily', 'updateAllUnshippedOrdersCronJob2');
        }
    }

    // Add rest routes
    add_action('rest_api_init', array(&$rest, 'addRestRoutes'));
}
