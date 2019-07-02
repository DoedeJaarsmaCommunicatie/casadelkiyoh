<?php
/**
 * Plugin Name:     Casadelkiyoh
 * Plugin URI:      https://casadelvino.nl/
 * Description:     This plugin adds a special kiyoh caching functionality
 * Author:          Mitch Hijlkema
 * Author URI:      https://doedejaarsma.nl/
 * Text Domain:     casadelkiyoh
 * Domain Path:     /languages
 * Version:         1.1.1
 *
 * @package         Casadelkiyoh
 */

// Your code starts here.
require_once __DIR__ . '/vendor/autoload.php';

$my_update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/DoedeJaarsmaCommunicatie/casadelkiyoh/',
	__FILE__,
	'casadelkiyoh'
);

add_filter( 'kirki_telemetry', '__return_false' );

new cdk_options();
$kiyoh = new cdk_model();

$kiyoh->hook_api();
