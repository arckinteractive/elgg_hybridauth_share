<?php

elgg_load_css('hybridauth.css');
elgg_load_css('hybridauth.share.css');
elgg_load_js('hybridauth.share.js');


$user_guid = elgg_get_logged_in_user_guid();

$providers = elgg_get_config('hybridauth_share_providers');

foreach ($providers as $provider) {

	$adapter = false;

	try {
		$attributes = array(
			'type' => 'checkbox',
			'name' => 'hybridauth_share[]',
			'value' => $provider,
			'data-provider' => $provider,
			'class' => 'hybridauth-share-destination-checkbox',
			'checked' => false,
		);

		//if (!elgg_hybridauth_share_check_permissions($provider)) {
        if (!elgg_get_plugin_user_setting("$provider:uid", $user_guid, 'elgg_hybridauth')) {
			$attributes['data-auth'] = true;
		} else {
			$attributes['checked'] = true;
		}

		$attrs = elgg_format_attributes($attributes);
		
		$form .= '<li>';
		$form .= '<label class="hybridauth-share-destination">';
		$form .= "<input $attrs />";
		$form .= elgg_view_icon(strtolower("auth-$provider")) . elgg_echo("hybridauth:share:$provider");
		$form .= '</label>';
		$form .= '</li>';

	} catch (Exception $e) {
		// service is not enabled
	}
}

if ($form) {
	echo '<ul class="hybridauth-share-providers">' . $form . '</ul>';
}
