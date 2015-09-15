var OW_ChangePassword = function($params)
{
        var self = this;

	this.responderUrl = $params.responderUrl;
        this.formName = $params['formName'];
        this.floatBox = undefined;
        this.errors = {};

        var password = $("#" + this.formName + " input[name='oldPassword']");

        if( password )
        {
            this.errors['password'] = [];
        }

        this.validatePassword = function()
        {
            var element = owForms[this.formName].elements['oldPassword'];
            element.removeErrors();

            self.errors['password']['error'] = undefined;

            var result = {};

            $.ajax( {
                        url: self.responderUrl,
                        type: 'POST',
                        data: { command: 'validatePassword', value: password.val() },
                        async: false,
                        dataType: 'json'
                    } ).done(
                        function(data)
                        {
                            result = data;

                        } );

           //var data = $.httpData( xhr, 'json');



            if( result.result == false )
            {
                 var text = OW.getLanguageText('base', 'join_error_password_not_valid');
                 self.errors.password = {error:text};
                 return false;
            }


//            $.ajax( {
//                    url: self.responderUrl,
//                    type: 'POST',
//                    data: { command: 'checkPassword', value: password.val() },
//                    dataType: 'json',
//                    async: false,
//                    success: function( data )
//                    {
//                        if( data.result == false )
//                        {
//                             self.errors['password']['error'] = OW.getLanguageText('base', 'join_error_password_not_valid');
//                             element.showError( OW.getLanguageText('base', 'join_error_password_not_valid') );
//                        }
//                    }
//            } );
            if( self.floatBox != undefined )
            {
                //self.floatBox.close();
            }
            
            return true;
        }
};