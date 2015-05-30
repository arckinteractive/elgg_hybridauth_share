//<script>

	elgg.provide('hybridauth.share');

	hybridauth.share.init = function () {

		if ($('body').find('#hybridauth-share-auth-done').length) {
			self.close();
		}

		$('.hybridauth-share-destination-checkbox').live('change', function () {
			var $elem = $(this);
			var provider = $elem.data('provider');
			var href = $elem.data('href');

			if ($(this).prop('checked')) {
				elgg.action(href, {
					success: function(data) {
						if (!data.output.can_share) {
							if (confirm(elgg.echo('hybridauth:share:confirm_auth', [provider]))) {
								$('<a>').attr({
									'href': data.output.auth,
									'target': '_blank'
								}).hide().appendTo('body').trigger('click');
							} else {
								$(this).prop('checked', false);
							}
						}
					},
					error: function() {
						elgg.register_error(elgg.echo('hybridauth:share:error:unknown'));
						$elem.prop('checked', false);
					}
				});
			}
		});
	};

	elgg.register_hook_handler('init', 'system', hybridauth.share.init);

