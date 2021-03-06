<?php

/**
 * Share add-on for hybridauth
 *
 * @author Ismayil Khayredinov <ismayil@arckinteractive.com>
 */
require_once __DIR__ . '/autoloader.php';

use Elgg\HybridAuth\Session;

elgg_register_event_handler('init', 'system', 'elgg_hybridauth_share_init');

/**
 * Initialize the plugin
 * @return void
 */
function elgg_hybridauth_share_init() {

	elgg_register_plugin_hook_handler('share:providers', 'hybridauth', 'elgg_hybridauth_share_providers');
	elgg_register_plugin_hook_handler('share:subtypes', 'hybridauth', 'elgg_hybridauth_share_subtypes');
	elgg_register_plugin_hook_handler('share:auth_endpoint', 'hybridauth', 'elgg_hybridauth_share_auth_endpoint');

	elgg_extend_view('elgg.css', 'hybridauth/share.css');

	elgg_register_event_handler('create', 'object', 'elgg_hybridauth_share_event');
	elgg_register_event_handler('update', 'object', 'elgg_hybridauth_share_event');
	elgg_register_event_handler('publish', 'object', 'elgg_hybridauth_share_event');

	elgg_register_plugin_hook_handler('route', 'hybridauth', 'elgg_hybridauth_share_router');

	elgg_register_plugin_hook_handler('hybridauth:share', 'thewire', 'elgg_hybridauth_share_prepare_wall_post');
	elgg_register_plugin_hook_handler('hybridauth:share', 'hjwall', 'elgg_hybridauth_share_prepare_wall_post');

	// Integrate with the wire form
	elgg_extend_view('forms/thewire/add', 'hybridauth/share');

	// Integrate with the wall forms
	elgg_extend_view('input/wall/status', 'hybridauth/share');
}

/**
 * Configure supported providers
 *
 * @param string $hook   "share:providers"
 * @param string $type   "hybridauth"
 * @param array  $return Providers
 * @return array
 */
function elgg_hybridauth_share_providers($hook, $type, $return) {
	return array_merge($return, [
		'Facebook',
		'Twitter',
		'LinkedIn',
	]);
}

/**
 * Configure default subtypes for triggered events
 *
 * @param string $hook   "share:subtypes"
 * @param string $type   "hybridauth"
 * @param array  $return Subtypes by event type
 * @return array
 */
function elgg_hybridauth_share_subtypes($hook, $type, $return) {

	if (elgg_is_active_plugin('thewire')) {
		$return['create'][] = 'thewire';
	}

	if (elgg_is_active_plugin('hypeWall')) {
		$return['publish'][] = 'hjwall';
	}

	return $return;
}

/**
 * Listen to 'create', 'object' event and post to providers if destination is specified
 *
 * @param string     $event	      'create'
 * @param string     $entity_type 'object'
 * @param ElggEntity $entity      Entity
 * @return void
 */
