var MassMailing = function( $responderUrl )
{
    var self = this;


    this.focusElement = null;
        
    this.responderUrl = $responderUrl;

    this.sentRequest = false;
    //this.userSelection = $(".user_section input");
    this.userRoles = $("#massMailingForm input[name='userRoles[]']");

    this.emailFormat = $("#massMailingForm select[name=emailFormat]");
    this.subject = $("#massMailingForm input[name=subject]");
    this.body = $("#massMailingForm textarea[name=body]");

    this.previewSubject = $(".previewSubject");
    this.previewBody = $(".previewBody");

    this.emailFormat.change(function(){ this.form.submit() })

    this.subject.focus( function() {
        self.focusElement = this;
    });

    this.body.focus( function() {
        self.focusElement = this;
    });

    this.getCountMassMailingUsers = function()
    {
        if ( this.sentRequest === false )
        {
            this.sentRequest = true;

            self.userRoles.attr( 'disabled', 'disabled' );

            var values = {};
            var roles = {};
            $("#massMailingForm input[name='userRoles[]']:checked").each(function(order, o){
                roles[$(o).val()] = $(o).val();
            });

            values['roles'] = roles;

            $('.total_members').html( "<div style='height:30px;width:25px;' class='ow_preloader_content'></div>"  );

            $.ajax( {
                            url: this.responderUrl,
                            type: 'POST',
                            data: {command: 'countMassMailingUsers', values: JSON.stringify(values)},
                            dataType: 'json',

                            success: function( data )
                            {
                                self.sentRequest = false;
                                self.userRoles.removeAttr( 'disabled' );

                                var params = [];
                                params['count'] = data.result;
                                $('.total_members').html( OW.getLanguageText('admin', 'massmailing_total_members', params) );
                            }
                        } );
        }
    }

    //this.userRoles.click( function(){self.getCountMassMailingUsers()} );
    this.userRoles.click( function(){self.getCountMassMailingUsers()} );

    this.copyPreviewSubject = function()
    {
        var $format = self.emailFormat.val();

        if( $format == 'html' )
        {
            self.previewSubject.html( self.subject.val() );
        }
        else
        {
            self.previewSubject.text( self.subject.val() );
        }
    }

    this.copyPreviewBody = function()
    {
        var $format = self.emailFormat.val();

        if( $format == 'html' )
        {
            self.previewBody.html( self.body.val() );
        }
        else
        {
            self.previewBody.html( self.nl2br( this.htmlspecialchars( self.body.val() ) ) );
        }
    }
    
    this.nl2br = function( $str )
    {
        return ($str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br />' +'$2');
    }

    this.htmlspecialchars = function(text)
    {
       var chars = Array("&", "<", ">", '"', "'");
       var replacements = Array("&amp;", "&lt;", "&gt;", "&quot;", "'");
       for (var i=0; i<chars.length; i++)
       {
           var re = new RegExp(chars[i], "gi");
           if(re.test(text))
           {
               text = text.replace(re, replacements[i]);
           }
       }
       return text;
    }

    this.addVar = function( $varname )
    {
        var $element = $(self.focusElement);

        if( !$element.length )
        {
            $element = $('#mass_mailing_body');
        }

        $element.val($element.val()+$varname);

    }
}

