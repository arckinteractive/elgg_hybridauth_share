<?php

/**
 * Log a message
 *
 * @param string $message Message
 * @return void
 */
function elgg_hybridauth_share_log($message, $operation = 'API') {
	if (!elgg_get_config('debug')) {
		return;
	}

	$file = elgg_get_config('dataroot') . 'elgg_hybridauth_share_log';
	file_put_contents($file, date(DATE_RSS) . " ($operation): " . $message . "\n", FILE_APPEND);
}

/**
 * Returns an array of object subtypes that are allowed to be shared to providers
 * @return array
 */
function elgg_hybridauth_share_subtypes() {
	$subtypes = elgg_get_config('hybridauth_share_subtypes');
	if (!$subtypes) {
		$subtypes = array();
	}

	return elgg_trigger_plugin_hook('get_subtypes', 'hybridauth:share', null, $subtypes);
}

/**
 * Verifies that a user has posting permissions
 *
 * @param string   $provider       Provider name
 * @param ElggUser $session_owner  HA session owner
 * @param string   $session_name   HA session name
 * @param string   $session_handle HA session handle
 * @param string $target_uid       UID of the target page/account if not posting to user account
 * @return boolean
 */
function elgg_hybridauth_share_check_permissions($provider, ElggUser $session_owner = null, $session_name = null, $session_handle = null, $target_uid = null) {

	$ha_session = new \Elgg\HybridAuth\Session($session_owner, $session_name, $session_handle);
	$ha_provider = $ha_session->getProvider($provider);

	if (!$ha_provider) {
		return false;
	}

	switch ($provider) {

		case 'Facebook' :
			try {

				$publish_actions = false;

				$uid = $ha_session->isAuthenticated($ha_provider);
				$result = $ha_session->getAdapter($ha_provider)->api()->api("/$uid/permissions", "GET", array());
				elgg_hybridauth_share_log(print_r($result, true), 'Facebook Permissions');
				foreach ($result['data'] as $permission) {
					if ($permission['permission'] == 'publish_actions' && $permission['status'] == 'granted') {
						$publish_actions = true;
					}
				}
				if (!$target_uid) {
					return $publish_actions;
				}

				$manage_pages = false;

				$result = $ha_session->getAdapter($ha_provider)->api()->api("/{$uid}/accounts", "GET");
				elgg_hybridauth_share_log(print_r($result, true), 'Facebook Accounts');

				if (isset($result['data']) && is_array($result['data'])) {
					foreach ($result['data'] as $array) {
						if ($target_uid == $array['id'] && $array['access_token'] && in_array('ADMINISTER', $array['perms'])) {
							$manage_pages = true;
						}
					}
				}

				return ($publish_actions && $manage_pages);
			} catch (Exception $e) {
				elgg_hybridauth_share_log($e->getMessage());
			}
			return false;

		default :
			return $ha_session->isConnected($ha_provider);
	}

	return false;
}

/**
 * Retrieves information about users' managed FB pages
 *
 * @param ElggUser $user
 * @return array
 */
function elgg_hybridauth_share_update_fb_pages($user) {

	$pages = array();

	try {

		$ha_session = new \Elgg\HybridAuth\Session($user);
		$ha_provider = $ha_session->getProvider('Facebook');
		$uid = $ha_session->isAuthenticated($ha_provider);

		$result = $ha_session->getAdapter($ha_provider)->api()->api("/{$uid}/accounts", "GET");
		elgg_hybridauth_share_log(print_r($result, true), 'Managed Facebook pages');

		if (isset($result['data']) && is_array($result['data'])) {
			foreach ($result['data'] as $array) {
				if ($array['access_token'] && in_array('ADMINISTER', $array['perms'])) {
					$pages[] = $array['id'] . '::' . $array['name'];
				}
			}
		}
	} catch (Exception $ex) {
		elgg_hybridauth_share_log($ex->getMessage(), 'Managed Facebook pages');
	}

	return $pages;
}

/**
 * Retrieves information about users' administered LinkedIn companies
 *
 * @param ElggUser $user
 * @return array
 */
function elgg_hybridauth_share_update_linkedin_pages($user) {

	$pages = array();

	try {

		$ha_session = new \Elgg\HybridAuth\Session($user);
		$ha_provider = $ha_session->getProvider('LinkedIn');

		$response = $ha_session->getAdapter($ha_provider)->api()->company('?is-company-admin=true&format=json', true);
		$result = json_decode($response['linkedin'], true);

		elgg_hybridauth_share_log(print_r($response, true), 'Administered LinkedIn Companies');

		if (isset($result['values']) && is_array($result['values'])) {
			foreach ($result['values'] as $array) {
				$pages[] = $array['id'] . '::' . $array['name'];
			}
		}
	} catch (Exception $ex) {
		elgg_hybridauth_share_log($ex->getMessage(), 'Administered LinkedIn Companies');
	}

	return $pages;
}
