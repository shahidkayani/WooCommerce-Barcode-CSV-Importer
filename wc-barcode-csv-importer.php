<?php
/*
Plugin Name: WooCommerce Barcode CSV Importer
Description: Import WooCommerce products via CSV with barcode scanning and mapping options.
Version: 1.0
Author: Shahid Maqsood
*/

// Register admin menu
add_action('admin_menu', 'wc_csv_importer_menu');
function wc_csv_importer_menu() {
    add_menu_page('Barcode CSV Importer', 'CSV Importer', 'manage_options', 'wc_csv_importer', 'wc_csv_importer_page');
}

// Enqueue Scripts
add_action('admin_enqueue_scripts', 'wc_csv_importer_scripts');
function wc_csv_importer_scripts() {
    wp_enqueue_script('wc-barcode-ajax', plugins_url('barcode-ajax.js', __FILE__), array('jquery'), null, true);
}

// Admin Page Content
function wc_csv_importer_page() {
    echo '<h1>WooCommerce Barcode CSV Importer</h1>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="csv_file" />';
    submit_button('Import CSV');
    echo '</form>';
    
    if ($_FILES['csv_file']) {
        wc_process_csv($_FILES['csv_file']['tmp_name']);
    }
}

// Process CSV File
function wc_process_csv($csv_file) {
    $file = fopen($csv_file, 'r');
    $row_count = 0;
    
    while (($data = fgetcsv($file)) !== FALSE) {
        // Map CSV fields to WooCommerce
        $product_data = wc_map_csv_to_product($data);
        wc_create_or_update_product($product_data);
        $row_count++;
    }
    fclose($file);
    echo "<p>Imported {$row_count} rows.</p>";
}

// Map CSV fields to WooCommerce
function wc_map_csv_to_product($data) {
    // Customize mappings here
    return [
        'sku' => $data[0],
        'post_title' => $data[1],
        'regular_price' => $data[2],
        'stock_quantity' => $data[3]
    ];
}

// Create or update product
function wc_create_or_update_product($product_data) {
    $product_id = wc_get_product_id_by_sku($product_data['sku']);
    $product = $product_id ? wc_get_product($product_id) : new WC_Product();

    $product->set_name($product_data['post_title']);
    $product->set_regular_price($product_data['regular_price']);
    $product->set_stock_quantity($product_data['stock_quantity']);
    $product->save();
}

add_action('wp_ajax_wc_find_product_by_barcode', 'wc_find_product_by_barcode');
function wc_find_product_by_barcode() {
    $barcode = $_POST['barcode'];
    $product_id = wc_get_product_id_by_sku($barcode);
    
    if ($product_id) {
        $product = wc_get_product($product_id);
        wp_send_json_success([
            'name' => $product->get_name(),
            'stock' => $product->get_stock_quantity()
        ]);
    } else {
        wp_send_json_error();
    }
}

?>
