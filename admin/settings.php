<?php

use WarehouseSpace\Controllers\MainController;

?>
<div class="container">
    <?php if (!empty($successMsg)) : ?>
        <?php foreach ($successMsg as $msg) : ?>
            <div class="notice notice-success is-dismissible"> 
                <p><strong><?php echo $msg ?></strong></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($errorMsg)) : ?>
        <?php foreach ($errorMsg as $msg) : ?>
            <div class="notice notice-error is-dismissible"> 
                <p><strong><?php echo $msg ?></strong></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 class="warehousespace-heading"><img src="<?php echo plugins_url('../imgs/logo_settings_header.png', __FILE__) ?>"><span>Warehouse</span>.space</h2>
    <?php if ($credentials !== false) : ?>
        <h2><?php _e('Products', 'warehousespace') ?></h2>
        <div class="buttons">
            <button class="button button-primary" id="synchronize-products"><?php _e('Synchronize products', 'warehousespace') ?></button>
        </div>
        <div class="statistics">
            <span class="warehousespace-important"><?php _e('Synchronized products', 'warehousespace') ?>: <?php echo '<span class="hitCount">'.$synchronized.'</span>/'.$total ?></span>
            <div class="showWhenInProgress" style="display: none">
                <strong>Please wait...</strong><br>
                <input type="hidden" name="progress_step" id="progress_step" value="<?php echo ($syncStep); ?>">
                <progress max="100" value="0" class="warehousespace-progress"><?php _e('Progress', 'warehousespace').': ' ?><span class="progressValue">0</span></progress>
            </div>
        </div>
    <?php endif; ?>

    <div class="notice warehousespace-notice" style="margin-top: 10px">
        Ship your products faster and cheaper by storing them closer to your customers.
        <br>
        Use Warehouse.Space distribution centers to reduce your shipping costs. We pick, pack and dispatch products to your customers with same day delivery in a number of major cities, and next day for many more.
        <br>
        <br>
        Sign up at <a href="https://warehouse.space">https://warehouse.space</a> fo free, and get your license key and warehouse number and start shipping to customers world wide at local postal rates.
        <br>
    </div>

    <h2><?php _e('Connection information', 'warehousespace') ?></h2>
    <div class="credentialsForm">
        <form method="POST" action="">
            <div class="warehousespace-horizontal-form-wrapper">
                <div class="warehousespace-horizontal-form-element">
                    <label for="accountkey">License key</label><br>
                    <input name="accountkey" id="accountkey" type="text" <?php echo (isset($credentials[MainController::ACCOUNTKEY]) ? 'value="'.$credentials[MainController::ACCOUNTKEY].'"' : '') ?>>
                </div>
                <div class="warehousespace-horizontal-form-element">
                    <label for="warehouse">Warehouse number</label><br>
                    <input name="warehouse" id="warehouse" type="text" <?php echo (isset($credentials[MainController::WAREHOUSE]) ? 'value="'.$credentials[MainController::WAREHOUSE].'"' : '') ?>>
                </div>
            </div>
            <input class="button button-primary" type="submit" name="credentials" value="<?php _e('Save', 'warehousespace') ?>">
        </form>
    </div>
    <div class="notice warehousespace-notice" style="margin-top: 10px">
        To help us pick, pack and dispatch the correct products to your customers we need your help.
        <br>
        Every product in our warehouse needs a barcode. If a product doesn’t have a barcode when you ship it to one of our warehouses, then we will add a barcode to the product packaging, which you will incur a small fee for us doing this.
        <br>
        This barcode is what we scan, so that we can confirm we have  correctly picked the product your customer has ordered.
        <br>
        To help us with this process we need you to ensure that every product you have in your store has a SKU value defined, that matches the barcode on the product. The barcode must be unique per product, including variants.
        <br>
        We are unable to receive a product into our warehouse that you have not defined its SKU value in Woo Commerce.
        <br>
        Thanks for your assistance in helping us, ship your products perfectly every time.
        <br><br>
        <div class="image-centered-with-desc">
            <img src="<?php echo plugins_url('../imgs/SKU_simple_product_screenshot.png', __FILE__) ?>"><br>
            <span>Simple product SKU</span>
        </div>    
        <br><br>
        <div class="image-centered-with-desc">
            <img src="<?php echo plugins_url('../imgs/SKU_variable_product_screenshot.png', __FILE__) ?>"><br>
            <span>Variable product SKU</span>
        </div>
    </div>
</div>