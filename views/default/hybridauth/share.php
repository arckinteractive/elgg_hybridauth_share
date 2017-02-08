<?php

elgg_load_css('hybridauth.css');

$user_guid = elgg_get_logged_in_user_guid();

$providers = (array) elgg_get_config('hybridauth_share_providers'); // BC
$providers = elgg_trigger_plugin_hook('share:providers', 'hybridauth', $vars, $providers);

$fields = [];

$ha_session = new \Elgg\HybridAuth\Session();

foreach ($providers as $provider_name) {

	$provider = $ha_session->getProvider($provider_name);

	if (!$provider || !$provider->isEnabled()) {
		continue;
	}
	
	$auth_endpoint = elgg_normalize_url('hybridauth/authenticate');
	$auth_endpoint = elgg_http_add_url_query_elements($auth_endpoint, [
		'provider' => $provider_name,
		'elgg_forward_url' => urlencode(elgg_normalize_url('hybridauth/share/auth_done')),
	]);

	$auth_endpoint = elgg_trigger_plugin_hook('share:auth_endpoint', 'hybridauth', [
		'provider' => $provider_name,
	], $auth_endpoint);

	$is_connected = $ha_session->isConnected($provider);

	$field = [
		'#type' => 'checkbox',
		'#class' => 'hybridauth-share-destination',
		'name' => 'hybridauth_share[]',
		'value' => $provider_name,
		'data-provider' => $provider_name,
		'class' => 'hybridauth-share-destination-checkbox',
		'checked' => false,
		'default' => false,
		'label' => elgg_view_icon(strtolower("auth-$provider_name")) . elgg_echo("hybridauth:share:$provider_name"),
		'data-auth' => $is_connected ? null : true,
		'data-dialog' => elgg_echo('hybridauth:share:confirm_auth', [$provider_name]),
		'data-auth-endpoint' => $auth_endpoint,
		'data-popup-opts' => 'location=1,status=1,scrollbars=1,directories=0,menubar=0,toolbar=0,resizable=0,width=800,height=570',
	];

	$fields[] = $field;
}

if (!empty($fields)) {
	echo elgg_view_field([
		'#type' => 'fieldset',
		'#class' => 'hybridauth-share-providers',
		'align' => 'horizontal',
		'fields' => $fields,
	]);
}
?>
<script>require(['hybridauth/share'])</script>