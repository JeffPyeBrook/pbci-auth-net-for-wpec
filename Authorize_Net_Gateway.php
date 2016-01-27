<?php
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

if ( ! defined( 'PBCI_AUTHNET' ) ) {
	define( 'PBCI_AUTHNET', 'pbciauth-net' );
}

require 'vendor/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class WPSC_Payment_Gateway_Authorize_Net_Gateway extends WPSC_Payment_Gateway {

	private $use_production = false;

	private $name_on_card = '';
	private $card_number = '';
	private $expiration_month = '';
	private $expiration_year = '';
	private $card_verification_code = '';

	private $sandbox_mode = true;
	private $api_login_id = '';
	private $api_transaction_key = '';

	private $sandbox_mode_can_change = false;
	private $api_login_id_can_change = false;
	private $api_transaction_key_can_change = false;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 3.9
	 */
	public function __construct( $options ) {
		parent::__construct();
		$this->title = __( 'Authorize.Net by Pye Brook Company, Inc.', 'wp-e-commerce' );
		add_action( 'wpsc_before_shipping_of_shopping_cart', array( $this, 'checkout_form' ) );

		if ( defined( 'AUTHORIZENET_API_LOGIN_ID' ) ) {
			$this->api_login_id = AUTHORIZENET_API_LOGIN_ID;
			$this->api_login_id_can_change = false;
		} else {
			$this->api_login_id = $this->setting->get( 'api_login_id' );
			$this->api_login_id_can_change = true;
		}

		if ( defined( 'AUTHORIZENET_TRANSACTION_KEY' ) ) {
			$this->api_transaction_key = AUTHORIZENET_TRANSACTION_KEY;
			$this->api_transaction_key_can_change = false;
		} else {
			$this->api_transaction_key = $this->setting->get( 'api_transaction_key' );
			$this->api_transaction_key_can_change = true;
		}

		if ( defined( 'AUTHORIZENET_SANDBOX' ) ) {
			$this->sandbox_mode = (bool)AUTHORIZENET_SANDBOX;
			$this->sandbox_mode_can_change = false;
		} else {
			$this->sandbox_mode = (bool)$this->setting->get( 'sandbox_mode' );
			$this->sandbox_mode_can_change = true;
		}

	}

	public function gateway_name() {
		return 'Authorize_Net_Gateway';
		//return $this->setting->get( 'gateway_name' );
	}

	function checkout_form() {
		global $gateway_checkout_form_fields;
		$gateway_name = $this->gateway_name();

		$checkOrCC                                     = $this->CreditCardForm();
		$gateway_checkout_form_fields[ $gateway_name ] = '<div id=\'checkorcc_select\'>';
		$form                                          = <<<EOT
<tr>
	<td colspan='2'>
		{$checkOrCC}
	</td>
</tr>
EOT;
		$gateway_checkout_form_fields[ $gateway_name ] .= $form;
		$gateway_checkout_form_fields[ $gateway_name ] .= '</div>';
	}

	function CreditCardForm() {
		$selected_month = $selected_year = $years = $months = '';
		$curryear       = date( 'Y' );

		//generate year options
		for ( $i = 0; $i < 10; $i ++ ) {
			$years .= "<option value='" . $curryear . "'>" . $curryear . "</option>\r\n";
			$curryear ++;
		}
		if ( isset( $_REQUEST['auth_net']['creditCard'] ) ) {
			$auth_net = $_REQUEST['auth_net']['creditCard'];
		} else {
			$auth_net = array(
				'name_on_card' => '',
				'card_number'  => '',
				'expiry'       => array( 'month' => '', 'year' => '' )
			);
		}
		if ( isset( $auth_net['expiry']['month'] ) && $auth_net['expiry']['month'] > 0 ) {
			$selected_month = "<option value='{$auth_net['expiry']['month']}' selected>{$auth_net['expiry']['month']}</option>\n";
			$selected_year  = "<option value='{$auth_net['expiry']['year']}' selected>{$auth_net['expiry']['year']}</option>\n";
		}

		$creditCardFormText = array(
			'appears-on-card-text'     => __( 'Name as It Appears on Card *', PBCI_AUTHNET ),
			'credit-card-number-text'  => __( 'Credit Card Number *', PBCI_AUTHNET ),
			'credit-card-expires-text' => __( 'Expiration *', PBCI_AUTHNET ),
			'cvv-text'                 => __( 'CVV *', PBCI_AUTHNET ),
			'01'                       => '01',
			'02'                       => '02',
			'03'                       => '03',
			'04'                       => '04',
			'05'                       => '05',
			'06'                       => '06',
			'07'                       => '07',
			'08'                       => '08',
			'09'                       => '09',
			'10'                       => '10',
			'11'                       => '11',
			'12'                       => '12'
		);

		return <<<EOF
	<div id='creditCardNew'>
		<input type='hidden' name='payType' id='payType'  value='creditCardForms'>
		<table border='0'>
		<tr>
			<td class='wpsc_CC_details'>{$creditCardFormText['appears-on-card-text']}</td>
			<td>
				<input type='text' value='{$auth_net['name_on_card']}' name='auth_net[creditCard][name_on_card]' class='authNetPaymentInput' style="width:100%;" />
			</td>
		</tr>
		<tr>
			<td class='wpsc_CC_details'>{$creditCardFormText['credit-card-number-text']}</td>
			<td>
				<input type='text' value='{$auth_net['card_number']}' name='auth_net[creditCard][card_number]' class='authNetPaymentInput' />
			</td>
		</tr>
		<tr>
			<td class='wpsc_CC_details'>{$creditCardFormText['credit-card-expires-text']}</td>
			<td>
				<select class='wpsc_ccBox authNetPaymentInput' name='auth_net[creditCard][expiry][month]' >
				" . $months . "
				{$selected_month}
				<option value='01'>{$creditCardFormText['01']}</option>
				<option value='02'>{$creditCardFormText['02']}</option>
				<option value='03'>{$creditCardFormText['03']}</option>
				<option value='04'>{$creditCardFormText['04']}</option>
				<option value='05'>{$creditCardFormText['05']}</option>
				<option value='06'>{$creditCardFormText['06']}</option>
				<option value='07'>{$creditCardFormText['07']}</option>
				<option value='08'>{$creditCardFormText['08']}</option>
				<option value='09'>{$creditCardFormText['09']}</option>
				<option value='10'>{$creditCardFormText['10']}</option>
				<option value='11'>{$creditCardFormText['11']}</option>
				<option value='12'>{$creditCardFormText['12']}</option>
				</select>
				<select class='wpsc_ccBox authNetPaymentInput' name='auth_net[creditCard][expiry][year]' >
				{$selected_year}
				" . $years . "
				</select>
			</td>
		</tr>
		<tr>
			<td class='wpsc_CC_details'>{$creditCardFormText['cvv-text']}</td>
			<td><input type='text' size='4' value='' maxlength='4' name='auth_net[creditCard][card_code]' class='authNetPaymentInput'/>
			</td>
		</tr>
		</table>
	</div>
EOF;
	}


	/**
	 * Load gateway only if curl is enabled (SDK requirement), PHP 5.3+ (same) and TEv2.
	 *
	 * @return bool Whether or not to load gateway.
	 */
	public static function load() {
		return true;
	}

	/**
	 * Displays the setup form
	 *
	 * @access public
	 * @since 3.9
	 * @uses WPSC_Checkout_Form::get()
	 * @uses WPSC_Checkout_Form::field_drop_down_options()
	 * @uses WPSC_Checkout_Form::get_field_id_by_unique_name()
	 * @uses WPSC_Payment_Gateway_Setting::get()
	 *
	 * @return void
	 */
	public function setup_form() {
		error_log( __FUNCTION__ );
		?>
<tr>
    <td colspan="2">
        <h4><?php _e( 'Authorize.net Credentials' ); ?></h4>
    </td>
</tr>
<tr>
    <td>
        <label for="auth-net-settings-api-login-id"><?php _e( 'API Login ID' ); ?></label>
    </td>
    <td>
	    <?php if ( $this->api_login_id_can_change ) { ?>
            <input type="text" name="<?php echo esc_attr( $this->setting->get_field_name( 'api_login_id' ) ); ?>" value="<?php echo esc_attr( $this->setting->get( 'api_login_id' ) ); ?>" id="auth-net-settings-api-login-id" />
		<?php } else { ?>
		    <i>Login ID already set in wp-config.php, not changeable here</i>
		<?php } ?>
    </td>
</tr>
<tr>
    <td>
        <label for="auth-net-settings-api-transaction-key"><?php _e( 'API Transaction Key' ); ?></label>
    </td>
    <td>
	    <?php if ( $this->api_transaction_key_can_change ) { ?>
		    <input type="text" name="<?php echo esc_attr( $this->setting->get_field_name( 'api_transaction_key' ) ); ?>" value="<?php echo esc_attr( $this->setting->get( 'api_transaction_key' ) ); ?>" id="auth-net-settings-api-transaction-key" />
	    <?php } else { ?>
		    <i>API Transaction already key set in wp-config.php, not changeable here</i>
	    <?php } ?>
    </td>
</tr>
<tr>
    <td>
        <label><?php _e( 'Test (Sandbox) Mode', 'wp-e-commerce' ); ?></label>
    </td>
    <td>
	    <?php if ( $this->sandbox_mode_can_change ) { ?>
		    <label><input <?php checked( $this->setting->get( 'sandbox_mode' ) ); ?> type="radio" name="<?php echo esc_attr( $this->setting->get_field_name( 'sandbox_mode' ) ); ?>" value="1" /> <?php _e( 'Yes', 'wp-e-commerce' ); ?></label>&nbsp;&nbsp;&nbsp;
		    <label><input <?php checked( (bool) $this->setting->get( 'sandbox_mode' ), false ); ?> type="radio" name="<?php echo esc_attr( $this->setting->get_field_name( 'sandbox_mode' ) ); ?>" value="0" /> <?php _e( 'No', 'wp-e-commerce' ); ?></label>
	    <?php } else { ?>
		    <i>Test (Sandbox) mode already set in wp-config.php, not changeable here</i>
	    <?php } ?>
    </td>
</tr>

		<tr>
		<td colspan="2">
		<p>
		<h3><?php _e( 'Instructions' ); ?></h3>
		<div>
			<p>
				The most secure way to configure the authorize.net payment gateway is to define the <code>api login id</code> and <code>api transaction key</code>
				in your <code>wp-config.php</code> file.</p><br>

			<h4>WARNING</h4>
			<p>If you enter the values here they are visible to any person who might have access to your adminstrative interface. Bad things can happen if the
				authorize.net credential information is exposed to the wrong person.</p>
				<p>One risk is a bad actor could use your charge account without your knowledge, taking money from people and perhaps
				sending it to themselves.</p>
				<p>Another risk is that a bad actor could easily change the credentials so that they fool you into thinking that they have completed payments when those payments
				never happend. Uou would then ship products to the bad actor without receiving the promised funds.</p><br>

			Create the settings for the authorize net service similiar to this:<br>
<pre>
define ( 'AUTHORIZENET_API_LOGIN_ID', '73rhks93hb' );
define ( 'AUTHORIZENET_TRANSACTION_KEY', '4FM3xH4938439fzd8z' );
define ( 'AUTHORIZENET_SANDBOX', false );
</pre>
<br>
<p>For support or enhancement requests see our web site at <a href="http://www.pyebrook.com">pyebrook.com</a></p>
<p>Check out our collection of WP-eCommerce plugins at <a href="http://pyebrook.com/store/">pyebrook.com/store/</a></p>
</div>
</td>
</tr>

		<?php
	}

	public function process() {

		$card_data = $_REQUEST['auth_net']['creditCard'];

		$this->name_on_card           = $card_data['name_on_card'];
		$this->card_number            = $card_data['card_number'];
		$this->expiration_month       = $card_data['expiry']['month'];
		$this->expiration_year        = $card_data['expiry']['year'];
		$this->card_verification_code = $card_data['card_code'];

		$this->invoice_number = $this->purchase_log->get( 'id' );

		if ( $this->charge_credit_card() ) {
			error_log( __FUNCTION__ . ' fake transaction complete success' );
		} else {
			error_log( __FUNCTION__ . ' fake transaction complete failure' );
		}
	}

	public function get_image_url() {
		return plugins_url( 'credit-card-montage-small.png', __FILE__ );
	}


	private function charge_credit_card() {

		$wpsc_checkout_form_data = $this->checkout_data->get_gateway_data();

		// Common setup for API credentials
		$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
		$merchantAuthentication->setName( $this->api_login_id );
		$merchantAuthentication->setTransactionKey( $this->api_transaction_key );
		$refId = 'ref' . time();

		// Create the payment data for a credit card
		$creditCard = new AnetAPI\CreditCardType();
		$creditCard->setCardNumber( $this->card_number );
		$creditCard->setExpirationDate( $this->expiration_year . '-' . $this->expiration_month );

		$paymentOne = new AnetAPI\PaymentType();
		$paymentOne->setCreditCard( $creditCard );

		// Order info
		$order = new AnetAPI\OrderType();
		$order->setInvoiceNumber( $this->invoice_number );
		$order->setDescription( get_bloginfo( 'name' ) );

		$cart_contents = $this->purchase_log->get_cart_contents();

		$lineitems = array();

		// Line Item Info
		foreach ( $cart_contents as $index => $item ) {
			// this is for the product options support that can be used in place of variations
			if ( defined( 'OPTION_BASE' ) ) {
				$options = wpsc_get_cart_item_meta( $item->id, OPTION_BASE, true );
				if ( ! empty( $options ) ) {
					$options_message = strip_tags( $options->message() );
				}
			}

			$custom_description = $item->name . ' ' . $options_message . ' ' . $item->custom_message;

			$lineitems[ $index ] = new AnetAPI\LineItemType();
			$lineitems[ $index ]->setItemId( $item->prodid );
			$lineitems[ $index ]->setName( substr( $item->name, 0, 31 ) );
			$lineitems[ $index ]->setDescription( substr( $custom_description, 0, 255 ) );
			$lineitems[ $index ]->setQuantity( $item->quantity );
			$lineitems[ $index ]->setUnitPrice( $this->force_two_decimals( $item->price ) );
			$lineitems[ $index ]->setTaxable( 0.0 != floatval( $item->tax_charged ) );
		}

		// Tax info
		$tax = new AnetAPI\ExtendedAmountType();
		$tax->setName( "Sales Tax" );
		$tax->setAmount( $this->force_two_decimals( $s = $this->purchase_log->get( 'wpec_taxes_total' ) ) );
		$tax->setDescription( "Sales Tax" );

		// Customer info
		$customer = new AnetAPI\CustomerDataType();
		$wp_user  = get_user_by( 'email', $this->checkout_data->get( 'billingemail' ) );
		if ( $wp_user ) {
			$customer->setId( $wp_user->ID );
		}

		$customer->setEmail( $this->checkout_data->get( 'billingemail' ) );

		// PO Number
		$ponumber = $this->checkout_data->get( 'billingponumber' );

		//Ship To Info
		$shipto = new AnetAPI\NameAndAddressType();
		$shipto->setFirstName( $s = $this->checkout_data->get( 'shippingfirstname' ) );
		$shipto->setLastName( $s = $this->checkout_data->get( 'shippinglastname' ) );
//		$shipto->setCompany( $s = $this->checkout_data->get( 'shippingcompany') );
		$shipto->setAddress( $s = $this->checkout_data->get( 'shippingaddress' ) );
		$shipto->setCity( $s = $this->checkout_data->get( 'shippingcity' ) );
		$shipto->setState( $s = $this->checkout_data->get( 'shippingstate' ) );
		$shipto->setZip( $s = $this->checkout_data->get( 'shippingpostcode' ) );
		$shipto->setCountry( $s = $this->checkout_data->get( 'shippingcountry' ) );

		// Bill To
		$billto = new AnetAPI\CustomerAddressType();
		$billto->setFirstName( $s = $this->checkout_data->get( 'billingfirstname' ) );
		$billto->setLastName( $s = $this->checkout_data->get( 'billinglastname' ) );
//		$billto->setCompany(  $s = $this->checkout_data->get( 'billingcompany') );
		$billto->setAddress( $s = $this->checkout_data->get( 'billingaddress' ) );
		$billto->setCity( $s = $this->checkout_data->get( 'billingcity' ) );
		$billto->setState( $s = $this->checkout_data->get( 'billingstate' ) );
		$billto->setZip( $s = $this->checkout_data->get( 'billingpostcode' ) );
		$billto->setCountry( $s = $this->checkout_data->get( 'billingcountry' ) );
		$billto->setPhoneNumber( $this->checkout_data->get( 'billingphone' ) );

		//create a transaction
		$transactionRequestType = new AnetAPI\TransactionRequestType();

		foreach ( $lineitems as $lineitem ) {
			$transactionRequestType->addToLineItems( $lineitem );
		}

		$transactionRequestType->setTransactionType( "authCaptureTransaction" );
		$transactionRequestType->setAmount( $this->force_two_decimals( $s = $this->purchase_log->get( 'totalprice' ) ) );
		$transactionRequestType->setTax( $tax );
		$transactionRequestType->setPayment( $paymentOne );
		$transactionRequestType->setOrder( $order );

		if ( ! empty( $ponumber ) ) {
			$transactionRequestType->setPoNumber( $ponumber );
		}

		$transactionRequestType->setCustomer( $customer );
		$transactionRequestType->setBillTo( $billto );
		$transactionRequestType->setShipTo( $shipto );

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$transactionRequestType->setCustomerIP( $_SERVER['REMOTE_ADDR'] );
		}

		$request = new AnetAPI\CreateTransactionRequest();
		$request->setMerchantAuthentication( $merchantAuthentication );
		$request->setRefId( $refId );
		$request->setTransactionRequest( $transactionRequestType );

		$controller = new AnetController\CreateTransactionController( $request );
		if ( $this->sandbox_mode ) {
			$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX );
		} else {
			$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION );
		}

		$result     = false;

		if ( $response != null ) {
			$tresponse = $response->getTransactionResponse();

			if ( $tresponse != null ) {

				// see http://developer.authorize.net/api/reference/ for definitions
				if ( ( $tresponse->getResponseCode() == "1" ) ) {
					// 1 = Approved
					$this->set_purchaselog_status( WPSC_Purchase_Log::ACCEPTED_PAYMENT );
					$result = true;
				} elseif ( ( $tresponse->getResponseCode() == "2" ) ) {
					// 2 = Declined
					$this->set_purchaselog_status( WPSC_Purchase_Log::ACCEPTED_PAYMENT );
				} elseif ( ( $tresponse->getResponseCode() == "3" ) ) {
					// 3 = Error
					$this->set_purchaselog_status( WPSC_Purchase_Log::INCOMPLETE_SALE );
				} elseif ( ( $tresponse->getResponseCode() == "4" ) ) {
					// 4 = Held for Review
					$this->set_purchaselog_status( WPSC_Purchase_Log::ORDER_RECEIVED );
					$result = true;
				} else {
					// Unknown transaction code
					$this->set_purchaselog_status( WPSC_Purchase_Log::INCOMPLETE_SALE );
				}

				wpsc_update_purchase_meta( $this->invoice_number, 'pbci_auth_net_raw_response', $tresponse );

				$this->purchase_log->set( 'transactid', $tresponse->getTransId() );
				$this->purchase_log->set( 'authcode', $tresponse->getAuthCode() );

			} else {
				error_log( __FUNCTION__ . ' ' . "Charge Credit Card ERROR :  Invalid response" );
			}

		} else {
			error_log( __FUNCTION__ . ' ' . "Charge Credit card Null response returned" );
		}

		return $result;
	}

	private function wpsc_instantiate_purchaselogitem( $purchase_log_id = false ) {
		global $purchlogitem;

		if ( empty( $purchase_log_id ) ) {
			if ( isset( $_REQUEST['purchaselog_id'] ) ) {
				$purchase_log_id = intval( $_REQUEST['purchaselog_id'] );
			}
		}

		if ( ! empty( $purchase_log_id ) ) {
			$purchlogitem = new wpsc_purchaselogs_items( (int) $purchase_log_id );
		}

	}

	private function force_two_decimals( $amount ) {
		$amount = round( (float)$amount, 2 );
		return $amount;
	}

	private function set_purchaselog_status( $status ) {
		//$this->purchase_log->set( 'processed', WPSC_Purchase_Log::INCOMPLETE_SALE );
		error_log( __FUNCTION__ . ' fake setting purchase log status to ' . $status );
		return $this;
	}
}