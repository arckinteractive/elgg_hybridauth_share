<?php

$session_owner = elgg_extract('session_owner', $vars, elgg_get_logged_in_user_entity());
$session_name = elgg_extract('session_name', $vars);
$session_handle = elgg_extract('session_handle', $vars);

elgg_load_css('hybridauth.css');
elgg_load_css('hybridauth.share.css');
elgg_load_js('hybridauth.share.js');

$ha_session = new \Elgg\HybridAuth\Session($session_owner, $session_name, $session_handle);
$providers = $ha_session->getEnabledProviders();

foreach ($providers as $ha_provider) {

	$provider = $ha_provider->getName();
	$share_on = elgg_get_plugin_setting($provider, 'elgg_hybridauth_share');
	if (!$share_on) {
		continue;
	}

	$scope = '';
	switch ($provider) {
		case 'Facebook' :
			$scope = 'publish_actions';
			break;

		case 'LinkedIn' :
			$scope = 'w_share';
			break;

	}
	
	$attributes = array(
		'type' => 'checkbox',
		'name' => 'hybridauth_share[]',
		'value' => $provider,
		'data-provider' => $provider,
		'data-href' => elgg_normalize_url(elgg_http_add_url_query_elements('action/hybridauth/can_share', array(
			'provider' => $provider,
			'scope' => $scope,
			'session_owner_guid' => $session_owner->guid,
			'session_name' => $session_name,
			'session_handle' => $session_handle,
		))),
		'class' => 'hybridauth-share-destination-checkbox',
		'checked' => false,
	);

	$attrs = elgg_format_attributes($attributes);

	$form .= '<li>';
	$form .= '<label class="hybridauth-share-destination">';
	$form .= "<input $attrs />";
	$form .= elgg_view_icon(strtolower("auth-$provider")) . elgg_echo("hybridauth:share:$provider");
	$form .= '</label>';
	$form .= '</li>';

	if (elgg_get_plugin_setting('fb_pages', 'elgg_hybridauth_share') && strtolower($provider) == 'facebook') {
		$pages = (array) $session_owner->_hybridauth_facebook_share_pages;
		if ($pages) {
			foreach ($pages as $page) {
				list($page_uid, $page_name) = explode('::', $page);
				$attributes['data-href'] = elgg_normalize_url(elgg_http_add_url_query_elements('action/hybridauth/can_share', array(
					'provider' => 'Facebook',
					'page_uid' => $page_uid,
					'scope' => 'publish_actions,manage_pages',
					'session_owner_guid' => $session_owner->guid,
					'session_name' => $session_name,
					'session_handle' => $session_handle,
				)));
				$attributes['data-provider'] = 'Facebook';
				$attributes['value'] = "Facebook-{$page_uid}";
				$attrs = elgg_format_attributes($attributes);

				$form .= '<li>';
				$form .= '<label class="hybridauth-share-destination">';
				$form .= "<input $attrs />";
				$form .= elgg_view_icon(strtolower("auth-$provider")) . $page_name;
				$form .= '</label>';
				$form .= '</li>';
			}
		}
	}

		if (elgg_get_plugin_setting('linkedin_pages', 'elgg_hybridauth_share') && strtolower($provider) == 'linkedin') {
		$pages = (array) $session_owner->_hybridauth_linkedin_share_pages;
		if ($pages) {
			foreach ($pages as $page) {
				list($page_uid, $page_name) = explode('::', $page);
				$attributes['data-href'] = elgg_normalize_url(elgg_http_add_url_query_elements('action/hybridauth/can_share', array(
					'provider' => 'LinkedIn',
					'page_uid' => $page_uid,
					'scope' => urlencode('w_share+rw_company_admin'),
					'session_owner_guid' => $session_owner->guid,
					'session_name' => $session_name,
					'session_handle' => $session_handle,
				)));
				$attributes['data-provider'] = 'LinkedIn';
				$attributes['value'] = "LinkedIn-{$page_uid}";
				$attrs = elgg_format_attributes($attributes);

				$form .= '<li>';
				$form .= '<label class="hybridauth-share-destination">';
				$form .= "<input $attrs />";
				$form .= elgg_view_icon(strtolower("auth-$provider")) . $page_name;
				$form .= '</label>';
				$form .= '</li>';
			}
		}
	}
}

if ($form) {
	echo '<ul class="hybridauth-share-providers hidden">' . $form . '</ul>';
}

echo elgg_view('input/hidden', array(
	'name' => 'session_owner_guid',
	'value' => $session_owner->guid,
));

echo elgg_view('input/hidden', array(
	'name' => 'session_name',
	'value' => $session_name,
));

echo elgg_view('input/hidden', array(
	'name' => 'session_handle',
	'value' => $session_handle,
));