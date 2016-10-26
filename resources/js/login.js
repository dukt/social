
(function($) {

Craft.SocialLoginForm = Garnish.Base.extend(
{
	$form: null,
	$buttons: null,

	init: function(loginProviders)
	{
		this.$form = $('#login-form');
		this.$buttons = $('> .buttons', this.$form);
		this.$socialLoginButtons = $('<div class="social-login-buttons"></div>').appendTo(this.$buttons);

		$.each(loginProviders, $.proxy(function(k, loginProvider)
		{
			$('<a href="'+loginProvider.url+'" title="Login with '+loginProvider.name+'"><img src="'+loginProvider.iconUrl+'" /></a>').appendTo(this.$socialLoginButtons);
		}, this));
	}
});

})(jQuery);
