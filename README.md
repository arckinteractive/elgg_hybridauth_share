Content Sharing for Hybridauth Client
=====================================

A tool that allows sharing of content to hybridauth providers.

Add checkboxes to your form with:
```php
echo elgg_view('hybridauth/share');
```

Add your subtypes to the config, e.g.:
```
elgg_set_config('hybridauth_share_subtypes', array(
	'thewire'
));
```