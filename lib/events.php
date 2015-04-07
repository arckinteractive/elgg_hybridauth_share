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
		$params = elgg_trigger_plugin_hook('hybridauth:share:' . $provider, $subtype, array('entity' => $entity), $gen_params);

		try {
			$ha = new ElggHybridAuth();
			$adapter = $ha->getAdapter($provider);
			if (!$adapter->isUserConnected()) {
				throw new Exception('Not connected');
			}
			$adapter = $ha->authenticate($provider);

			$postfix = elgg_get_config('hybridauth_share_postfix');

			switch ($provider) {
				case 'Facebook' :
					$adapter->api()->api("/me/feed", "POST", $params);
					break;

				case 'Twitter' :
					$link = $params['link'];
					$length = ($link) ? 114 : 137; // t.co link is 23 chars long + 3 spaces
					$status = implode(' ', array_filter(array(elgg_get_excerpt($params['message'], $length - strlen($postfix)), $link, $postfix)));
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
		}
	}

	return true;
}
