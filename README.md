Content Sharing for Hybridauth Client
=====================================
![Elgg 2.3](https://img.shields.io/badge/Elgg-2.3.x-orange.svg?style=flat-square)

A tool that allows sharing of content to hybridauth providers.

## Usage

### Form elements

To add checkboxes to your form, simply output ``hybridauth/share`` view. It will add
checkboxes for configured providers.

```php
echo elgg_view('hybridauth/share');
```

### Providers

You can add supported providers via ```'share:providers','hybridauth'``` hook.
By default, supported providers are Facebook, Twitter and LinkedIn.
You can filter the data being sent to the provider via ``'hybridauth:share:<provider>', '<object_subtype>'`` hook.

### Object subtypes

To add your subtype to share handling, use ```'share:subtypes', 'hybridauth'``` hook.
This will add a create event listener and submit the data to the selected providers.