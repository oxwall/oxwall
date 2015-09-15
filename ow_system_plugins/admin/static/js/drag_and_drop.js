OW_Components_DragAndDrop = function() {
	var self = this;
	
	this.prepare();
	this.defaultSettings.handle = '.dd_handle';
    this.initialize();
    
    this.events.allowCustomize = [];
    
    $('#allow_customize_btn').click(function(){
    	self.trigger('allowCustomize', [this.checked]);
    });
};

OW_Components_DragAndDrop.prototype = new componentDragAndDrop ({
	
	initializeSections: function() {
	    var self = this;
	    var sectionSettings = this.extendSettings({
	        items: '.component',
	        receive: function(e, ui){
	            if (ui.placeholder.hasClass('ow_dnd_placeholder')) {
	                self.transfered = true;
	            }
	        },
	
	        over: function(e, ui){
		        ui.placeholder.removeClass('placeholder-hidden');
		        ui.placeholder.addClass('ow_dnd_placeholder');
		    },
	        
	        change: function(e, ui) {
	           self.handleSectionChange(this, e, ui);
	        },
	
	        update: function(e, ui) {
	        	self.arrangeFreezed(this);
	            if (ui.item.cloning && self.transfered) {
	                ui.item.clone = self.clone(this, ui.item);
	            } else {
	                self.update(this);
	            }
	        }
	
	    });
	    
	    this.$sections.sortable(sectionSettings).disableSelection();
	},
	
	setHandler: function(handler) {
		
		this.bind('allowCustomize', function(state, callbackFnc){
                    handler.allowCustomize(state, callbackFnc);
		});
	
		this.parent_setHandler(handler);
	}
});
