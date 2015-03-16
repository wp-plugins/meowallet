<?php

class MEOWallet_Trasact {
	
	public static function status($id){
		return MEOWallet_Request::get(
			   MEOWallet_Config::getEnvURl() . '/operations/' . $id,
			   MEOWallet_Config::$apikey, FALSE)->status;
	}
	/*
	 * @TODO
	 */
	//public static function post($id){
	//	return MEOWallet_Request::post(
	//		   MEOWallet_Config::getEnvURl() . '/chekout/',
	//		   MEOWallet_Config::$apikey, FALSE)->url_redirect;
	//}
	
}
