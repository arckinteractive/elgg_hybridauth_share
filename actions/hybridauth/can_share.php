<?php

$provider = get_input('provider');
$session_owner_guid = get_input('session_owner_guid');
$session_owner = get_entity($session_owner_guid);
$session_name = get_input('session_name');
$session_handle = get_input('session_handle');

$page_uid = get_input('page_uid');

$ha_session = new \Elgg\HybridAuth\Session($session_owner, $session_name, $session_handle);
$ha_provider = $ha_session->getProvider($provider);

$can_share = elgg_hybridauth_share_check_permissions($provider, $session_owner, $session_name, $session_handle, $page_uid);

$auth = elgg_normalize_url(elgg_http_add_url_query_elements('hybridauth/authenticate', array(
	'provider' => $provider,
	'elgg_forward_url' => $_SERVER['HTTP_REFERER'],
	'scope' => get_input('scope'),
	'page_uid' => $page_uid,
	'session_owner_guid' => $session_owner->guid,
	'session_name' => $session_name,
	'session_handle' => $session_handle,
)));

if (elgg_is_xhr()) {
	$json = json_encode(array(
		'perm' => $can_share,
		'auth' => $auth,
	));
	echo $json;
	forward(REFERRER);
} else {
	if (!$can_share) {
		forward($auth);
	}
	forward(REFERRER);
}