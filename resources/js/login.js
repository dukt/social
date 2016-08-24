
(function($) {

Craft.SocialLoginForm = Garnish.Base.extend(
{
	$form: null,
	$buttons: null,

	init: function(loginProviders)
	{
		console.log('loginProviders', loginProviders);

		this.$form = $('#login-form');
		this.$buttons = $('> .buttons', this.$form);

		$.each(loginProviders, $.proxy(function(k, loginProvider)
		{
			$('<a href="'+loginProvider.url+'" class="btn submit">Login with '+loginProvider.name+'</a>').appendTo(this.$buttons);
		}, this));
	}
});

})(jQuery);
