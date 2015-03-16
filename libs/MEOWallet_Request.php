<?php

class MEOWallet_Request {
	
	public static function get($url, $apikey, $data){
		return self::doCallGet($url, $apikey, $data);
	}
	public static function post($url, $apikey, $data){
		return self::doCallPost($url, $apikey, $data);
	}
	public static function doCallPost($url, $apikey, $data, $post = TRUE){
	
	//$body = json_encode($data);
	//echo "$url <br>";
	//echo "$apikey<br>";
	//print_r ($data);
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			if($data){
				$body = json_encode($data);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: WalletPT ' . $apikey,
			'Content-Length: ' . strlen($body))
		);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$post_result = curl_exec($ch);	
		curl_close($ch);
		
		if($post_result == FALSE){
			throw new Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));
		} else {
			$post_result_array = json_decode($post_result);
			//$res_id	= $post_result_array->id;
			$res_url_redirect = $post_result_array->url_redirect;
			//$res_url_confirm	= $post_result_array->url_confirm;
			//$res_url_cancel		= $post_result_array->url_cancel;
			if(!isset($res_url_redirect)){
				$message = 'Erro a processar o Pagamento (' . $post_result_array->code . '): '
				. $post_result_array->message;
				throw new Exception($message, $post_result_array->code);
			} else {
				//return header('Location: ' . $post_result_array->url_redirect);
				return $post_result_array;
				//$url_redirect = $result_array->url_redirect;
			}
		}			
	}
	
	public static function doCallGet($url, $apikey){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true); // retirar depois
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: WalletPT ' . $apikey)
		);
		$get_result = curl_exec($ch);
		$get_result_array = json_decode($get_result);

		if(!in_array($get_result_array->status, array('COMPLETE', 'PENDING', 'FAIL'))) sleep(5);
		if(!in_array($post_result_array->status, array('COMPLETE', 'PENDING', 'FAIL'))){
			throw new Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));
		} else {
			return $get_result_array;
			curl_close($ch);
		}
	}
}
