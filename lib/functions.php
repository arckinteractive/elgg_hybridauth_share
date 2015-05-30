<?php

function elgg_hybridauth_share_check_permissions($provider) {

	$uid = elgg_get_plugin_user_setting("$provider:uid", elgg_get_logged_in_user_guid(), 'elgg_hybridauth');

	if (!$uid) {
		return false;
	}

	try {
		$ha = new ElggHybridAuth();
		$adapter = $ha->getAdapter($provider);
	} catch (Exception $e) {
		return false;
	}

	if ($adapter) {

		try {

			switch ($provider) {

				case 'Facebook' :
					$result = $adapter->adapter->api->api("/v2.1/$uid/permissions", "GET", array());
					foreach ($result['data'] as $permission) {
						if ($permission['permission'] == 'publish_actions' && $permission['status'] == 'granted') {
							return true;
						}
					}
					$adapter->logout();
					return false;

				default :
					return $adapter->isUserConnected();
			}
		} catch (Exception $e) {
			return false;
		}
	}

	return false;
}


function elgg_hybridauth_share_update_fb_pages($user) {

	try {
		$provider = 'Facebook';
		$uid = elgg_get_plugin_user_setting("Facebook:uid", $user->guid, 'elgg_hybridauth');
		if (!$uid) {
			return;
		}
		
		$ha = new ElggHybridAuth();
		$adapter = $ha->authenticate($provider);
		$result = $adapter->api()->api("/{$uid}/accounts", "GET");
		
		$pages = array();
		if (isset($result['data']) && is_array($result['data'])) {
			foreach ($result['data'] as $array) {
				if ($array['access_token'] && in_array('ADMINISTER', $array['perms'])) {
					$pages[] = $array['id'] . '::' . $array['name'];
				}
			}
		}

		$user->_hybridauth_facebook_share_pages = $pages;
	} catch (Exception $ex) {
		error_log($ex->getMessage());
	}
}