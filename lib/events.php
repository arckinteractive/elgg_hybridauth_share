<?php

/**
 * Listen to 'create', 'object' event and post to providers if destination is specified
 *
 * @param string     $event	'create'
 * @param string     $type	'object'
 * @param ElggEntity $entity
 * @return boolean
 */
function elgg_hybridauth_share_event($event, $type, $entity) {

	if (!$entity instanceof ElggObject) {
		return;
	}

	$subtype = $entity->getSubtype();
	if (!in_array($subtype, elgg_hybridauth_share_subtypes())) {
		return;
	}

	$destinations = get_input('hybridauth_share', array());
	if (empty($destinations)) {
		return;
	}

	$default_params = array(
		'message' => $entity->description,
		'link' => $entity->getURL(),
		'picture' => $entity->getIconURL('large'),
		'name' => $entity->title,
		'description' => elgg_get_site_entity()->description,
	);

	$gen_params = elgg_trigger_plugin_hook('hybridauth:share', $subtype, array('entity' => $entity), $default_params);
	if (empty($gen_params) || !is_array($gen_params)) {
		return;
	}

	$gen_params = array_filter($gen_params);

	$session_owner_guid = get_input('session_owner_guid');
	$user = get_entity($session_owner_guid) ? : elgg_get_logged_in_user_entity();
	$session_name = get_input('session_name');
	$session_handle = get_input('session_handle');

	foreach ($destinations as $destination) {

		list($provider, $target_uid) = explode('-', $destination);

		try {

			$ha_session = new \Elgg\HybridAuth\Session($user, $session_name, $session_handle);
			$ha_provider = $ha_session->getProvider($provider);

			if (!$ha_provider) {
				throw new Exception("$provider is not a valid provider for session $session_name with a handle $session_handle");
			}

			$can_share = elgg_hybridauth_share_check_permissions($provider, $user, $session_name, $session_handle, $target_uid);

			if (!$can_share) {
				throw new Exception("Insufficient permissions to post to $provider");
			}

			$adapter = $ha_session->getAdapter($ha_provider);

			$params = elgg_trigger_plugin_hook('hybridauth:share:' . $provider, $subtype, array('entity' => $entity), $gen_params);

			$postfix = elgg_get_plugin_setting('postfix', 'elgg_hybridauth_share');

			switch ($provider) {
				case 'Facebook' :

					$uid = $ha_session->isAuthenticated($ha_provider);

					if (!$target_uid) {
						$response = $adapter->api()->api("/$uid/feed", "POST", $params);
						elgg_hybridauth_share_log(print_r($response, true), 'Posting to Facebook personal feed');
					} else {
						$api = $ha_session->getAdapter($ha_provider)->api();
						$result = $api->api("/$uid/accounts", "GET");
						elgg_hybridauth_share_log(print_r($response, true), 'Facebook account permissions');

						$access_token = false;
						if (isset($result['data']) && is_array($result['data'])) {
							foreach ($result['data'] as $array) {
								if ($target_uid == $array['id']) {
									$access_token = $array['access_token'];
								}
							}
						}

						if (!$access_token) {
							throw new Exception("No access token for a Facebook page with UID $target_uid");
						}

						$api->setAccessToken($access_token);
						$response = $api->api("/$target_uid/feed", "POST", $params);
						elgg_hybridauth_share_log(print_r($response, true), 'Posting to Facebook as a page');
					}

					break;

				case 'Twitter' :
					$link = $params['link'];
					$length = ($link) ? 114 : 137; // t.co link is 23 chars long + 3 spaces
					$status = implode(' ', array_filter(array(elgg_get_excerpt($params['message'], $length - strlen($postfix)), $link, $postfix)));

					if ($params['picture']) {
						$status = array(
							'message' => $status,
							'picture' => $params['picture']
						);
					}
					$adapter->setUserStatus($status);
					break;

				case 'LinkedIn' :
					if (!$target_uid) {
						$status = array(
							$params['name'],
							$params['message'],
							$params['link'],
							$params['picture'],
							false
						);
						$adapter->setUserStatus($status);
					} else {
						$content = array_filter(array(
							'title' => $params['name'],
							'comment' => $params['message'],
							'submitted-url' => $params['link'],
							'submitted-image-url' => $params['picture'],
						));
						$response = $ha_session->getAdapter($ha_provider)->api()->share('new', $content, false, false, $target_uid);
						elgg_hybridauth_share_log(print_r($response, true), 'Posting to LinkedIn company feed');
					}
					break;
				default :
					$status = implode(' ', array_filter(array($params['message'], $params['link'], $postfix)));
					$adapter->setUserStatus($status);
					break;
			}
		} catch (Exception $e) {
			elgg_hybridauth_share_log($e->getMessage(), "Posting to $provider");
			register_error(elgg_echo('hybridauth:share:fail', array($provider)));
		}
	}

	return true;
}
