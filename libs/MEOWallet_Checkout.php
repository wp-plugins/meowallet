<?php

class MEOWallet_Checkout {
	
	public static function getRedirectionUrl($params){
		
	//	$links = array(
	//		'url_confirm' => get_permalink( woocommerce_get_page_id('shop')) . '?' . $post_result_array->id,
	//		'url_cancel' => get_permalink( woocommerce_get_page_id('shop')) . '?' . $post_result_array->id
	//	);
	//	if(array_keys($params) == 'item'){
	//		$amount = 0;
	//		foreach ($param['payment']['items'] as $item){
	//			$amount += $item['qt'] * $item['price'];
	//		}
	//		$payload['payment']['amount'] = $amount;
	//	}
	//	$payload = array_merge((array)$params, (array)$links);
		
		$result = MEOWallet_Request::post(
			   MEOWallet_Config::getEnvURl() . '/checkout/',
			   MEOWallet_Config::$apikey, $params);
			   
		return $result->url_redirect;
	}
	
}
