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
			//'Identica',
			//'QQ',
			//'Sina',
			//'Murmur',
			//'Pixnet',
			//'Plurk'
	));

	elgg_set_config('hybridauth_share_subtypes', array());
	elgg_set_config('hybridauth_share_postfix', 'via ' . elgg_get_site_entity()->name);
	
	elgg_register_js('hybridauth.share.js', elgg_get_simplecache_url('js', 'hybridauth/share'));
	elgg_register_css('hybridauth.share.css', elgg_get_simplecache_url('css', 'hybridauth/share'));

	elgg_register_event_handler('create', 'object', 'elgg_hybridauth_share_event');

	elgg_register_plugin_hook_handler('route', 'hybridauth', 'elgg_hybridauth_share_router');
}
