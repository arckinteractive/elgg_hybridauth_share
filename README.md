Content Sharing for Hybridauth Client
=====================================

A tool that allows sharing of content to hybridauth providers.


## Integrate your plugin

Add checkboxes to your form with:

```php
echo elgg_view('hybridauth/share');
```

Add your object subtype to the array of allowed object subtypes with
```'get_subtypes', 'hybridauth:share'``` hook.



## Updates

* In 1.1, config values were replaced with plugin settings.
