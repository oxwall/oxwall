var MultiselectField = function( id, name ){
    var self = this;
    var $context = $('#'+id);
    var hiddenNodes = {};
    var $choices = $('select.choicesSelect', $context);
    var $selected = $('select.selectedSelect', $context);

    $choices.dblclick(
        function(){
            self.addValue($(this).val());
        }
    );

    $selected.dblclick(
        function(){
            self.removeValue($(this).val());
        }
    );

    $('input.select', $context).click(
        function(){
            var values = $choices.val();
            if( !values ) return;
            for( var i = 0; i < values.length; i++ )
            {
                self.addValue(values[i]);
            }
        }
    );

    $('input.deselect', $context).click(
        function(){
            var values = $selected.val();
            if( !values ) return;
            for( var i = 0; i < values.length; i++ )
            {
                self.removeValue(values[i]);
            }
        }
    );

    this.addValue = function( key ){
        $option = $('option[value='+key+']', $choices);
        $hiddenNode = $('<input type="hidden" name="'+name+'[]" value="'+key+'" />');
        $hiddenNode.appendTo($context);
        hiddenNodes[key] = $hiddenNode;

        if ($option.length > 0)
        {
            $option.appendTo($selected);
        }
    };

    this.removeValue = function( key ){
        $option = $('option[value='+key+']', $selected);
        if ($option.length > 0)
        {
            $option.appendTo($choices);
            hiddenNodes[key].remove();
            delete hiddenNodes[key];
        }
    };

    this.getValue = function(){
        var values = [];
        $.each(hiddenNodes, function(val){values.push(val)});
        return values;
    };

    this.setValue = function( value ){
        self.resetValue();
        for( var i = 0; i < value.length; i++ )
        {
            self.addValue(value[i]);
        }
    };

    this.resetValue = function(){
        $.each(hiddenNodes,
            function(key){
                self.removeValue(key);
            }
        );
    };
}