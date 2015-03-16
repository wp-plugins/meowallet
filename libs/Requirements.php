<?php

if (version_compare(PHP_VERSION, '5.2.1', '<')) {
    throw new Exception('Versão 5.2.1 ou Superior do PHP é necesária para que o MEO Wallet funcione');
}

if (!function_exists('curl_init')) {
  throw new Exception('cURL-PHP é necesário para que o MEO Wallet funcione.');
}
if (!function_exists('json_decode')) {
  throw new Exception('JSON-PHP é necesário para que o MEO Wallet funcione.');
}

require_once('MEOWallet_Config.php');

require_once('MEOWallet_Transact.php');

// Plumbing
require_once('MEOWallet_Request.php');
require_once('MEOWallet_Notify.php');
require_once('MEOWallet_Checkout.php');


// Sanitization
//require_once('Sanitizer.php');