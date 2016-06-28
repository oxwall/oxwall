var AvatarUserSelect = function( list, contextId ){
    this.list = list;
    this.resultList = [];
    this.$context = $('#'+contextId);
}

AvatarUserSelect.prototype = {
    init: function(){
        var self = this;
        $.each( this.list,
            function(index, data){
                $('#'+data.linkId).click(
                    function(){
                        var uai = self.findIndex(data.userId);
                        if( uai == null ){
                            self.resultList.push(data.userId);
                            $(this).addClass('ow_mild_green');
                        }else{
                             self.resultList.splice(uai, 1);
                            $(this).removeClass('ow_mild_green');
                        }

                        var $countNode = $('div.count_label', self.$context);

                        if( $countNode.length > 0 )
                        {
                            $countNode.html($('input.count_label', self.$context).val().replace("#count#", self.resultList.length));
                        }

                        $( '.submit_cont input', self.$context ).val($('input.button_label').val().replace("#count#", self.resultList.length));

                    }
                );
            }
        );

        $('input.submit',this.$context).click(function(){
            self.submit();
        });
    },

    findIndex: function( value ){

        for( var i = 0; i < this.resultList.length; i++){
            if( value == this.resultList[i] ){
                return i;
            }
        }
        return null;
    },

    reset: function(){
        $('a.selected', this.$context).removeClass('selected');
        this.resultList = [];
    },

    submit: function(){
        if( this.resultList.length == 0 )
        {
            OW.warning(OW.getLanguageText('base', 'avatar_user_select_empty_list_message'));
            return;
        }
        OW.trigger('base.avatar_user_list_select', [this.resultList]);
    }
}