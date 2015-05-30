<?php

/**
 * Listen to 'create', 'object' event and post to providers if destination is specified
 * @param string $event	'create'
 * @param string $type	'object'
 * @param ElggEntity $entity
 * @return boolean
 * @throws Exception
 */
function elgg_hybridauth_share_event($event, $type, $entity) {
	
	if (!elgg_instanceof($entity, 'object')) {
		return true;
	}

	$subtypes = elgg_get_config('hybridauth_share_subtypes');
	$subtype = $entity->getSubtype();

	if (!in_array($subtype, $subtypes)) {
		return true;
	}

	$destinations = get_input('hybridauth_share', array());
	if (sizeof($destinations) < 1) {
		return true;
	}

	$default_params = array(
		'message' => $entity->description,
		'link' => $entity->getURL(),
		'picture' => $entity->getIconURL('large'),
		'name' => $entity->title,
		'description' => elgg_get_site_entity()->description,
	);

	switch ($subtype) {

		default :
			$gen_params = elgg_trigger_plugin_hook('hybridauth:share', $subtype, array('entity' => $entity), $default_params);
			$gen_params = array_filter($gen_params);
			break;
	}
	
	foreach ($destinations as $provider) {

		try {
			
			// special case for facebook providers
			if (strpos($provider, 'facebook-page-') === 0) {
				$fb_page_id = str_replace('facebook-page-', '', $provider);
				$provider = 'Facebook';
			}

			$params = elgg_trigger_plugin_hook('hybridauth:share:' . $provider, $subtype, array('entity' => $entity), $gen_params);
					
			$ha = new ElggHybridAuth();
			$adapter = $ha->getAdapter($provider);

			/**
			 * TODO - this is a problem it fails silently after they get disconnected
			 * which can happen simply by logging out of both applications
			 * 
			 * Need to sort out offline access, doesn't seem possible with hybridauth
			 * probably need to fall back to native API calls
			 */
			if (!$adapter->isUserConnected()) {
				throw new Exception('Not connected');
			}
			$adapter = $ha->authenticate($provider);
			
			$postfix = elgg_get_config('hybridauth_share_postfix');

			switch ($provider) {
				case 'Facebook' :
					
					if (!$fb_page_id) {
						$fb_page_id = elgg_get_plugin_user_setting("Facebook:uid", elgg_get_logged_in_user_guid(), 'elgg_hybridauth');
						if (!$fb_page_id) {
							break;
						}
					}
					
					// post to a page
					$response = $adapter->api()->api("{$fb_page_id}/feed", "POST", $params);
					elgg_hybridauth_share_log(print_r($response,1));
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
					$status = array(
						$params['name'],
						$params['message'],
						$params['link'],
						$params['picture'],
						false
					);
					$adapter->setUserStatus($status);
					break;
				
				default :
					$status = implode(' ', array_filter(array($params['message'], $params['link'], $postfix)));
					$adapter->setUserStatus($status);
					break;
			}
		} catch (Exception $e) {
			elgg_log("Error posting to $provider: " . $e->getMessage());
			elgg_hybridauth_share_log($e->getMessage());
		}
	}

	return true;
}


function elgg_hybridauth_share_user_login($event, $type, $user) {
	elgg_hybridauth_share_update_fb_pages($user);
}