<?php
/**
 * Plugin Name: Authorize.net Gateway for WP-eCommerce
 * Description: Authorize.net
 * Version: 1.0
 * Author: Pye Brook Company, Inc.
 * Author URI: http://pyebrook.com/
 **/

/*
 * All plugin functions are prefixed with 'pbci_ap_', the ap is short for administrative payment
 */
define( 'PBCI_AUTHNET', 'pbci_auth_net_text_domain' );


define( 'WPECAUTHNET_PLUGIN_NAME', 'wpec_auth_net' );
//define( 'WPECAUTHNET_CLASSES', plugin_dir_path( __FILE__ ) . 'wpec_auth_net/classes/' );

//$nzshpcrt_gateways[ $num ] = array(
//	'name'                   => __( 'Authorize.net AIM/CIM/ARB', PBCI_AUTHNET ),
//	'api_version'            => 2,
//	'class_name'             => WPECAUTHNET_PLUGIN_NAME,
//	'image'                  => WPSC_URL . '/images/cc.gif',
//	'requirements'           => array(),
//	'has_recurring_billing'  => true,
//	'wp_admin_cannot_cancel' => true,
//	'display_name'           => __( 'Authorize.Net', PBCI_AUTHNET ),
//	'form'                   => 'form_auth_net',
//	'submit_function'        => 'submit_auth_net',
//	'payment_type'           => 'credit_card',
//	'internalname'           => WPECAUTHNET_PLUGIN_NAME
//);


//include_once( 'wpec_auth_net/authorize-net.php' );
//add_action( 'wpsc_pre_load', 'pbci_an_setup_gateway' );

function pbci_an_setup_gateway() {
	$enable_admin_payment_option = true;

	if ( $enable_admin_payment_option ) {
		add_filter( 'wpsc_merchants_modules', 'pbci_an_add_gateway' );
	}
}

function pbci_an_add_gateway( $gateways ) {

	//if ( current_user_can ( 'manage_options' ) || pbci_an_is_admin_logged_in() || ($_SERVER['HTTP_HOST'] == 'sparklegear.local') ) {


	require_once( $f = plugin_dir_path( __FILE__ ) .  'authorize-net.php' );

	//$nzshpcrt_gateways[ $num ] = array(
//	'name'                   => __( 'Authorize.net AIM/CIM/ARB', PBCI_AUTHNET ),
//	'api_version'            => 2,
//	'class_name'             => WPECAUTHNET_PLUGIN_NAME,
//	'image'                  => WPSC_URL . '/images/cc.gif',
//	'requirements'           => array(),
//	'has_recurring_billing'  => true,
//	'wp_admin_cannot_cancel' => true,
//	'display_name'           => __( 'Authorize.Net', PBCI_AUTHNET ),
//	'form'                   => 'form_auth_net',
//	'submit_function'        => 'submit_auth_net',
//	'payment_type'           => 'credit_card',
//	'internalname'           => WPECAUTHNET_PLUGIN_NAME
//);

	$gateway = array(
		'name'                   => __( 'PBCI Authorize.net AIM/CIM/ARB', PBCI_AUTHNET ),
		'api_version'            => 2.0,
		'class_name'             => WPECAUTHNET_PLUGIN_NAME,
		'image'                  => WPSC_URL . '/images/cc.gif',
		'requirements'           => array(
			/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
			'php_version' => 5.0,
		),
		'has_recurring_billing'  => true,
		'wp_admin_cannot_cancel' => true,
		'display_name'           => __( 'PBCI Authorize.Net', PBCI_AUTHNET ),
		'form'                   => 'form_auth_net',
		'submit_function'        => 'submit_auth_net',
		'payment_type'           => 'credit_card',
		'internalname'           => WPECAUTHNET_PLUGIN_NAME,
	);

	$gateways[] = $gateway;

	return $gateways;
}

