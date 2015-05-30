//<script>

	elgg.provide('hybridauth.share');

	hybridauth.share.init = function() {

		if ($('body').find('#hybridauth-share-auth-done').length) {
			self.close();
		}
		
		$(document).on('change', '.hybridauth-share-destination-checkbox[data-auth]', function() {
			var $elem = $(this);
			var provider = $elem.data('provider')
			if ($(this).prop('checked')) {
				if (confirm(elgg.echo('hybridauth:share:confirm_auth', [provider]))) {
					window.open(
							elgg.config.wwwroot + 'hybridauth/authenticate?provider=' + provider + '&elgg_forward_url=' + encodeURIComponent(elgg.config.wwwroot + 'hybridauth/popup'),
							"hybridauth",
							"location=1,status=0,scrollbars=0,width=800,height=570"
							);
					$elem.removeAttr('data-auth');
				} else {
					$(this).prop('checked', false);
				}
			}
		});
	};
	
	elgg.register_hook_handler('init', 'system', hybridauth.share.init);

