<?php

namespace WarehouseSpace\WP\Classes;

use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\WP\Controllers\AdminController;

class ProductSync
{
    protected $statuses         = ['private', 'publish'];
    protected $types            = ['variable', 'simple'];
    protected $virtual          = false;
    protected $downloadable     = false;
    protected $orderBy          = ['ID' => 'ASC'];
    protected $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function getProductSyncLimit()
    {
        return AdminController::PRODUCT_SYNC_LIMIT;
    }

    public function getProductSyncPage()
    {
        return isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
    }

    public function getProductsTotal()
    {
        global $wpdb;
        $args = [
            'status'            => $this->statuses,
            'type'              => $this->types,
            'downloadable'      => $this->downloadable,
            'virtual'           => $this->virtual,
            'orderby'           => $this->orderBy,
            'limit'             => -1,
            'return'            => 'ids',
            'paginate'          => false,
        ];
        $products = wc_get_products($args);
        return count($products);
    }

    public function getSynchronizedProductsTotal()
    {
        global $wpdb;
        $args = [
            'status'            => $this->statuses,
            'type'              => $this->types,
            'downloadable'      => $this->downloadable,
            'virtual'           => $this->virtual,
            'orderby'           => $this->orderBy,
            'limit'             => 0,
            'wareSpaceIndexed'  => 1,
            'return'            => 'objects',
            'paginate'          => true,
        ];
        $products = wc_get_products($args);
        return $products->total;
    }

    public function addWarehouseIndexedMeta($query, $query_vars)
    {
        if (isset($query_vars['wareSpaceIndexed'])) {
            if ($query_vars['wareSpaceIndexed'] === '') {
                $query['meta_query'][] = array(
                    'key' => 'wareSpaceIndexed',
                    'value' => '',
                    'compare' => 'NOT EXISTS',
                );
            } else {
                $query['meta_query'][] = array(
                    'key' => 'wareSpaceIndexed',
                    'value' => esc_attr($query_vars['wareSpaceIndexed']),
                );
            }
        }
        return $query;
    }

    public function getProductSyncData($product, $id, $title, $description, $category, $images, $weightUnit, $parentId = false)
    {
        $height = $product->get_height();
        $width = $product->get_width();
        $depth = $product->get_length();
        $weight = $product->get_weight();
        $images = $this->getImageUrlsFromIds($images);
        $price = $product->get_regular_price();
        return [
            'id'            => ($parentId === false) ? $id : $parentId,
            'ProductID'     => $id,
            'Article'       => $product->get_sku(),
            'BuyPrice'      => 0,
            'Category'      => $category,
            'Description'   => strip_shortcodes(wp_strip_all_tags($description)),
            'Title'         => $title,
            'Images'        => $images,
            'ItemDepth'     => (float)$depth,
            'ItemHeight'    => (float)$height,
            'ItemWeight'    => (float)$weight,
            'ItemWidth'     => (float)$width,
            'Manufacturer'  => '',
            'MinQuantity'   => 0,
            'Model'         => '',
            'SellPrice'     => (float)$price,
            'Supplier'      => '',
            'UOM'           => 'each',
            'WeightCat'     => $weightUnit,
            'Barcode'       => '',
            'Currency'      => get_woocommerce_currency(),
        ];
    }

    public function getImageUrlsFromIds($array)
    {
        $newArray = [];
        foreach ($array as $imageId) {
            $newArray[] = wp_get_attachment_url($imageId);
        }
        return $newArray;
    }

    public function productToSyncArray($product, $weightUnit)
    {
        $data = [];
        $id = $product->get_id();
        $categories = get_the_terms($id, 'product_cat');
        $category = '';
        if (isset($categories[0])) {
            $category = $categories[0]->name;
        }
        $type = $product->get_type();
        $title = get_the_title($id);
        $description = $product->get_short_description();
        if (!is_string($description) || strlen($description) === 0) {
            $description = $product->get_description();
        }
        if ($type === 'variable') {
            foreach ($product->get_children() as $child_id) {
                $variation = wc_get_product($child_id);
                $images = [];
                $imgId = $variation->get_image_id();
                if (strlen($imgId) > 0) {
                    $images[] = $imgId;
                }
                $title = $variation->get_name();
                $data[] = $this->getProductSyncData($variation, $child_id, $title, $description, $category, $images, $weightUnit, $id);
            }
        } else if ($type === 'simple') {
            $images = $product->get_gallery_image_ids();
            $imgId = $product->get_image_id();
            if (strlen($imgId) > 0) {
                $images[] = $imgId;
            }
            $data[] = $this->getProductSyncData($product, $id, $title, $description, $category, $images, $weightUnit);
        }
        return $data;
    }

    public function productSyncAjax()
    {
        $start = microtime(1);
        $args = [
            'status'            => $this->statuses,
            'type'              => $this->types,
            'downloadable'      => $this->downloadable,
            'virtual'           => $this->virtual,
            'orderby'           => $this->orderBy,
            'limit'             => $this->getProductSyncLimit(),
            'page'              => $this->getProductSyncPage(),
            'return'            => 'objects',
            'paginate'          => true,
        ];
        $products = wc_get_products($args);
        $productArray = [];
        $weightUnit = get_option('woocommerce_weight_unit');
        $ids = [];
        foreach ($products->products as $product) {
            $productData = $this->productToSyncArray($product, $weightUnit);
            if ($productData !== false) {
                $productArray = array_merge($productArray, $productData);
                foreach ($productData as $data) {
                    $ids[] = $data['id'];
                }
            }
        }
        $localTime = microtime(1) - $start;
        $config = $this->controller->getCredentials();
        $start = microtime(1);
        if ($config !== false) {
            try {
                $core = new MainController($config);
                $result = $core->exportProductsBulk($productArray);
            } catch (\Exception $e) {
                $this->controller->handleErrors($e);
            }
            $externalTime = microtime(1) - $start;

            foreach ($result['data']['hit'] as $id) {
                update_post_meta($id, 'wareSpaceIndexed', 1);
            }
            wp_send_json([
                'hitCount'  => count($result['data']['hit']),
                'missed'    => $result['data']['missed'],
                'errors'    => $result['data']['errors'],
                'hit'       => $result['data']['hit'],
                'sentIds'   => $ids,
                'localTime' => $localTime,
                'externalTime' => $externalTime,
            ]);
        }
    }

    public function exportProductToWarehouse($id, $config)
    {
        $status = get_post_status($id);
        if (in_array($status, $this->statuses)) {
            $product = wc_get_product($id);
            $weightUnit = get_option('woocommerce_weight_unit');
            $productData = $this->productToSyncArray($product, $weightUnit);
            $core = new MainController($config);
            try {
                $result = $core->exportProducts($productData);
                foreach ($result['data']['hit'] as $i) {
                    update_post_meta($i, 'wareSpaceIndexed', 1);
                }
            } catch (\Exception $e) {
                $this->controller->handleErrors($e);
            }
        }
    }
}
