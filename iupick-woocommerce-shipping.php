<?php
/*
	Plugin Name: iupick WooCommerce Extension
	Plugin URI: https://github.com/javolero/iupick-woocommerce-shipping-method
	Description: Obtain real time shipping waypoints.
	Version: 1.0
	Author: javolero
	Author URI: https://github.com/javolero/
	Text Domain: wf-shipping-iupick
*/
if (!defined('WF_IUPICK_ID')){
	define("WF_IUPICK_ID", "wf_iupick_woocommerce_shipping");
}

if (!defined('WF_IUPICK_VERSION')){
	define("WF_IUPICK_VERSION", "1.1.0");
}

if (!defined('WF_IUPICK_ADV_DEBUG_MODE')){
	define("WF_IUPICK_ADV_DEBUG_MODE", "off"); // Turn 'on' to allow advanced debug mode.
}

/**
 * Plugin activation check
 */
function wf_iupick_plugin_pre_activation_check(){
	//TODO: check this - set_transient('wf_iupick_welcome_screen_activation_redirect', true, 30);
}
register_activation_hook( __FILE__, 'wf_iupick_plugin_pre_activation_check' );

/**
 * Check if WooCommerce is active
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {	


	wp_register_script('iupick-js', "https://s3-us-west-1.amazonaws.com/iupick-map/iupick-map.js", array(), WF_IUPICK_VERSION , true);

	//wp_register_script('iupick-js', plugins_url('iupick.js', __FILE__), array('jquery'), WF_IUPICK_VERSION , true);
	
	wp_enqueue_style('iupick-leaflet', 'https://unpkg.com/leaflet@1.2.0/dist/leaflet.css', array(), WF_IUPICK_VERSION );
	wp_enqueue_style('iupick-markercluster', 'https://unpkg.com/leaflet.markercluster@1.2.0/dist/MarkerCluster.Default.css', array('iupick-leaflet'), WF_IUPICK_VERSION );


	add_action('admin_enqueue_scripts', 'iupick_admin_js');

	if (!function_exists('iupick_admin_js')){
		function iupick_admin_js() {

			wp_enqueue_script('iupick-admin-js', plugins_url('iupick-admin.js', __FILE__), array('jquery'), WF_IUPICK_VERSION , true);
		}
	}
	


	add_action('woocommerce_after_checkout_validation', 'rei_after_checkout_validation');

	if (!function_exists('rei_after_checkout_validation')){
		function rei_after_checkout_validation( $posted ) {

		    
		    $shipping_methods = WC()->shipping->get_shipping_methods();

        	
        	$enabled = $shipping_methods[ WF_IUPICK_ID ]->settings['enabled'];
        	
        	$selected = WC()->session->get( 'chosen_shipping_methods' );


        	if( $enabled === 'override' || ( !empty( $selected ) && $selected[0] === 'IUPICK' )  ){

        		if (empty($_POST['wf_iupick_id'])) {
			         wc_add_notice( __( "Waypoint is needed", 'wf-shipping-iupick' ), 'error' );
			    }	
        	}

		}
	}


	add_action( 'woocommerce_checkout_update_order_meta', 'iupick_checkout_field_update_order_meta' );

	if( !function_exists( 'iupick_checkout_field_update_order_meta' ) ){
		function iupick_checkout_field_update_order_meta( $order_id ) {
		    if ( ! empty( $_POST['wf_iupick_id'] ) ) {
		        update_post_meta( $order_id, 'wf_iupick_id', sanitize_text_field( $_POST['wf_iupick_id'] ) );

		        //update_post_meta( $order_id, 'wf_iupick_name', sanitize_text_field( $_POST['wf_iupick_name'] ) );
		    }
		}	
	}

	add_action( 'woocommerce_order_details_after_order_table', 'iupick_thankyoupage' );


	function iupick_thankyoupage($order){

		$wf_iupick_id = get_post_meta( $order->id, 'wf_iupick_id', true );
		$wf_iupick_name = $order->get_shipping_company();

		if( !empty( $wf_iupick_id ) ){

			echo "<h2>" . __('You can pick up your order with your ID and tracking number.', 'wf-shipping-iupick') . "</h2>";	

			echo "<label>" . __('Waypoint:', 'wf-shipping-iupick') . " </label>";

			echo "<span>" . $wf_iupick_name . "</span><br/><br/>";
		}
	
		
	}


	add_action( 'add_meta_boxes', 'mv_add_meta_boxes' );
	if ( ! function_exists( 'mv_add_meta_boxes' ) )
	{
	    function mv_add_meta_boxes()
	    {
	        add_meta_box( 'mv_other_fields', __('IUPICK','wf-shipping-iupick'), 'mv_add_other_fields_for_packaging', 'shop_order', 'side', 'core' );
	    }
	}


	function wf_iupick_woocommerce_email_order_details( $order, $sent_to_admin, $plain_text ) {


	    
	    if( $order->has_status('completed') ){

	    	$wf_iupick_packages = get_post_meta( $order->ID, 'wf_iupick_packages', false );

	    	if( !empty( $wf_iupick_packages ) ){
	    		if( $plain_text ){
	    			echo __('You can pick up your order with your ID and tracking number.','wf-shipping-iupick');
	    		}else{
	    			echo "<h3>".__('You can pick up your order with your ID and tracking number.','wf-shipping-iupick')."</h3><p>";
	    		}

	    		foreach( $wf_iupick_packages as $package ){

		        	$package = explode('|', $package);

		        	echo __('Company: ','wf-shipping-iupick'). $package[0] .', '  .  __('Tracking number: ','wf-shipping-iupick') .$package[1];

		        	if( $plain_text ){
			        	echo "\n";
			        }else{
			        	echo "<br/>";
			        }
		        }


		        if( !$plain_text ){
		        	echo "</p>";
		        }

	    	}


	    }
	}
	add_action('woocommerce_email_order_details', 'wf_iupick_woocommerce_email_order_details', 30, 3 );

	// Adding Meta field in the meta container admin shop_order pages
	if ( ! function_exists( 'mv_add_other_fields_for_packaging' ) )
	{
	    function mv_add_other_fields_for_packaging()
	    {
	        global $post;

	        $wf_iupick_id = get_post_meta( $post->ID, 'wf_iupick_id', true ) ? get_post_meta( $post->ID, 'wf_iupick_id', true ) : '';

	        
	        echo '<input type="hidden" id="iupick_order_id" value="' . $post->ID . '">';
	        echo '<input type="hidden" id="iupick_field_nonce" value="' . wp_create_nonce() . '">';

	        if( !empty( $wf_iupick_id ) ){

	        	$wf_iupick_packages = get_post_meta( $post->ID, 'wf_iupick_packages', false );

	        	echo "<h2>Packages</h2>";
		        echo'<ul id="iupick-packages">';

	        	if( !empty( $wf_iupick_packages ) ){
		        	foreach( $wf_iupick_packages as $package ){

			        	$package = explode('|', $package);

			        	echo '<li class="iupick-package"><span class="iupick-company">'. $package[0] .'</span> - <span class="iupick-tracking">'.$package[1].'</span> </li>';

			        }
		        }else{
		        	echo '<li>'.__('No packages found.','wf-shipping-iupick').'</li>';
		        }

		        echo "</ul>";



		        echo '<p><label>'.__('Length', 'wf-shipping-iupick').'</label><br/>';
	        	echo '<input type="text" class="input-text" id="iupick_length" placeholder="' . __('Length', 'wf-shipping-iupick') . '">';	
	        	echo '</p>';

	        	echo '<p><label>'.__('Width', 'wf-shipping-iupick').'</label><br/>';
	        	echo '<input type="text" class="input-text" id="iupick_width" placeholder="' . __('Width', 'wf-shipping-iupick') . '">';	
	        	echo '</p>';

	        	echo '<p><label>'.__('Height', 'wf-shipping-iupick').'</label><br/>';
	        	echo '<input type="text" class="input-text" id="iupick_height" placeholder="' . __('Height', 'wf-shipping-iupick') . '">';	
	        	echo '</p>';

	        	echo '<p><label>'.__('Weight', 'wf-shipping-iupick').'</label><br/>';
	        	echo '<input type="text" class="input-text" id="iupick_weight" placeholder="' . __('Weight', 'wf-shipping-iupick') . '">';	
	        	echo '</p>';


	        	$wf_iupick_shipping_vendors = include( 'includes/data-wf-shipping-vendors.php' );

	        	echo '<p><label>'.__('Shipping vendor', 'wf-shipping-iupick').'</label><br/>';

	        	echo '<select id="iupick_shipping_vendor">';

	        		echo '<option value="">'. __('None', 'wf-shipping-iupick') . '</option>';

	        		foreach( $wf_iupick_shipping_vendors as $key => $vendor ){

	        			echo '<option value="'. $key .'">'. $vendor . '</option>';
	        		}

	        	echo '</select></p>';

	        	echo '<p><label>'.__('Tracking number', 'wf-shipping-iupick').'</label><br/>';
	        	echo '<input type="text" class="input-text" id="iupick_tracking_number" placeholder="' . __('Tracking number', 'wf-shipping-iupick') . '">';	
	        	echo '</p>';

	        	echo '<button id="create_iupick_shipping" class="button">'.__('Create IUPICK shipping', 'wf-shipping-iupick').'</button>';
	        }


	        $est_delivery['fedex_delivery_time'] = date( 'd-m-Y', strtotime("+".$days." days", strtotime($est_delivery['fedex_delivery_time'])));

	        $day = date('D', strtotime($est_delivery['fedex_delivery_time']) );

	        $est_delivery['fedex_delivery_time'] = date( 'd-m-Y', strtotime("+".$days." days", strtotime($est_delivery['fedex_delivery_time'])));

	    }
	}

	// Save the data of the Meta field
	add_action( 'wp_ajax_iupick_add_tracking', 'wc_order_iupick_fields');

	if ( ! function_exists( 'wc_order_iupick_fields' ) )
	{

	    function wc_order_iupick_fields(  ) {

	        // We need to verify this with the proper authorization (security stuff).

	        // Check if our nonce is set.
	        if ( ! isset( $_POST[ 'nonce' ] ) ) {

	        	echo json_encode(array('status' => 'error'));
	            wp_die();

	        }
	        $nonce = $_REQUEST[ 'nonce' ];

	        //Verify that the nonce is valid.
	        if ( ! wp_verify_nonce( $nonce ) ) {
	            echo json_encode(array('status' => 'error'));
	            wp_die();
	        }

	        $post_id = $_POST[ 'order_id' ];

	        $order = new WC_Order($post_id);

	        $wf_iupick_id = get_post_meta( $post_id, 'wf_iupick_id', true );

	        

	        $package = implode('|', array( $_POST[ 'shipping_vendor' ], $_POST[ 'tracking_number' ] ));

	        include 'iupick-php/lib/Iupick.php';
	        include 'iupick-php/lib/Shipment.php';
	        include 'iupick-php/lib/Waypoints.php';


	        $states = include( 'includes/data-wf-states.php' );

	        

	        
			$shipping_methods = WC()->shipping->get_shipping_methods();        	

        	$sandbox = $shipping_methods[ WF_IUPICK_ID ]->settings['sandbox'];

			if( $sandbox == 'yes' ){
				Iupick\Iupick::setSecretToken( $shipping_methods[ WF_IUPICK_ID ]->settings['secret_token_sandbox'] );
				Iupick\Iupick::setPublicToken( $shipping_methods[ WF_IUPICK_ID ]->settings['public_token_sandbox'] );
				Iupick\Iupick::setEnviroment('sandbox');
			}else{
				Iupick\Iupick::setSecretToken( $shipping_methods[ WF_IUPICK_ID ]->settings['secret_token'] );
				Iupick\Iupick::setPublicToken( $shipping_methods[ WF_IUPICK_ID ]->settings['public_token'] );
				Iupick\Iupick::setEnviroment('production');
			}

			$length = $_POST['length'];
			$width = $_POST['width'];
			$height = $_POST['height'];
			$weight = $_POST['weight'];

			try{
				$shipmentToken = Iupick\Shipment::create( intval($length) , intval($width),intval($height),intval($weight));

				$shipmentToken = $shipmentToken['shipment_token'];

				$shipperAddress = Iupick\Iupick::createAddress(
					$shipping_methods[ WF_IUPICK_ID ]->settings['shipper_city'],
					$shipping_methods[ WF_IUPICK_ID ]->settings['shipper_line_one'],
					$shipping_methods[ WF_IUPICK_ID ]->settings['shipper_line_two'],
					$shipping_methods[ WF_IUPICK_ID ]->settings['shipper_postal_code'],
					$shipping_methods[ WF_IUPICK_ID ]->settings['shipper_neighborhood'],
					$states[ $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_state_code'] ],
					$shipping_methods[ WF_IUPICK_ID ]->settings['shipper_state_code']
				);
				$shipperContact = Iupick\Iupick::createPerson(
				    $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_name'],
				    $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_phone'],
				    $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_email'],
				    $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_title'],
				    $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_company'],
				    $shipping_methods[ WF_IUPICK_ID ]->settings['shipper_phone_extension']
				);
				$recipientContact = Iupick\Iupick::createPerson(
				    $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
				    $order->get_billing_phone(),
				    $order->get_billing_email(),
				    '',
				    '',
				    ''
				);
				$data = [
				    'shipmentToken' => $shipmentToken,
				    'waypointId' => intval($wf_iupick_id),
				    'shipperAddress' => $shipperAddress,
				    'shipperContact' => $shipperContact,
				    'recipientContact' => $recipientContact,
				    'thirdPartyReference' => $shipping_methods[ WF_IUPICK_ID ]->settings['reference']
				];


				$addInformation = Iupick\Shipment::addInformation($data);

				$waybill = Iupick\Shipment::generateWaybill($shipmentToken);

				if( isset( $waybill['error'] ) && !empty( $waybill['error'] ) ){

					echo json_encode(array('status' => 'error', 'error' =>  $waybill['error'], 'html' => $html ));
	            	wp_die();

				}

				add_post_meta( $post_id, 'wf_iupick_waybill', json_encode( $waybill ) );

			}catch(Exception $ex){

				echo json_encode(array('status' => 'error', 'error' =>  $e->getMessage() ));
	            wp_die();
			}
			


	        add_post_meta( $post_id, 'wf_iupick_packages', $package );


	        $wf_iupick_packages = get_post_meta( $post_id, 'wf_iupick_packages', false );

	        $html = '';
	        
        	if( !empty( $wf_iupick_packages ) ){
	        	foreach( $wf_iupick_packages as $package ){
		        	$package = explode('|', $package);
		        	$html.= '<li class="iupick-package"><span class="iupick-company">'. $package[0] .'</span> - <span class="iupick-tracking">'.$package[1].'</span> </li>';
		        }
	        }

	        echo json_encode(array('status' => 'ok', 'html' => $html));
	        wp_die();

	    }
	}


	

	

	
	if (!function_exists('wf_get_settings_url')){
		function wf_get_settings_url(){
			return version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
		}
	}
	
	if (!function_exists('wf_plugin_override')){
		add_action( 'plugins_loaded', 'wf_plugin_override' );
		function wf_plugin_override() {
			if (!function_exists('WC')){
				function WC(){
					return $GLOBALS['woocommerce'];
				}
			}
		}
	}
	if (!function_exists('wf_get_shipping_countries')){
		function wf_get_shipping_countries(){
			$woocommerce = WC();
			$shipping_countries = method_exists($woocommerce->countries, 'get_shipping_countries')
					? $woocommerce->countries->get_shipping_countries()
					: $woocommerce->countries->countries;
			return $shipping_countries;
		}
	}
	if(!class_exists('wf_iupick_wooCommerce_shipping_setup')){
		class wf_iupick_wooCommerce_shipping_setup {
			
			public function __construct() {

                /*add_action('admin_init', array($this,'wf_iupick_welcome'));
                add_action('admin_menu', array($this,'wf_iupick_welcome_screen'));
                add_action('admin_head', array($this,'wf_iupick_welcome_screen_remove_menus'));*/
				
				$this->wf_init();
				add_action( 'init', array( $this, 'init' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				add_action( 'woocommerce_shipping_init', array( $this, 'wf_iupick_wooCommerce_shipping_init' ) );
				add_filter( 'woocommerce_shipping_methods', array( $this, 'wf_iupick_wooCommerce_shipping_methods' ) );		
				add_filter( 'admin_enqueue_scripts', array( $this, 'wf_iupick_scripts' ) );		

				add_action('woocommerce_before_order_notes', array($this, 'wogmlf_my_custom_checkout_field') );
							
			}
			/*public function wf_iupick_welcome()
            {
	          	
            }
            public function wf_iupick_welcome_screen()
            {
            	add_dashboard_page('Welcome To Fedex', 'Welcome To Fedex', 'read', 'Fedex-Welcome', array($this,'wf_iupick_screen_content'));
            }
            public function wf_iupick_screen_content()
            {
            	include 'includes/wf_iupick_welcome.php';
            }
            public function wf_iupick_welcome_screen_remove_menus()
            {
            	 remove_submenu_page('index.php', 'Fedex-Welcome');
            }*/

            public function wogmlf_my_custom_checkout_field( $checkout ) {

            	$shipping_methods = WC()->shipping->get_shipping_methods();


            	$enabled = $shipping_methods[ WF_IUPICK_ID ]->settings['enabled'];

        		$secret_token = $shipping_methods[ WF_IUPICK_ID ]->settings['secret_token'];
        		$public_token = $shipping_methods[ WF_IUPICK_ID ]->settings['public_token'];

        		if( $shipping_methods[ WF_IUPICK_ID ]->settings['sandbox'] === 'yes' ){
        			$secret_token = $shipping_methods[ WF_IUPICK_ID ]->settings['secret_token_sandbox'];
        			$public_token = $shipping_methods[ WF_IUPICK_ID ]->settings['public_token_sandbox'];
        		}

            	if ( is_checkout() ) {

            		//wp_enqueue_script('iupick-markercluster');
					wp_enqueue_script('iupick-js');
				}

				?>

				<div id="woocommerce-iupick">

					<?php
						woocommerce_form_field( 'wf_iupick_id', array( 
							'type' 			=> 'text', 
							'label'         => __('Waypoint ID', 'wf-shipping-iupick'),
							'required'		=> true,
							'class' 		=> array('my-field-class orm-row-wide'), 
							), $checkout->get_value( 'wf_iupick_id' ));
					?>

					<a href="#" class="btn-select-waypoint button"><?= __('Select one waypoint on the map') ?></a>

					

					

					<script type="text/javascript">
						var iupick_token = 'Token <?= $public_token ?>'
						var iupick_main = 'iupick_main';
						var iupick_map = 'iupick_map';
						var iupick_init = false;
						var iupick_object = null;
						var iupick_disable_autoload = true;

						var iupick_moved = false;

						var use_iupick = false;
						var enabled_all = <?= ($enabled === 'override')?'true':'false' ?>;

						function selectedWaypoint(waypoint) {
							//console.log('Selected Waypoint:', waypoint);

							iupick_object = waypoint;

							jQuery('.wf_iupick_preview').text( iupick_object.entity + ' ' + iupick_object.name );
							
						}

						jQuery( document ).on( 'updated_checkout', function() {


						    if( enabled_all || jQuery('.shipping_method:checked').val() === 'IUPICK' || jQuery('input#shipping_method_0').val() === 'IUPICK' ){
						    	
						    	jQuery( '#woocommerce-iupick' ).show();		

						    	if( !iupick_moved ){
						    		jQuery( '#woocommerce-iupick' ).insertBefore('.woocommerce-shipping-fields');
						    		jQuery('.iupick-modal').prependTo('body');
						    		iupick_moved = true;
						    	}
						    }else{
						    	jQuery( '#woocommerce-iupick' ).hide();
						    }

						} );


						jQuery('body').on('change', '.shipping_method', function(){

							if( jQuery('.shipping_method:checked').val() === 'IUPICK' ){
								jQuery('.iupick-modal').show();
								iupick_object = null;
						    	if( !iupick_init ){
						    		initIupickMap();
						    		iupick_init = true;	
						    	}
							}

						});

						jQuery('body').on('click', '.btn-select-waypoint', function(e){
							e.preventDefault();

							jQuery('.iupick-modal').show();
							iupick_object = null;
					    	if( !iupick_init ){
					    		initIupickMap();
					    		iupick_init = true;	
					    	}
						});

						jQuery('body').on('click', '.btn-cancel-waypoint', function(e){
							e.preventDefault();

							jQuery('.iupick-modal').hide();
						});

						jQuery('body').on('click', '.btn-ok-waypoint', function(e){
							e.preventDefault();

							
							if( iupick_object == null ){
								alert('<?= __('You need select a waypoint on the map', 'wf-shipping-iupick') ?>');
								return;
							}

							if( confirm( '<?= __('Select this waypoint override your previous shipping address, continue?', 'wf-shipping-iupick') ?>' ) ){

								console.log(iupick_object);

								jQuery('#wf_iupick_id').val( iupick_object.id );

								jQuery('#shipping_company').val( "Ocurre: " + iupick_object.entity + ' ' + iupick_object.name );	
								jQuery('#shipping_address_1').val( iupick_object.address.line_one );
								jQuery('#shipping_address_2').val( iupick_object.address.line_two );
								jQuery('#shipping_postcode').val( iupick_object.address.postal_code.code );
								jQuery('#shipping_city').val( iupick_object.address.city );
								jQuery('#shipping_state').val( iupick_object.address.postal_code.state.code );
								jQuery('#shipping_state').change();
							
								jQuery('#ship-to-different-address-checkbox').prop('checked', true).change();

							}

							jQuery('.iupick-modal').hide();
							
						});


						jQuery(function(){

							jQuery('#wf_iupick_id_field').attr('readonly', true );
						})

					</script>

					

					<style>

						/*#woocommerce-iupick{
							display: none;
						}*/

						.iupick-modal {
							display: none;
						    position: fixed;
						    top: 0;
						    width: 100vw;
						    height: 100vh;
						    padding: 5vh 5vw 5vh 5vw;
						    left: 0;
						    background: rgba(1,1,1,.5);
						    z-index: 99999;
						    
						}
						.iupick-inner{
							width: 90vw;
							height: 90vh;
							background: white;
							overflow: scroll;
						}

						.iupick-inner .iupick-actions{
							padding: 10px;
							text-align: center;
						}

						#wf_iupick_id_field{
							display: none;
						}




						
						
						#iupick_map{
							width: 100%;
							height: 400px;
						}

						.waypoint-text{
							width: 100%;
							display: block;
						}
					</style>
					
					
				</div>

				<div class="iupick-modal">
					<div class="iupick-inner">
						
						

						<div class="iupick-actions">

							<p class="form-row">
								<label><?= __('Waypoint:', 'wf-shipping-iupick') ?></label>
								<span class="waypoint-text wf_iupick_preview"></span>
							</p>

							<a href="#" class="button btn-cancel-waypoint"><?= __('Cancel', 'wf-shipping-iupick') ?></a>
							<a href="#" class="button btn-ok-waypoint"><?= __('Ok', 'wf-shipping-iupick') ?></a>
						</div>
						<div id="iupick_map" ></div>
						<div id="iupick_main"></div>
					</div>
				</div>

				<?php

			}

			public function init(){
				if ( ! class_exists( 'wf_order' ) ) {
					include_once 'includes/class-wf-legacy.php';
				}	
			}
			public function wf_init() {
				// Localisation
				load_plugin_textdomain( 'wf-shipping-iupick' , false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
			}
			
			public function wf_iupick_scripts() {
				wp_enqueue_script( 'jquery-ui-sortable' );
			}
			
			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_iupick_woocommerce_shipping_method' ) . '">' . __( 'Settings', 'wf-shipping-iupick' ) . '</a>',
                    
				);
				return array_merge( $plugin_links, $links );
			}			
			
			public function wf_iupick_wooCommerce_shipping_init() {
				include_once( 'includes/class-wf-iupick-woocommerce-shipping.php' );
			}

			
			public function wf_iupick_wooCommerce_shipping_methods( $methods ) {
				$methods[] = 'wf_iupick_woocommerce_shipping_method';
				return $methods;
			}		
		}
		new wf_iupick_wooCommerce_shipping_setup();
	}
}
