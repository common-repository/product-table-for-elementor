<?php

function ptew_check_dependency_elementor()
{
    // Get the plugin name.
    $plugin_name = esc_html__('Product Table for Elementor - WooCommerce', 'product-table-for-elementor');

    // Get the Elementor plugin name.
    $elementor_name = esc_html__('Elementor', 'product-table-for-elementor');

    // Get the Elementor installation link.
    $elementor_install_link = esc_url(admin_url('plugin-install.php?s=Elementor&tab=search&type=term'));

    // Get the notice message.
    $notice_message = sprintf(
        esc_html__(
            '%1$s requires %2$s to be installed and activated to function properly. %3$s',
            'product-table-for-elementor'
        ),
        "<strong>$plugin_name</strong>",
        "<strong>$elementor_name</strong>",
        '<a href="' . $elementor_install_link . '">' . esc_html__('Please click on this link and install Elementor', 'product-table-for-elementor') . '</a>'
    );

    // Display the notice message.
    printf(
        '<div class="notice notice-warning is-dismissible"><p style="padding: 13px 0">%1$s</p></div>',
        esc_attr($notice_message)
    );
}


/**
 * Output PHP notice, if the PHP version is below 5.4
 */
function ptew_check_dependency_php()
{
    // Get the current PHP version.
    $php_version = phpversion();

    // Check if the PHP version is below 5.4.
    if (version_compare($php_version, '5.4.0', '<')) {
        // Get the error message.
        $message = esc_html__(
            'Product Table Elementor requires PHP version 5.4+, the plugin is currently NOT ACTIVE.',
            'product-table-for-elementor'
        );

        // Wrap the error message in a `div` element with the class `error`.
        $html_message = sprintf(
            '<div class="error">%s</div>',
            wpautop($message)
        );

        // Output the error message.
        echo wp_kses_post($html_message);
    }
}


/**
 * Load plugin text domain.
 */
function ptew_product_textdomain()
{
    // Load the plugin text domain.
    load_plugin_textdomain(
        'product-table-for-elementor',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}

/**
 * Create a category for WooCommerce Module elements.
 */
function ptew_product_module_category()
{
    // Get the Elementor instance.
    $elementor = \Elementor\Plugin::instance();

    // Add a category for Product Table Elementor elements.
    $elementor->elements_manager->add_category(
        'ptew-addons-elementor',
        [
            'title' => __('WooCommerce Product Table', 'product-table-for-elementor'),
            'icon'  => 'font',
        ],
        1
    );
}

/**
 * Check if WooCommerce is installed and activate required modules.
 */
function ptew_woocommerce_product_modules() {
  // Check if the WooCommerce plugin is installed and activated.
  if ( ! class_exists( 'WooCommerce' ) ) {
    return;
  }

  // Load modules.
  $modules = [
    'ptew-woocommerce-product-table',
  ];

  foreach ( $modules as $module ) {
    require_once PTEW_PRODUCT_ELEMENTOR_PATH . "modules/$module.php";
  }
}

/**
 * Enqueue styles for Product Elementor.
 *
 * This function enqueues the necessary CSS styles for Product Elementor plugin.
 * It's used to ensure that the stylesheets are loaded properly.
 */
function ptew_product_elementor_scripts() {
	wp_enqueue_style( 'ptew-product-styles', PTEW_PRODUCT_ELEMENTOR_URL . 'assets/css/ep-elements.css' );
}

