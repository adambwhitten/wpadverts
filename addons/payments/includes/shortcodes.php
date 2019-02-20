<?php
/**
 * List of registered shortcodes
 * 
 * @package     Adverts
 * @copyright   Copyright (c) 2015, Grzegorz Winiarski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Register shortcodes
add_shortcode('adverts_payments_checkout', 'shortcode_payments_checkout');

// Shortcode functions

/**
 * Generates HTML for [shortcode_payments_complete] shortcode
 * 
 * The shortcodes allows completeing pending payments.
 * 
 * @param array $atts Shorcode attributes
 * @since 0.1
 * @return string Fully formatted HTML for adverts list
 */
function shortcode_payments_checkout( $atts ) {

    $params = shortcode_atts(array(
        'name' => 'default',
    ), $atts, 'shortcode_payments_complete' );

    extract( $params );
    
    include_once ADVERTS_PATH . '/includes/class-flash.php';
    include_once ADVERTS_PATH . '/includes/shortcodes.php';

    wp_enqueue_style( 'adverts-frontend' );
    wp_enqueue_style( 'adverts-icons' );
    wp_enqueue_style( 'adverts-payments-frontend' );
    
    wp_enqueue_script( 'adext-payments' );
    wp_enqueue_script( 'adverts-frontend' );

    // 129 - payment object not created
    // 133 - payment object created

    $adverts_flash = array( "error" => array(), "info" => array() );
    
    $payment_id = adverts_get_post_id_from_hash( adverts_request( "advert-hash" ) );
    
    if( ! $payment_id ) {
        ob_start();
        $adverts_flash["error"][0] = array(
            "message" => __( "The provided payment hash could not be found.", "adverts" ),
            "icon" => "adverts-icon-cancel"
        );
        adverts_flash( $adverts_flash );
        return ob_get_clean();
    }
    
    $payment = get_post( $payment_id );
    
    if( $payment === null ) {
        ob_start();
        $adverts_flash["error"][0] = array(
            "message" => __( "The payment does not exist or the payment hash is incorrect.", "adverts" ),
            "icon" => "adverts-icon-cancel"
        );
        adverts_flash( $adverts_flash );
        return ob_get_clean();
    }
    
    $post_id = get_post_meta( $payment_id, '_adverts_object_id', true );
    $post = get_post( $post_id );

    $adverts_flash["info"][0] = array(
        "message" => sprintf( __( "<p><strong>Your Payment Is Required</strong><p>Please complete payment for the <em>'%s'</em> Ad posting to have it published.</p>"), $post->post_title ),
        "icon" => "adverts-icon-basket"
    );
    
    $listing = get_post( get_post_meta( $post->ID, "payments_listing_type", true ) );
    $listing_id = $listing->ID;
    
    $price = get_post_meta($listing_id, 'adverts_price', true);
    
    if( $payment->payment_status === "completed" ) {
        ob_start();
        $adverts_flash["info"][0] = array(
            "message" => __( "The payment has been already approved and your Ad should be published soon.", "adverts" ),
            "icon" => "adverts-icon-ok"
        );
        adverts_flash( $adverts_flash );
        return ob_get_clean();
    } else {

        ob_start();
        // wpadverts/addons/payments/templates/add-payment.php
        include ADVERTS_PATH . 'addons/payments/templates/add-payment.php';
        return ob_get_clean();
    }
}