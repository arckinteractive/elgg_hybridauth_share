<?php

elgg_load_css('hybridauth.css');
elgg_load_css('hybridauth.share.css');
elgg_load_js('hybridauth.share.js');

$providers = elgg_get_config('hybridauth_share_providers');

foreach ($providers as $provider) {

	$adapter = false;

	$ha = new ElggHybridAuth();

	try {
		$adapter = $ha->getAdapter($provider);

		$attributes = array(
			'type' => 'checkbox',
			'name' => 'hybridauth_share[]',
			'value' => $provider,
			'data-provider' => $provider,
			'class' => 'hybridauth-share-destination-checkbox',
		);
		if (!elgg_hybridauth_share_check_permissions($provider)) {
			$attributes['data-auth'] = true;
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