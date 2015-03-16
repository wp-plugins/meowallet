<?php
/*
Plugin Name: MEO WALLET
Plugin URI: https://jquiterio.eu/plugins/meowallet/
Description: MEO Wallet Payment Gateway is the best away to accept payments via MEO Wallet, Multibanco and Credit/Debit Card.
Version: 1.8
Author: Jorge Quitério
Author URI: https://jquiterio.eu
License: GPLv3
*/

/*
 * This Plugin contain sources from another open source projects published with GPLv2|3 Licenses
*/
/*
 This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

add_action('plugins_loaded', 'meowallet_init', 0);

function meowallet_init(){
	if ( ! class_exists('WC_Payment_Gateway') ) {
    return;
  	}
	define ('mw_plugin_dir', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
	require_once dirname( __FILE__ ) . '/class/meowallet.php';
	
	add_filter( 'woocommerce_payment_gateways', 'add_meowallet_plugin');
}
function add_meowallet_plugin($methods){
	$methods[] = 'WC_MEOWallet';
	return $methods;
}
