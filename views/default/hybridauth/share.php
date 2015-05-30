<?php

elgg_load_css('hybridauth.css');
elgg_load_css('hybridauth.share.css');
elgg_load_js('hybridauth.share.js');


$user = elgg_get_logged_in_user_entity();

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
        if (!elgg_get_plugin_user_setting("$provider:uid", $user->guid, 'elgg_hybridauth')) {
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
		
		if (strtolower($provider) == 'facebook' && $attributes['checked']) {
			$pages = (array) $user->_hybridauth_facebook_share_pages;
			if ($pages) {
				foreach ($pages as $page) {
					$parts = explode('::', $page);
					$attributes['checked'] = false;
					$attributes['data-provider'] = 'facebook-page-' . $parts[0];
					$attributes['value'] = 'facebook-page-' . $parts[0];
					$attrs = elgg_format_attributes($attributes);
					
					$form .= '<li>';
					$form .= '<label class="hybridauth-share-destination">';
					$form .= "<input $attrs />";
					$form .= elgg_view_icon(strtolower("auth-$provider")) . $parts[1];
					$form .= '</label>';
					$form .= '</li>';
				}
			}
		}

	} catch (Exception $e) {
		// service is not enabled
	}
}

if ($form) {
	echo '<ul class="hybridauth-share-providers">' . $form . '</ul>';
}
