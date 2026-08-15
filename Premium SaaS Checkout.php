<?php
/**
 * Plugin Name: Premium SaaS Checkout
 * Plugin URI: https://example.com
 * Description: Modern SaaS Multi-Step WooCommerce Checkout
 * Version: 1.0.0
 * Author: Sajid Ashraf
 * License: GPL2+
 */

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

define('PSC_VERSION', '1.0.0');
define('PSC_PLUGIN_FILE', __FILE__);
define('PSC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PSC_PLUGIN_PATH', plugin_dir_path(__FILE__));

/*
|--------------------------------------------------------------------------
| DEBUG
|--------------------------------------------------------------------------
*/

if (!defined('PSC_DEBUG')) {
    define('PSC_DEBUG', true);
}

function psc_log($message)
{
    if (PSC_DEBUG && defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[PSC] ' . print_r($message, true));
    }
}

/*
|--------------------------------------------------------------------------
| CHECK WOOCOMMERCE
|--------------------------------------------------------------------------
*/

register_activation_hook(__FILE__, 'psc_activate_plugin');

function psc_activate_plugin()
{
    if (!class_exists('WooCommerce')) {

        deactivate_plugins(plugin_basename(__FILE__));

        wp_die(
            'WooCommerce must be installed and activated first.',
            'Plugin Activation Error',
            array(
                'back_link' => true
            )
        );
    }
}

/*
|--------------------------------------------------------------------------
| ADMIN NOTICE
|--------------------------------------------------------------------------
*/

add_action('admin_notices', function () {

    if (!class_exists('WooCommerce')) {

        echo '<div class="notice notice-error">
                <p><strong>Premium SaaS Checkout</strong> requires WooCommerce.</p>
              </div>';
    }

});

/*
|--------------------------------------------------------------------------
| LOAD ASSETS
|--------------------------------------------------------------------------
*/

add_action('wp_enqueue_scripts', 'psc_enqueue_assets');

function psc_enqueue_assets()
{

    if (!is_checkout()) {
        return;
    }

    wp_enqueue_style(
        'psc-style',
        PSC_PLUGIN_URL . 'premium-checkout.css',
        array(),
        PSC_VERSION
    );

    wp_enqueue_script(
        'psc-script',
        PSC_PLUGIN_URL . 'premium-checkout.js',
        array('jquery'),
        PSC_VERSION,
        true
    );

    wp_localize_script(
        'psc-script',
        'psc_ajax',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('psc_nonce')
        )
    );

}

/*
|--------------------------------------------------------------------------
| PLUGIN LOADED
|--------------------------------------------------------------------------
*/

add_action('plugins_loaded', function () {

    psc_log('Plugin Loaded Successfully');

});




/*
|--------------------------------------------------------------------------
| REMOVE DEFAULT WOOCOMMERCE CHECKOUT
|--------------------------------------------------------------------------
*/

remove_action(
    'woocommerce_checkout_order_review',
    'woocommerce_order_review',
    10
);

remove_action(
    'woocommerce_checkout_order_review',
    'woocommerce_checkout_payment',
    20
);

/*
|--------------------------------------------------------------------------
| CUSTOM CHECKOUT WRAPPER
|--------------------------------------------------------------------------
*/

add_action('woocommerce_before_checkout_form', 'psc_checkout_wrapper_start', 5);

function psc_checkout_wrapper_start()
{
    if (!is_checkout()) {
        return;
    }

    ?>
    
    <div id="psc-checkout">

        <div class="psc-progress">

            <div class="psc-step active">
                <span>1</span>
                <p>Choose Package</p>
            </div>

            <div class="psc-line"></div>

            <div class="psc-step">
                <span>2</span>
                <p>Checkout</p>
            </div>

            <div class="psc-line"></div>

            <div class="psc-step">
                <span>3</span>
                <p>Payment</p>
            </div>

        </div>

        <div class="psc-grid">

            <div class="psc-left">

    <?php
}

add_action('woocommerce_checkout_before_order_review', function(){

    echo '</div><div class="psc-right">';

},5);

add_action('woocommerce_checkout_after_order_review', function(){

    echo '</div></div></div>';

},99);


add_action(
    'woocommerce_checkout_order_review',
    'psc_custom_summary',
    5
);

function psc_custom_summary()
{
    ?>

    <div class="psc-summary">

        <h2>Order Summary</h2>

        <?php woocommerce_order_review(); ?>

    </div>

    <?php
}


add_action(
    'woocommerce_checkout_order_review',
    'psc_custom_payment',
    20
);

function psc_custom_payment()
{
    ?>

    <div class="psc-payment">

        <?php woocommerce_checkout_payment(); ?>

    </div>

    <?php
}

add_action('wp_head', 'psc_inline_css');

function psc_inline_css()
{
    if (!is_checkout()) {
        return;
    }
?>
<style>

#psc-checkout{
    max-width:1400px;
    margin:40px auto;
    padding:20px;
}

.psc-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:40px;
}

.psc-left,
.psc-right{
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

@media(max-width:768px){

.psc-grid{
grid-template-columns:1fr;
}

}

</style>
<?php
}


add_action('wp_footer', 'psc_inline_js', 999);

function psc_inline_js()
{
    if (!is_checkout()) {
        return;
    }
?>
<script>

jQuery(function($){

    console.log("Premium Checkout Loaded");

    $(".psc-step").on("click",function(){

        console.log("Step Clicked");

    });

});

</script>
<?php
}