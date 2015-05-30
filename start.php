<?php

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/events.php';

elgg_register_event_handler('init', 'system', 'elgg_hybridauth_share_init');

/**
 * Initialize the plugin
 */
function elgg_hybridauth_share_init() {

	elgg_set_config('hybridauth_share_providers', array(
		'Facebook',
		'Twitter',
		'LinkedIn',
	));

	elgg_set_config('hybridauth_share_subtypes', array());
	elgg_set_config('hybridauth_share_postfix', 'via ' . elgg_get_site_entity()->name);
	
    // Loaded from min
	//elgg_register_js('hybridauth.share.js', elgg_get_simplecache_url('js', 'hybridauth/share'));
	elgg_register_css('hybridauth.share.css', elgg_get_simplecache_url('css', 'hybridauth/share'));

	elgg_register_event_handler('create', 'object', 'elgg_hybridauth_share_event');
//	elgg_register_event_handler('login', 'user', 'elgg_hybridauth_share_user_login');

	elgg_register_plugin_hook_handler('route', 'hybridauth', 'elgg_hybridauth_share_router');
	elgg_register_plugin_hook_handler('hybridauth:authenticate', 'Facebook', 'elgg_hybridauth_fb_authenticate');
}


function elgg_hybridauth_share_log($message) {
	$file = elgg_get_config('dataroot') . 'elgg_hybridauth_share_log';
	
	file_put_contents($file, $message . "\n", FILE_APPEND);
}