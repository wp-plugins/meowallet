<?php

require_once(dirname(__FILE__) . '/../libs/Requirements.php');

/*
 * Class WC_MEOWallet_Plugin
 */
 
 class WC_MEOWallet extends WC_Payment_Gateway {
 	
	/*
	 * Constructor
	 */
	 
	 function __construct(){
	 	$this->id 			= 'meowallet_payment';
		$this->icon			= apply_filters('woocommerce_mw_icon', mw_plugin_dir . 'assets/mw.png');
		$this->method_title = __('MEO Wallet', 'meowallet');
		$this->has_fields   = true;
		$this->notify_url   = str_replace('https:', 'http:', add_query_arg( 'wc-api', 'WC_MEOWallet_Plugin', home_url( '/' ) ) );
		
		$this->init_form_fields();
        $this->init_settings();
		
		$this->enabled			  = $this->get_option('enabled');
		$this->title              = $this->get_option('title');
        $this->description        = $this->get_option('description');
		
		$this->apikey_live			  = $this->get_option('apikey_live');
		$this->environment			  = $this->get_option('environment');
		$this->apikey_sandbox	      = $this->get_option('apikey_sandbox');
		
		$this->to_euro_rate		  = $this->get_option('to_euro_rate');
		
		
		$this->log = new WC_Logger();
		
		add_action('woocommerce_api_wc_gateway_mw', array( &$this, 'meowallet_response'));
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options'));
		add_action('wp_enqueue_scripts', array( &$this, 'meowallet_ckeckout_scripts'));
		add_action('print_admin_scripts_wc_ps', array(&$this, 'meowallet_admin_scripts'));
		add_action('print_admin_scripts_wc_settings', array(&$this, 'meowallet_admin_scripts'));
		add_action('validate_request', array($this, '_requests'));
		
		// http://stackoverflow.com/questions/22577727/problems-adding-action-links-to-wordpress-plugin
		//$prefix = is_network_admin() ? 'network_admin_' : '';
		//add_filter("{$prefix}plugin_action_links_$plugin_basename",array($this,'plugin_action_links'),10,4);
		add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'plugin_action_links'));
		
		
		
		
	}
	/*
	 * @param array $actions since wc 1.0.6
	 * @return if plugin is active! 
	 */
	function plugin_action_links($actions, $plugin_file, $plugin_data, $context){
		
		$action_after_plugin_active = array(
			'configure' => sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=wc-settings&tab=checkout'), __('Configurar', 'meowallet')),
			'docs'		=> sprintf('<a href="%s" target="_blank">%s</a>', 'https://docs.jquiterio.eu/plugins/meowallet/docs', __('Docs', 'meowallet')),
			'suporte'	=> sprintf('<a href="%s" target="_blank">%s</a>', 'https://jquiterio.eu/support', __('Suporte', 'meowallet'))
		);
		return array_merge($action_after_plugin_active, $actions);
	}
	function meowallet_ckeckout_scripts(){
		wp_enqueue_script( 'meowallet-admin', mw_plugin_dir . 'assets/js/wc_admin_script.js', array('jquery'));
	 }
	
	function meowallet_admin_scripts(){
		wp_enqueue_script('meowallet_integration', mw_plugin_dir . 'assets/js/mw_script.js', array('wc_meowallet'));
	}
	
	public function admin_options() {
		$image_path = mw_plugin_dir . 'assets/mw.png';
		 ?>
		<!-- <h3><?php _e('MEO Wallet', 'meowallet'); ?></h3> -->
		<?php echo "<a href=\"https://wallet.pt\"><img src=\"$image_path\" /></a>"; ?>
		<p><?php _e('Pagamentos via MEO Wallet', 'meowallet'); ?></p>
		<table class="form-table">
			<?php
			$this->generate_settings_html();
			?>
			</table>
		<?php
	}
	
	function init_form_fields(){
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Activar/Desactivar', 'meowallet'),
				'label' => __('Activar o MEO Wallet', 'meowallet'),
				'type'  => 'checkbox',
				'description' => __('Activar o MEO Wallet', 'meowallet'),
				'default' => 'yes'
			),
			'title' => array(
				'title' => __('Titulo', 'meowallet'),
                'type' => 'text',
                'description' => __('Dá um titulo à forma de pagamento para ser visto durante o pagamento', 'meowallet'),
                'default' => __('MEO Wallet', 'meowallet')
			),
			'description' => array(
                'title' => __('Descrição', 'meowallet'),
                'type' => 'textarea',
                'description' => __('Oferece uma Descrição da forma de pagamento por MEO Wallet aos seus clientes para ser vista durante o processo de pagamento', 'meowallet'),
                'default' => __('Pagar com MEO Wallet - MEO Wallet, Multibanco, Cartão de Crédito/Débito', 'meowallet')
			),
			'apikey_live' => array(
				'title' => __('Chave API', 'meowallet'),
				'type' 	=> 'text',
				'description' => __('Introduza a sua Chave API do MEO Wallet . Não é a mesma que a Chave API do MEO Wallet-Sandbox. <br />Para obter a sua Chave API, clique <a target="_blank" href="https://www.sandbox.meowallet.pt/login/">aqui</a>', 'meowallet'),
				'default' => '',
				'class' => 'production_settings sensitive'
				
			),
			'apikey_sandbox' => array(
				'title' => __('Chave API Sandbox', 'meowallet'),
				'type' 	=> 'text',
				'description' => __('Introduza a sua Chave API de testes do MEO Wallet. <br />Para obter a sua Chave API, clique <a target="_blank" href="https://www.wallet.pt/login/">aqui</a>', 'meowallet'),
				'default' => '',
				'class' => 'sandbox_settings sensitive'
			),
			'environment' => array(
				'title' => __('Escolher Ambiente de Trabalho', 'meowallet'),
				'type'  => 'select',
				'label' => __('Activar o MEO Wallet em modo de tests!', 'meowallet'),
				'description' => __('Escolha o seu Ambiente de Trabalho entre Teste e Produção.', 'meowallet'),
				'default' => 'sandbox',
				'options'   => array(
              		'sandbox'    => __( 'Teste', 'meowallet' ),
              		'production'   => __( 'Produção', 'meowallet' ),				
				),
			)
		);
		if (get_woocommerce_currency() != 'EUR'){
			 $this->form_fields['ex_to_euro'] = array(
			 	'title' => __("Taxa de Cambio para Euro", 'meowallet'),
			 	'type' => 'text',
           		'description' => 'Taxa de Cambio para Euro',
           		'default' => '1',
			 );
		}
	}
	
	function process_payment($order_id){
		global $woocoommerce;
		
		return array(
			'result' => 'success',
			'redirect' => $this->charge_payment($order_id)
		);
	}
	
	function charge_payment($order_id){
		global $woocommerce;
		$order_items = array();
		
		$order = new WC_Order( $order_id );
		
		MEOWallet_Config::$isProduction = ($this->environment == 'production') ? TRUE : FALSE;
		MEOWallet_Config::$apikey 		= (MEOWallet_Config::$isProduction) ? $this->apikey_live : $this->apikey_sandbox;
		//MEOWallet_Config::$isTuned = 'yes';
		
		$params = array(
			'payment' => array(
				'client' => array(
					'name' => $order->billing_first_name. ' '. $order->billing_last_name,
					'email' => $_POST['billing_email'],
					'address' => array(
						'country' 	=> $_POST['billing_country'],
						'address' 	=> $_POST['billing_address_1'],
						'city'    	=> $_POST['billing_city'],
						'postalcode'=> $_POST['billing_postcode']
					)
				),
				'amount' => $order->get_total(),
				'currency' => 'EUR',
				'items' => $items,
				'url_confirm' => get_permalink( woocommerce_get_page_id('shop')) . '?' . $post_result_array->id,
				'url_cancel' => get_permalink( woocommerce_get_page_id('shop')) . '?' . $post_result_array->id
			)
		);
		$client_details = array();
		$client_details['name'] = $_POST['billing_first_name']. ' '. $_POST['billing_last_name'];
        $client_details['email'] = $_POST['billing_email'];
		//$client_details['address'] = $client_address;
		
		$client_address = array();
		$client_address['country'] = $_POST['billing_country'];
		$client_address['address'] = $_POST['billing_address_1'];
		$client_address['city'] = $_POST['billing_city'];
		$client_address['postalcode'] = $_POST['billing_postcode'];
		//$params['payment']['client'] = $client_details;
		//$params['payment']['client']['address'] = $client_address;
		
		$items = array();
		if(sizeof($order->get_items()) > 0){
			foreach($order->get_items() as $item){
		 		if ($item['qty']) {
		 			$product = $order->get_product_from_item($item);
					$client_items = array();
					$client_items['client']['id'] = $item['id'];
					$client_item['client']['name'] = $item['name'];
					$client_items['client']['description'] = $item['name'];
					$client_item['client']['qt']   = $item['qty'];	
					
					$items['client'][] = $client_items;
		 		}
		 	}
		}
		if($order->get_total_shipping() > 0) {
			$items[] = array(
				  'id' => 'shippingfee',
            'price' => $order->get_total_shipping(),
            'quantity' => 1,
            'name' => 'Shipping Fee',
			);
		}
		if($order->get_total_tax() > 0) {
			$items[] = array(
				  'id' => 'taxfee',
            'price' => $order->get_total_tax(),
            'quantity' => 1,
            'name' => 'Tax',
				
			);
		}
		if($order->get_order_discount() > 0) {
          $items[] = array(
            'id' => 'totaldiscount',
            'price' => $order->get_total_discount() * -1,
            'quantity' => 1,
            'name' => 'Total Discount'
          );
        }
		if(sizeof($order->get_fees() ) > 0){
		  $fees = $order->get_fees();
          $i = 0;
          foreach($fees as $item ) {
            $items[] = array(
              'id' => 'itemfee' . $i,
              'price' => $item['line_total'],
              'quantity' => 1,
              'name' => $item['name'],
            );
            $i++;
          }
		}
		//$params['client']['amount'] = $order->get_total();
		//if (get_woocommerce_currency() != 'EUR'){
		//	foreach ($items as &$item){
		//		$item['price'] = $item['price'] * $this->to_euro_rate;
		//	}
		//	unset($item);
		//	$params['payment']['amount'] *= $this->to_euro_rate;
		//}
		//$params['payment']['items'] = $items;
		
		
		//$woocommerce->cart->empty_cart();
		
		return MEOWallet_Checkout::getRedirectionUrl($params);
	}
	function meowallet_response(){
		global $woocommerce;
		@ob_clean();
		
		global $woocommerce;
		$order = new WC_Order($order_id);
		
		MEOWallet_Config::$isProduction = ($this->environment == 'production') ? TRUE : FALSE;
		
		if($this->environment == 'production') {
			MEOWallet_Config::$apikey = $this->apikey_live;
		} else {
			MEOWallet_Config::$apikey = $this->apikey_sandbox;
		}
		
		$_notification = new MEOWallet_Notify();
		
		if(in_array('NEW', $_notification->payment['status'])){
		//	 header('Location: ' .  $url_redirect);
			 header( 'HTTP/1.1 200 OK' );
			if ($order->get_order($_notification->order_id) == TRUE){
				$_notification = MEOWallet_Trasact::status($_notification->order_id);
				do_action("wallet-request", $_notification);
			}
		} 
	}
	function _requests($_notification){
		global $woocommerce;
		
		$order = new WC_Order($_notification->order_id);
		if($_notification->status == 'COMPLETE') {
			$order->payment_complete();
			$woocommerce->cart->empty_cart();
		} elseif($_notification->status == 'PENDING') {
			$order->update_status('on-hold');
		} elseif($_notification->status == 'FAIL') {
			$order->update_status('failed');
		}
		exit;
	}
		 	
}
