<?php

class MEOWallet_Config{
	
	public static $apikey;
	public static $isProduction = FALSE;
	//public static $isTuned = FALSE;
	
	const SANDBOX_URL	 = 'https://services.sandbox.meowallet.pt/api/v2';
	const WALLET_URL	 = 'https://services.wallet.pt/api/v2';
	
	public static function getEnvURl(){
		return MEOWallet_Config::$isProduction ? MEOWallet_Config::WALLET_URL : MEOWallet_Config::SANDBOX_URL;
	}
}