function elgg_hybridauth_share_event($event, $entity_type, $entity) {

	if (!elgg_instanceof($entity, 'object')) {
		return;
	}

	$subtypes = [
		'create' => (array) elgg_get_config('hybridauth_share_subtypes'),
	]; // BC
	$subtypes = elgg_trigger_plugin_hook('share:subtypes', 'hybridauth', [], $subtypes);
	
	$subtypes = (array) elgg_extract($event, $subtypes);

	$subtype = $entity->getSubtype();
	
	if (!in_array($subtype, $subtypes)) {
		return;
	}

	$destinations = get_input('hybridauth_share', array());
	
	if (sizeof($destinations) < 1) {
		return;
	}

	$post = array(
		'message' => strip_tags($entity->description),
		'link' => '',
		'picture' => '',
		'name' => '',
	);

	$post = elgg_trigger_plugin_hook('hybridauth:share', $subtype, [
		'entity' => $entity,
			], $post);
	
	$post = array_filter($post);

	$ha_session = new Session();

	foreach ($destinations as $provider_name) {

		$provider = $ha_session->getProvider($provider_name);
		if (!$ha_session->isConnected($provider)) {
			continue;
		}

		$ha_session->authenticate($provider);

		$params = elgg_trigger_plugin_hook("hybridauth:share:$provider_name", $subtype, [
			'entity' => $entity,
			'session' => $ha_session,
			'provider' => $provider,
				], $post);

		if ($params === true) {
			return;
		}

		try {

			$adapter = $ha_session->getAdapter($provider);
			if (!$adapter) {
				throw new Exception('Unable to instantiate the adapter');
			}

			switch ($provider_name) {
				case 'Facebook' :
					$token = $adapter->getAccessToken();
					$response = $adapter->api()->post('/me/feed', $params, $token['access_token']);
					/* @var $response \Facebook\FacebookResponse */

					$debug_mode = (bool) elgg_get_plugin_setting('debug_mode', 'elgg_hybridauth');
					if ($debug_mode) {
						$file = elgg_get_config('dataroot') . 'elgg_hybridauth_debug';
						file_put_contents($file, PHP_EOL . PHP_EOL . $response->getBody() . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
					}
					break;

				case 'Twitter' :
					$link = $params['link'];
					$length = ($link) ? 674 : 700; // t.co link is 23 chars long + 3 spaces
					$message = implode(' ', array_filter(array(elgg_get_excerpt($params['message'], $length), $link)));
	
					if (strlen($message) > 280) {
						// break up long posts into multiple tweets
						$tweet_maxlength = 273; // allow for 6 extra characters: ' (1/4)' and an extra space
						$text_array = explode(' ', $message);
						$i=0;
						$messages = [''];
						foreach ($text_array as $word) {
							if (strlen($messages[$i] . $word . ' ') > $tweet_maxlength) {
								$i++;
							}
							$messages[$i] .= $word . ' ';
						}
					}
					else {
						$messages = [$message];
					}
					
					$id = '';
					$username = '';

					foreach ($messages as $key => $message) {
						$prefix = '';
						if (count($messages) > 1) {
							$prefix = '(' . ($key + 1) . '/' . count($messages) . ') ';
						}
						
						$status = [
							'message' => $prefix . $message
						];
						
						if ($username) {
							// supposedly required for use with in_reply_to_status_id
							//$status['message'] = '@' . $username . ' ' . $status['message'];
						}
						
						if ($params['picture'] && $key === 0) {
							$status['picture'] = $params['picture'];
						}
						
						if ($id) {
							// this isn't working, dunno why...
							//$status['in_reply_to_status_id'] = $id;
						}
						

						$response = $adapter->setUserStatus($status);
						if ($response->id_str) {
							$id = (string) $response->id_str;
							$username = $response->user->screen_name;
						}
					}
					
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
					$status = implode(' ', array_filter(array($params['message'], $params['link'])));
					$adapter->setUserStatus($status);
					break;
			}
		} catch (Exception $e) {
			elgg_log("Error posting to $provider_name: " . $e->getMessage(), 'ERROR');
		}
	}

	return true;
}

/**
 * Add additional authentication parameters to auth endpoint URL
 * 
 * @param string $hook   "share:auth_endpoint"
 * @param string $type   "hybridauth"
 * @param string $return URL
 * @param array  $params Hook params
 * @return string
 */
function elgg_hybridauth_share_auth_endpoint($hook, $type, $return, $params) {

	$provider = elgg_extract('provider', $params);

	switch ($provider) {

		case 'Facebook' :
			// To post to Facebook we require public_actions scope grant
			return elgg_http_add_url_query_elements($return, [
				'scope' => 'publish_actions',
			]);
	}
}

/**
 * Display a helpful message when the auth is complete in a popup
 * 
 * @param string $hook	'route'
 * @param string $type	'hybridauth'
 * @param array  $return
 * @return boolean
 */
function elgg_hybridauth_share_router($hook, $type, $return) {

	$segments = elgg_extract('segments', $return);

	if ($segments[0] == 'share' && $segments[1] == 'auth_done') {
		echo elgg_view_resource('hybridauth/share/auth_done');
		return false;
	}
}

/**
 * Prepare wire post
 *
 * @param string $hook   "hybridauth:share"
 * @param string $type   "thewire"
 * @param array  $return Post params
 * @param array  $params Hook params
 * @return
 */
function elgg_hybridauth_share_prepare_wire_post($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	if (!elgg_instanceof($entity, 'object', 'thewire')) {
		return;
	}

	// Do we need to add anything here?
	
	return $return;
}

/**
 * Prepare wall post
 * 
 * @param string $hook   "hybridauth:share"
 * @param string $type   "hjwall"
 * @param array  $return Post params
 * @param array  $params Hook params
 * @return 
 */
function elgg_hybridauth_share_prepare_wall_post($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	if (!elgg_instanceof($entity, 'object', 'hjwall')) {
		return;
	}
	
	if ($entity->address) {
		$return['link'] = $entity->address;
	} else if ($attachments = $entity->getAttachments()) {
		$attachment = array_shift($attachments);

		$icon_sizes = ['master', 'large', 'medium'];
		foreach ($icon_sizes as $icon) {
			if ($attachment->hasIcon($icon)) {
				$picture = elgg_get_inline_url($attachment, false, '');
				break;
			}
		}

		$return['name'] = $attachment->getDisplayName();

		if ($attachment->access_id == ACCESS_PUBLIC) {
			$return['link'] = $attachment->getURL();
		}

		if (empty($return['link'])) {
			$return['link'] = $picture;
		} else {
			$return['picture'] = $picture;
		}
	}

	return $return;
}
