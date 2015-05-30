<?php

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/events.php';

elgg_register_event_handler('init', 'system', 'elgg_hybridauth_share_init');
elgg_register_event_handler('upgrade', 'system', 'elgg_hybridauth_share_upgrade');

/**
 * Initialize the plugin
 * @return void
 */
function elgg_hybridauth_share_init() {

	elgg_register_js('hybridauth.share.js', elgg_get_simplecache_url('js', 'hybridauth/share'));
	elgg_register_css('hybridauth.share.css', elgg_get_simplecache_url('css', 'hybridauth/share'));

	elgg_register_event_handler('create', 'object', 'elgg_hybridauth_share_event');
	
	elgg_register_plugin_hook_handler('hybridauth:authenticate', 'Facebook', 'elgg_hybridauth_fb_authenticate');
	elgg_register_plugin_hook_handler('hybridauth:authenticate', 'LinkedIn', 'elgg_hybridauth_linkedin_authenticate');

	elgg_register_action('hybridauth/can_share', __DIR__ . '/actions/hybridauth/can_share.php');
}

/**
 * Sets initial plugin setting values
 * @return type
 */
function elgg_hybridauth_share_upgrade() {
	if (!elgg_is_admin_logged_in()) {
		return;
	}

	$providers = elgg_get_config('hybridauth_share_providers');
	if (!$providers) {
		$providers = array(
			'Facebook',
			'Twitter',
			'LinkedIn',
		);
	}

	foreach ($providers as $provider) {
		if (is_null(elgg_get_plugin_setting($provider, 'elgg_hybridauth_share'))) {
			elgg_set_plugin_setting($provider, 1, 'elgg_hybridauth_share');
		}
	}

	$postfix = elgg_get_config('hybridauth_share_postfix');
	if (!$postfix) {
		$postfix = 'via ' . elgg_get_site_entity()->name;
	}

	if (is_null(elgg_get_plugin_setting('postfix', 'elgg_hybridauth_share'))) {
		elgg_set_plugin_setting('postfix', $postfix, 'elgg_hybridauth_share');
	}

	
}
