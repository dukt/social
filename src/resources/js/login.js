
(function($) {

Craft.SocialLoginForm = Garnish.Base.extend(
{
    $form: null,
    $buttons: null,

    init: function(loginProviders, error)
    {
        this.$form = $('#login-form');
        this.$submitBtn = $('#submit');
        this.$buttons = $('> .buttons', this.$form);
        this.$socialLoginButtons = $('<div class="social-login-buttons"></div>').appendTo(this.$buttons);

        $.each(loginProviders, $.proxy(function(k, loginProvider)
        {
            $('<a href="'+loginProvider.url+'" title="Login with '+loginProvider.name+'"><img src="'+loginProvider.iconUrl+'" /></a>').appendTo(this.$socialLoginButtons);
        }, this));

        this.addListener(this.$submitBtn, 'click', $.proxy(function() {
            if (this.$error)
            {
                this.$error.remove();
            }
        }, this));

        if(error)
        {
            this.showError(error);
        }
    },

    showError: function(error)
    {
        if (!error)
        {
            error = Craft.t('An unknown error occurred.');
        }

        this.$error = $('<p class="error" style="display:none">'+error+'</p>').insertAfter($('.buttons', this.$form));
        this.$error.velocity('fadeIn');
    },
});

})(jQuery);
