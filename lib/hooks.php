<?php


/**
 * Facebook has just been authenticated
 * we're looking for pages they manage to see if there are access tokens
 * This doesn't store actual tokens, only caches a list of pages to display in the form
 * 
 * @param string $hook     "hybridauth:authenticate"
 * @param string $provider "Facebook"
 * @param mixed  $return   No expected return
 * @param array  $params   Hook params
 * @return void
 */
function elgg_hybridauth_fb_authenticate($hook, $provider, $return, $params) {
	
	if (!elgg_get_plugin_setting('fb_pages', 'elgg_hybridauth_share')) {
		return;
	}
	
	$user = elgg_extract('entity', $params);
	if (!$user instanceof ElggUser) {
		return;
	}

	$pages = elgg_hybridauth_share_update_fb_pages($user);
	$user->_hybridauth_facebook_share_pages = $pages;
}

/**
 * LinkedIN has just been authenticated
 * we're looking for companies they administer
 *
 * @param string $hook     "hybridauth:authenticate"
 * @param string $provider "LinkedIn"
 * @param mixed  $return   No expected return
 * @param array  $params   Hook params
 * @return void
 */
function elgg_hybridauth_linkedin_authenticate($hook, $provider, $return, $params) {

	if (!elgg_get_plugin_setting('linkedin_pages', 'elgg_hybridauth_share')) {
		return;
	}

	$user = elgg_extract('entity', $params);
	if (!$user instanceof ElggUser) {
		return;
	}

	$pages = elgg_hybridauth_share_update_linkedin_pages($user);
	$user->_hybridauth_linkedin_share_pages = $pages;
}