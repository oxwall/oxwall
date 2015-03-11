var mainSettings = function( $responderUrl )
{
    var self = this;

    this.responderUrl = $responderUrl;
    this.floatbox = undefined;
    this.sentRequest = false;

    this.sendMailButton = $("#sendVerifyMail");
    this.email = $("form[name=configSaveForm] input[name=siteEmail]");

    $("#verify_site_email_button").click(
                function() { self.floatbox = new OW_FloatBox({$title: OW.getLanguageText('admin', 'verify_site_email'), $contents: $('#site-email-verify'), height: '170px', width: '350px'}); }
            );

    this.sendMailButton.click(

        this.sendVerificationMail = function()
        {
            if ( self.sentRequest === false )
            {
                try
                {
                    window.owForms['configSaveForm'].getElement('siteEmail').removeErrors()
                    window.owForms['configSaveForm'].getElement('siteEmail').validate();
                }
                catch(e)
                {
                    self.floatbox.close();
                    return;
                }

                self.sentRequest = true;

                $.ajax( {
                            url: self.responderUrl,
                            type: 'POST',
                            data: { command: 'sendVerifyEmail', email: self.email.val() },
                            dataType: 'json',

                            success: function( data )
                            {
                                self.sentRequest = false;

                                if ( !self.empty(data.message) )
                                {
                                    switch(data.type)
                                    {
                                        case 'info':
                                                OW.info(data.message);
                                            break;

                                        case 'warning':
                                                OW.warning(data.message);
                                            break;

                                        case 'error':
                                                OW.error(data.message);
                                            break;
                                    }
                                }

                                self.floatbox.close();
                            }
                        } );
            }
        }
    );

    this.empty = function(mixed_var)
    {
        var key;
        if (mixed_var === "" ||
            mixed_var === 0 ||
            mixed_var === "0" ||
            mixed_var === null ||        mixed_var === false ||
            typeof mixed_var === 'undefined'
        ){
            return true;
        }
        if (typeof mixed_var == 'object') {
            for (key in mixed_var) {
                return false;
            }        return true;
        }

        return false;
    }
}

