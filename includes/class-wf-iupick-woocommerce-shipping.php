<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_iupick_woocommerce_shipping_method extends WC_Shipping_Method {
	
	private $found_rates;
	private $services;
	
	public function __construct() {
		$this->id                               = WF_IUPICK_ID;
		$this->method_title                     = __( 'IUPICK', 'wf-shipping-iupick' );
		$this->method_description               = __( 'Obtains  real time shipping waypints.', 'wf-shipping-iupick' );
		$this->init();
	}
	
	private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		
		// Define user set variables

		$en = $this->get_option( 'enabled' );

		$this->enabled         = ($en === 'enabled')  ? 'yes' : 'no';

		$this->cost_rate       = $this->get_option( 'cost_rate' );

		$this->title           = $this->get_option( 'title', $this->method_title );
		
		$this->secret_token    = $this->get_option( 'secret_token' );
		$this->public_token    = $this->get_option( 'public_token' );

		$this->secret_token_sandbox	= $this->get_option( 'secret_token_sandbox' );
		$this->public_token_sandbox	= $this->get_option( 'public_token_sandbox' );


		$this->sandbox         = ( $bool = $this->get_option( 'sandbox' ) ) && $bool == 'yes' ? true : false;
		$this->debug           = ( $bool = $this->get_option( 'debug' ) ) && $bool == 'yes' ? true : false;


		

		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug ) {
			wc_add_notice( $message, $type );
		}
	}
	
	

	private function environment_check() {
			
		/*if ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'FedEx is enabled, but the origin postcode has not been set.', 'wf-shipping-iupick' ) . '</p>
			</div>';
		}*/
	}

	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();
        
		// Show settings
		parent::admin_options();
	}

	public function init_form_fields() {
		$this->form_fields  = include( 'data-wf-settings.php' );
	}

	public function calculate_shipping( $package = array() ) {

		// Debugging
		$this->debug( __( 'IUPICK debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-iupick' ) );


		$this->add_found_rates();
	}

	public function add_found_rates() {

		$this->add_rate( array(
            'id'        => "IUPICK", // ID for the rate. If not passed, this id:instance default will be used.
            'label'     => $this->title, // Label for the rate
            'cost'      => $this->cost_rate, // Amount or array of costs (per item shipping)
            'taxes'     => '', // Pass taxes, or leave empty to have it calculated for you, or 'false' to disable calculations
            'calc_tax'  => 'per_order', // Calc tax per_order or per_item. Per item needs an array of costs
            'meta_data' => array(), // Array of misc meta data to store along with this rate - key value pairs.
            'package'   => false, // Package array this rate was generated for @since 2.6.0
        ) );
	}

	private function wf_load_product( $product ){
		if( !$product ){
			return false;
		}
		return ( WC()->version < '2.7.0' ) ? $product : new wf_product( $product );
	}
}
