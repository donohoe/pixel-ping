<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_domain() {
	$domain = '';
	if (!empty($_SERVER['HTTP_REFERER'])) {
		$url = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
		$bits = parse_url($url);
		$domain = str_replace('www.', '', $bits['host']);
	}
	return $domain;
}

function get_url($url='') {
	if (empty($url)) {
		$url = isset($_GET['u']) ? trim($_GET['u']) : '';
	}
	if (!empty($url)) {
		$url = filter_var($url, FILTER_SANITIZE_URL);
		$bits = parse_url($url);
		$url = $bits['scheme'] . '://' . $bits['host'] . $bits['path'];
	}
	return $url;
}

function save_long_term($referer_counts) {
	$data = array();
	$y = date('Y');
	$m = date('m');

	$fn_path = plugin_dir_path( __FILE__ ) . 'history';
	wp_mkdir_p( $fn_path );

	$fn = "{$fn_path}/{$y}_{$m}.json";
	if (file_exists($fn)) {
		$tmp = file_get_contents( $fn );
		if (!empty($tmp)) {
			$data = json_decode($tmp, true);
		}
	}

	unset($referer_counts['updated']);
	foreach($referer_counts as $k => $referer) {
		if (isset($data[$k])) {
			$data[$k]['count'] = $data[$k]['count'] + $referer['count'];
		} else {
			$data[$k] = $referer;
		}
	}

	$json = json_encode( $data, JSON_PRETTY_PRINT );
	//$json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK );
	file_put_contents($fn, $json);
}

function increment_referer_cache() {
	get_cache_status();
	$url = get_url();
	if (!empty($url)) {
		$id = crc32($url);
		$domain = get_domain();

		$referer_counts = get_transient( PIXEL_PING_CACHE_KEY );
		if ($referer_counts === false) {
			$referer_counts = array( 'updated' => time() );
		}

		if (isset($referer_counts[$id])) {
			$referer_counts[$id]['count']++;
		} else {
			$referer_counts[$id]['count']  = 1;
			$referer_counts[$id]['url']    = $url;
			$referer_counts[$id]['domain'] = $domain;
		}

		if ($referer_counts['updated'] < (time() - (MINUTE_IN_SECONDS))) {
			save_long_term($referer_counts);
			$referer_counts = array( 'updated' => time() );
		}

		set_transient( PIXEL_PING_CACHE_KEY, $referer_counts, WEEK_IN_SECONDS );
	}
}

function get_cache_status(){
	if (defined( 'PIXEL_PING_SECRET_KEY' ) ) {
		$key = isset($_GET['k']) ? trim($_GET['k']) : '';
		if ($key === PIXEL_PING_SECRET_KEY) {
			$referer_counts = get_transient( PIXEL_PING_CACHE_KEY );
			print_r($referer_counts);
			exit();
		}
	}
}

increment_referer_cache();

header('Content-Type: image/png');
$transparent_1x1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
print base64_decode($transparent_1x1);
exit;
