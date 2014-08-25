<?php

function elgg_hybridauth_share_check_permissions($provider) {

	$uid = elgg_get_plugin_user_setting("$provider:uid", $guid, 'elgg_hybridauth');

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
					$result = $adapter->adapter->api->api("/$uid/permissions", "GET", array());
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
