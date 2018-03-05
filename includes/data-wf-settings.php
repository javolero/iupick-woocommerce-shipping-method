<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$states  = include( 'data-wf-states.php' );



/**
 * Array of settings
 */
return array(


	'enabled'     => array(
		'title'           => __( 'Enabled IUPICK', 'wf-shipping-iupick' ),
		'type'            => 'select',
		'default'         => 'disabled',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			'enabled'        => __( 'Enabled iupick', 'wf-shipping-iupick' ),
			'override'     => __( 'Override flat rate shipping with iupick', 'wf-shipping-iupick' ),
			'disabled'        => __( 'Disabled', 'wf-shipping-iupick' ),
		),
	),


	'cost_rate'         => array(
		'title'           => __( 'Shipping cost', 'wf-shipping-iupick' ),
		'type'            => 'number',
		'label'           => __( 'Cost', 'wf-shipping-iupick' ),
		'default'         => '0'
	),

	

	'title'            => array(
		'title'           => __( 'Method Title', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'description'     => __( 'This controls the title which the user sees during checkout.', 'wf-shipping-iupick' ),
		'default'         => __( 'IUPICK', 'wf-shipping-iupick' ),
		'desc_tip'        => true
	),
	'secret_token'           => array(
		'title'           => __( 'IUPICK Secret token', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'description'     => __( 'Secret token .', 'wf-shipping-iupick' ),
		'default'         => ''
    ),
    'public_token'           => array(
		'title'           => __( 'IUPICK Public token', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'description'     => __( 'Public token.', 'wf-shipping-iupick' ),
		'default'         => ''
    ),

    'secret_token_sandbox'=> array(
		'title'           => __( 'Sandbox IUPICK Secret token', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'description'     => __( 'Sandbox Secret token .', 'wf-shipping-iupick' ),
		'default'         => ''
    ),
    'public_token_sandbox'=> array(
		'title'           => __( 'Sandbox IUPICK Public token', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'description'     => __( 'Sandbox Public token.', 'wf-shipping-iupick' ),
		'default'         => ''
    ),
    
	'sandbox'      => array(
		'title'           => __( 'Sandbox', 'wf-shipping-iupick' ),
		'label'           => __( 'Enable sandbox mode', 'wf-shipping-iupick' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable sandbox mode to use sandbox environmnet', 'wf-shipping-iupick' )
	),
    'debug'      => array(
		'title'           => __( 'Debug Mode', 'wf-shipping-iupick' ),
		'label'           => __( 'Enable debug mode', 'wf-shipping-iupick' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'wf-shipping-iupick' )
	),


	'maps_api'=> array(
		'title'           => __( 'Google Maps Api Key', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'description'     => __( 'Google Maps Api Key.', 'wf-shipping-iupick' ),
		'default'         => 'AIzaSyCaI8tlA-dmA_hf3Y6F6KW5LoYSw8smmuY'
    ),



	'reference'            => array(
		'title'           => __( 'Shop Reference (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
		'default'         => __( 'IUPICK shop reference', 'wf-shipping-iupick' ),
		'desc_tip'        => true
	),



	'shipper_name'            => array(
		'title'           => __( 'Shipper name (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),

	'shipper_phone'            => array(
		'title'           => __( 'Shipper phone (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),

	'shipper_email'            => array(
		'title'           => __( 'Shipper email (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),

	'shipper_title'            => array(
		'title'           => __( 'Shipper title', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),

	'shipper_company'            => array(
		'title'           => __( 'Shipper company (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),

	'shipper_phone_extension'     => array(
		'title'           => __( 'Shipper phone extension ', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),


	'shipper_city'     => array(
		'title'           => __( 'Shipper Address city (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),


	


	'shipper_state_code'     => array(
		'title'           => __( 'Shipper Address State (required)', 'wf-shipping-iupick' ),
		'type'            => 'select',
		'options'         => $states
	),



	

	'shipper_line_one'     => array(
		'title'           => __( 'Shipper Address line one (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),
	'shipper_line_two'     => array(
		'title'           => __( 'Shipper Address line two (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),
	'shipper_neighborhood'     => array(
		'title'           => __( 'Shipper Address neighborhood (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),
	'shipper_postal_code'     => array(
		'title'           => __( 'Shipper Address postal_code (required)', 'wf-shipping-iupick' ),
		'type'            => 'text',
	),


);