<?php
/**
 *
 * Pixel Ping
 *
 * @link https://github.com/donohoe/pixel-ping
 * @since 1.0.0
 * @package PixelPing
 *
 * @wordpress-plugin
 * Plugin Name: Pixel Ping
 * Plugin URI: https://github.com/donohoe/pixel-ping
 * Description: Capture basic Page Views in a privacy-forward way
 * Author: Michael Donohoe
 * Author URI: https://donohoe.dev/
 * Version: 1.4.1
 * License: GPL2+
 * License URI: 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('PIXEL_PING_SECRET_KEY', 'bluebook');
define('PIXEL_PING_CACHE_KEY',  'pixel_ping');

function pixel_ping_rewrite_rule() {
	add_rewrite_rule('^pixel\.png$', 'index.php?pixel_ping=true', 'top');
}
add_action('init', 'pixel_ping_rewrite_rule', 10);

function pixel_ping_query_vars($vars) {
	$vars[] = 'pixel_ping';
	return $vars;
}
add_filter('query_vars', 'pixel_ping_query_vars',  10, 1);

function pixel_ping_request( $wp ) {
	if ( isset( $wp->query_vars['pixel_ping'] ) && 'true' === $wp->query_vars['pixel_ping'] ) {
		include_once (plugin_dir_path( __FILE__ ) . 'pixel.php');
		exit;
	}
}
add_action( 'parse_request', 'pixel_ping_request', 10, 1 );

function pixel_ping_cron() {
    if ( ! wp_next_scheduled( 'pixel_ping' ) ) {
		$timestamp = strtotime('6:00:00');
        wp_schedule_event( $timestamp, 'daily', 'pixel_ping_event' );
    }
}
add_action( 'wp', 'pixel_ping_cron' );

function pixel_ping_save() {
	$referer_counts = get_transient( PIXEL_PING_CACHE_KEY );
	if (!empty($referer_counts)) {
		save_long_term($referer_counts);
		pixel_ping_csv();
		$referer_counts = array( 'updated' => time() );
		set_transient( PIXEL_PING_CACHE_KEY, $referer_counts, WEEK_IN_SECONDS );
	}
}
add_action( 'pixel_ping_event', 'pixel_ping_save' );

function pixel_ping_csv() {
	$fn_path = plugin_dir_path( __FILE__ ) . 'history/';
	$file_csv = fopen($fn_path . 'pings.csv', 'w');
	fputcsv($file_csv, array('Year', 'Month', 'ID', 'Domain', 'Count', 'URL'));

	foreach (glob($fn_path . '*.json') as $file_json) {
		$fn = basename($file_json, '.json');
		list($year, $month) = explode('_', $fn);

		$content = file_get_contents($file_json);
		$data = json_decode($content, true);

		foreach ($data as $id => $d) {
			fputcsv($file_csv, array($year, $month, $id, $d['domain'], $d['count'], $d['url']));
		}
	}
	fclose($file_csv);
}
