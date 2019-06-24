<?php
/**
 * Plugin Name:     Casadelkiyoh
 * Plugin URI:      https://casadelvino.nl/
 * Description:     This plugin adds a special kiyoh caching functionality
 * Author:          Mitch Hijlkema
 * Author URI:      https://doedejaarsma.nl/
 * Text Domain:     casadelkiyoh
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Casadelkiyoh
 */

// Your code starts here.

require_once __DIR__ . '/vendor/autoload.php';

add_filter( 'kirki_telemetry', '__return_false' );

new cdk_options();
new cdk_model();
