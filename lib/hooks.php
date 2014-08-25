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
