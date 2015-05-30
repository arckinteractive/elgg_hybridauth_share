<?php

/**
 * Display a helpful message when the auth is complete in a popup
 * @param string $hook	'route'
 * @param string $type	'hybridauth'
 * @param array $return
 * @return boolean
 */
function elgg_hybridauth_share_router($hook, $type, $return) {

	$segments = elgg_extract('segments', $return);

	if ($segments[0] == 'popup') {
		echo elgg_view_page('', elgg_view('hybridauth/popup'));
		return false;
	}

	return $return;
}


/**
 * Facebook has just been authenticated
 * we're looking for pages they manage to see if there are access tokens
 * 
 * @param type $hook
 * @param type $provider
 * @param type $return
 * @param type $params
 */
function elgg_hybridauth_fb_authenticate($hook, $provider, $return, $params) {
	$user = $params['entity'];
	
	elgg_hybridauth_share_update_fb_pages($user);
}
