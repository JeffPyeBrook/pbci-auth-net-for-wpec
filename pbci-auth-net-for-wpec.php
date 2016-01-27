<?php
/*
Plugin Name: Authorize.net Credit Card Gateway for WP-eCommerce
Plugin URI: pyebrook.com
Description: Modern gateway built on the WP-eCommerce Merchant component 3.0 component interface available since WP-eCommerce 3.9. Compatible with WP-eCommerce Theme Engine 2.0
Version: 3.0
Author: Pye Brook Company, Inc. / Jeffrey Schutzman
Author URI: https://profiles.wordpress.org/jeffpyebrookcom/
*/



/*
** Copyright 2010-2016, Pye Brook Company, Inc.
**
**
** This software is provided under the GNU General Public License, version
** 2 (GPLv2), that covers its  copying, distribution and modification. The
** GPLv2 license specifically states that it only covers only copying,
** distribution and modification activities. The GPLv2 further states that
** all other activities are outside of the scope of the GPLv2.
**
** All activities outside the scope of the GPLv2 are covered by the Pye Brook
** Company, Inc. License. Any right not explicitly granted by the GPLv2, and
** not explicitly granted by the Pye Brook Company, Inc. License are reserved
** by the Pye Brook Company, Inc.
**
** This software is copyrighted and the property of Pye Brook Company, Inc.
**
** Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY
** WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
** A PARTICULAR PURPOSE.
**
*/

add_action( 'wpsc_after_register_gateways', 'pbci_auth_register_gateway_file' );

function pbci_auth_register_gateway_file( $gateway_controller ) {
	$gateway_file = dirname( __FILE__ ) . '/Authorize_Net_Gateway.php';
	WPSC_Payment_Gateways::register_file( $gateway_file );
}
