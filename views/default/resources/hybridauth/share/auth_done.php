<?php

$title = elgg_echo('hybridauth:share:auth_done');

$layout = elgg_view_layout('one_column', [
	'title' => $title,
	'content' => elgg_view('hybridauth/auth_done'),
]);

echo elgg_view_page($title, $layout);