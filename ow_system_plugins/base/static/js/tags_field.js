TagsField = function( id ){
	var self = this;
	var $cont = $('#'+id);

	var $hidden = $('input[type=hidden]', $cont);

	var $text = $('input:text', $cont);
	
	$('a.tags-field-del-tag', $cont).bind('click', function(){
		self.deleteTag($(this).parent());
		self.update();
	});
	
	this.deleteTag = function(a){
		$(a).remove();
	}
	
		
	
	this.update = function(){
		part = '';
		part2 = '';
		
		$('div.tags-field-tag-cont span.tags-field-tag', $cont).each(function(){
			part += $(this).html() + ',';
		});
		
		$.each( $text.attr('value').split(','), function(){
			var val = $.trim(this);
			
			if(val.length == 0) return;
			
			part2 += val + ',';
		} );
		
		$hidden.val( part + '|sep|' + part2 );
	}

	this.reset = function(){
		$('div.tags-field-tag-cont', $cont).remove();
		$hidden.val('|sep|');
	}

	$text.bind('blur', function(){ self.update() } );
}