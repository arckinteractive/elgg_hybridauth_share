<?php

$english = array(

	'hybridauth:share:Facebook' => 'Post to Facebook',
	'hybridauth:share:Twitter' => 'Post to Twitter',
	'hybridauth:share:LinkedIn' => 'Post to LinkedIn',

	'hybridauth:share:auth_done' => 'Autnentication is now complete',
	'hybridauth:share:confirm_auth' => 'In order to post to %s you need to authenticate your account and provide our site with necessary permissions to post on your behalf. Would you like to proceed?',

	'hybridauth:share:settings:posting' => 'Enable posting to following providers',
	'hybridauth:share:settings:posting:help' => 'Note that only Facebook, Twitter, and LinkedIn have default configuration. You will need to implement your logic for the rest',

	'hybridauth:share:settings:postfix' => 'Signature to append to each post',

	'hybridauth:share:settings:fb_pages' => 'Enable posting as managed Facebook pages',
	'hybridauth:share:settings:fb_pages:help' => 'This will require "manage_pages" scope in HybridAuth Facebook settings',

	'hybridauth:share:settings:linkedin_pages' => 'Enable posting as LinkedIn companies',
	'hybridauth:share:settings:linkedin_pages:help' => 'This will require "rw_company_admin" scope in HybridAuth LinkedIn settings',

	'hybridauth:share:error:unknown' => 'Connection to the provider can not be established at this time',

	'hybridauth:share:fail' => 'Your post was saved, but there was a problem sharing it to %s',
);

add_translation("en", $english);
