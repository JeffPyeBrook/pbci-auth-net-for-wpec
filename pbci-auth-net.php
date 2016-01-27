<?php
/*
Plugin Name: AAAA PBCI Authorize.net plugin
Plugin URI:
Description:
Version: 3.0
Author:
Author URI:
*/



/*
** Copyright 2010-2013, Pye Brook Company, Inc.
**
** Licensed under the Pye Brook Company, Inc. License, Version 1.0 (the "License");
** you may not use this file except in compliance with the License.
** You may obtain a copy of the License at
**
**     http://www.pyebrook.com/
**
** This software is not free may not be distributed, and should not be shared.  It is governed by the
** license included in its original distribution (license.pdf and/or license.txt) and by the
** license found at www.pyebrook.com.
*
** This software is copyrighted and the property of Pye Brook Company, Inc.
**
** See the License for the specific language governing permissions and
** limitations under the License.
**
** Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
*/

add_action( 'wpsc_after_register_gateways', 'pbci_auth_register_gateway_file' );

function pbci_auth_register_gateway_file( $gateway_controller ) {
	$gateway_file = dirname( __FILE__ ) . '/Authorize_Net_Gateway.php';
	WPSC_Payment_Gateways::register_file( $gateway_file );
}
