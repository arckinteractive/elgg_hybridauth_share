<?php

$entity = elgg_extract('entity', $vars);

$ha_session = new \Elgg\HybridAuth\Session();
$providers = $ha_session->getProviders();

echo '<div>';
echo '<label>' . elgg_echo('hybridauth:share:settings:posting') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('hybridauth:share:settings:posting:help') . '</div>';

echo '<ul class="elgg-checkboxes elgg-vertical">';

foreach ($providers as $provider) {
	$name = $provider->getName();
	echo '<li>';
	echo elgg_view('input/checkbox', array(
		'name' => "params[$name]",
		'value' => 1,
		'default' => 0,
		'checked' => (bool) $entity->$name,
	));

	echo $name;
	echo '</li>';
}

echo '</ul>';
echo '</div>';


echo '<div>';
echo '<label>' . elgg_echo('hybridauth:share:settings:postfix') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[postfix]',
	'value' => $entity->postfix,
));
echo '</div>';


echo '<div>';
echo '<label>' . elgg_echo('hybridauth:share:settings:fb_pages') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('hybridauth:share:settings:fb_pages:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[fb_pages]',
	'value' => $entity->fb_pages,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('hybridauth:share:settings:linkedin_pages') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('hybridauth:share:settings:linkedin_pages:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[linkedin_pages]',
	'value' => $entity->linkedin_pages,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';