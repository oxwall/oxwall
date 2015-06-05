var OW_Captcha = function( $params )
{
    var self = this;

    //this.errors = [];

    this.captchaId = $params.captchaId;
    this.captchaClass = $params.captchaClass;
    this.captchaUrl = $params.captchaUrl;
    this.responderUrl = $params.responderUrl;
    this.captcha = $( '#' + this.captchaId );
    this.form = $( this.captcha.parents('form').get(0) );

    this.refresh = function()
    {
        var $img = $( '.' + self.captchaClass + ' #siimage');
        $img.replaceWith($img.clone().attr('src', this.captchaUrl + '?sid=' + Math.random()));
    }

    this.validateCaptcha = function()
    {
            var element = owForms[self.form.attr('name')].elements[this.captcha.attr('name')];

            var result = {};
            //self.errors['captcha']['error'] = undefined;

            $.ajax( {
                        url: self.responderUrl,
                        type: 'POST',
                        data: { command: 'checkCaptcha', value: this.captcha.val() },
                        dataType: 'json',
                        async: false,
                        success: function(data)
                        {
                            result = data;
                        }
                    } );

           //var data = $.httpData( xhr, 'json' );

            if( result.result == false )
            {
                 // self.errors['captcha']['error'] = OW.getLanguageText('base', 'join_error_password_not_valid');
                 self.refresh();
                 return false;
            }

            return true;
    }

    $( '.' + this.captchaClass + ' #siimage_refresh').click( function(){self.refresh()} );
}

