<?php
/*
 * Plugin Name: Elementor Product Table for WooCommerce
 * Requires Plugins: elementor, woocommerce
 * Description: Display your WooCommerce products in an intuitive table layout.
 * Plugin URI: http://userelements.com/elementor-product-table
 * Version: 0.3
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: userelements
 * Author URI: http://userelements.com/
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: product-table-for-elementor
 * Domain Path: /languages

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'PTEW_PRODUCT_ELEMENTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'PTEW_PRODUCT_ELEMENTOR_URL', plugins_url( '/', __FILE__ ) );

require_once PTEW_PRODUCT_ELEMENTOR_PATH . 'inc/elementor-product-essential.php';

add_action( 'plugins_loaded', 'ptew_product_table_elementor_init' );

/**
 * Initializes the Product Table Elementor plugin.
 */
function ptew_product_table_elementor_init() {
	// Check if Elementor installed and activated
	if ( ! did_action( 'elementor/loaded' ) ) {
	    add_action( 'admin_notices', 'ptew_check_dependency_elementor' );
	    return;
	}

	if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
		add_action( 'admin_notices', 'ptew_check_dependency_php' );
		return;
	}

	add_action( 'init', 'ptew_product_textdomain' );
	add_action( 'elementor/init', 'ptew_product_module_category' );
	add_action( 'elementor/init', 'ptew_woocommerce_product_modules' );
	add_action( 'wp_enqueue_scripts', 'ptew_product_elementor_scripts' );
}


/**
 * Retrieve a list of terms from a specified taxonomy.
 *
 * @param string $taxonomy The name of the taxonomy to retrieve terms from.
 * @param string $key      Optional. The property of the term object to use as the key in the resulting array.
 *                         Default is 'term_id'.
 * @return array           An associative array where keys are term IDs (or specified keys) and values are term names.
 */
function ptew_get_product_terms_list($taxonomy = 'category', $key = 'term_id'){
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => true,
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return [];
    }

    $options = [];
    foreach ($terms as $term) {
        $options[$term->{$key}] = $term->name;
    }

    return $options;
}


/**
 * Retrieve a list of product meta keys.
 *
 * @global object $wpdb WordPress database access abstraction object.
 * @return array       An array containing all product meta keys.
 */
function ptew_get_product_meta_list() {
    global $wpdb;

    $meta_keys          = ptew_fetch_product_meta_keys($wpdb);
    $filtered_keys      = ptew_filter_excluded_keys($meta_keys);
    $additional_keys    = ptew_get_additional_keys();
    $all_keys           = ptew_merge_keys($filtered_keys, $additional_keys);

    return $all_keys;
}


/**
 * Fetches all product meta keys from the WordPress database.
 *
 * @param object $wpdb WordPress database access abstraction object.
 * @return array       An array containing all product meta keys.
 */
function ptew_fetch_product_meta_keys($wpdb) {
    $table_prefix = $wpdb->prefix;

    $query = "
        SELECT meta_key
        FROM {$table_prefix}postmeta
        WHERE post_id IN (
            SELECT ID
            FROM {$table_prefix}posts
            WHERE post_type = 'product'
        )";

    return $wpdb->get_col($query);
}


/**
 * Filters out excluded meta keys from a given array of meta keys.
 *
 * @param array $meta_keys An array containing meta keys.
 * @return array           An array containing meta keys excluding the excluded keys.
 */
function ptew_filter_excluded_keys($meta_keys) {
    $excluded_keys = array(
        '_sku', '_wpcom_is_markdown', '_wp_old_slug', '_edit_lock', '_price',
        '_regular_price', '_sale_price', '_stock', '_stock_status', '_tax_status',
        '_tax_class', '_edit_last', '_manage_stock', '_backorders', '_wc_review_count',
        '_product_version', 'total_sales', '_sold_individually', '_virtual',
        '_downloadable', '_download_limit', '_download_expiry', '_wc_average_rating',
        '_product_image_gallery', '_wc_rating_count', '_downloadable_files',
        '_product_attributes', '_thumbnail_id'
    );

    return array_filter($meta_keys, function ($key) use ($excluded_keys) {
        return !in_array($key, $excluded_keys);
    });
}


/**
 * Retrieves additional meta keys to be included in the product meta keys list.
 *
 * @return array An array containing additional meta keys.
 */
function ptew_get_additional_keys() {
    return array(
        'Add to Cart', 'Average Rating', 'Backorder Allowed', 'Category',
        'Description', 'Download Expiry', 'Download Limit', 'Downloadable',
        'Downloadable files', 'ID', 'Image', 'Image Gallery', 'Manage Stock',
        'Number of reviews', 'Price', 'Product Title', 'Rating', 'Rating count',
        'Regular Price', 'Sale Price', 'SKU', 'Sold Individually', 'Stock Quantity',
        'Stock Status', 'Tags', 'Tax Status', 'Attributes', 'Total Sold', 'Virtual'
    );
}


/**
 * Merges two arrays of meta keys, ensuring uniqueness of keys.
 *
 * @param array $filtered_keys   An array containing filtered meta keys.
 * @param array $additional_keys An array containing additional meta keys.
 * @return array                 An array containing merged meta keys with uniqueness maintained.
 */
function ptew_merge_keys($filtered_keys, $additional_keys) {
    return array_merge($additional_keys, array_unique($filtered_keys));
}
