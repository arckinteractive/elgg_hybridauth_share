define(function (require) {

	var $ = require('jquery');

	$(document).on('change', '.hybridauth-share-destination-checkbox[data-auth]', function () {
		var $elem = $(this);
		if ($elem.prop('checked')) {
			if (confirm($elem.data('dialog'))) {
				window.open(
						$elem.data('authEndpoint'),
						"hybridauth",
						$elem.data('popupOpts')
						);
				$elem.removeAttr('data-auth');
			} else {
				$(this).prop('checked', false);
			}
		}
	});
});

