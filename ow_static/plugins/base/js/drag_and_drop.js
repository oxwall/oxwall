OW_Components_DragAndDrop = function() {

    var self = this;

    this.prepare();
    this.defaultSettings.handle = '.dd_handle';
    this.initialize();

    $(this.sectionSelector).each(function(){
    	DND_InterfaceFix.fix(this);
    });


    this.events.reset = [];

    $('#reset_position_btn').click(function(){
    	if ( confirm( OW.getLanguageText('base', 'widgets_reset_position_confirm') ) ) {
    		self.resetCustomization();
        }
    });

};

OW_Components_DragAndDrop.prototype = new componentDragAndDrop ({

	setHandler: function(handler) {
		this.bind('reset', function(successFnc){
			handler.reset(successFnc);
		})

		this.parent_setHandler(handler);
	},

	/* / Animation */

	handleRemoveFromSection: function($item, completeFnc) {
		var self = this;

		$item.find('.action').hide();
		$item.fadeOut('fast', function(){
		    $item.appendTo(self.$panel);
		    $item.find('.view_component').hide();
		    $item.find('.schem_component').show();

		    self.actionComplete();
		    $item.fadeIn('fast', completeFnc);
		});
	},

	clone: function(sortable, $cloningItem) {
        var self = this;
        var sourceId = $cloningItem.attr('id');

        var stack = [];
        $(sortable).find('.component').each(function(){
        	stack.push(this.id);
        });

        var section = $(sortable).attr('ow_place_section');
        var $destItem = $('<div class="ow_dnd_preloader ow_preloader_content ow_stdmargin"></div>');

        self.trigger('clone', [section, stack, sourceId, function(id){
            $destItem.attr('id', id);
            self.trigger('cloneComplete', [id]);
        }]);
        return $destItem;
    },

    handleStop: function(sortable, e, ui) {
    	if ( ! this.transfered ) {
            this.parent_handleStop( sortable, e, ui );
            return;
    	}
        
        var self = this;
    	var $item = $(ui.item);
    	var cloning = ui.item.cloning;
    	var $preloader = $('<div class="ow_dnd_preloader ow_preloader_content ow_stdmargin"></div>');
    	var $view = $item.find('.view_component');

    	if ( $view.length ) {
    		$view.show();
                $item.find('.schem_component').hide();

                DND_InterfaceFix.fix($item);
                this.parent_handleStop(sortable, e, ui);

                return;
    	}

    	var reloadFnc = function( Id ) {
    		self.trigger('reload', [Id, true, function( markup ){
            	if (markup) {
                    self.drawComponent(Id, markup);
                    $preloader.remove();
                }
            }]);

    		self.complete();
    	};

    	if ( cloning ) {
    		this.unbind('cloneComplete');
    		this.bind('cloneComplete', function( newKey ){
    			$preloader.remove();
    			reloadFnc( newKey );
    		});
    		return;
    	}

    	$item.hide().after( $preloader );
    	reloadFnc( $item.attr('id') );
    },

    changeScheme: function(schemeId) {
    	var $left = $('.left_section', '#place_sections');
        var $right = $('.right_section', '#place_sections');

        DND_InterfaceFix.fix($left);
        DND_InterfaceFix.fix($right);

        this.parent_changeScheme(schemeId);
    },

    isComponentRenderable: function(id) {
    	return this.isComponentInSection(id);
    },

    drawComponent: function(cmpId, markup) {
    	this.parent_drawComponent(cmpId, markup);

    	DND_InterfaceFix.fix($(document.getElementById(cmpId)));
    },

    applyComponentSettings: function(id, settings) {

    },

    redrawEmbeds: function( containerNode ) {
    	var configureEmbed = function($embed) {
    		$embed.attr('width', '100%')
    			.attr('wmode', 'transparent');
    	};

    	var configureObject = function($object) {
    		$object.attr('width', '100%');
    	};

    	$('embed', containerNode).each(function(){
    		var $clone = $(this).clone();
    		configureEmbed($clone);
    		$(this).replaceWith($clone);
    	});

    	$('object', containerNode).each(function(){
    		var $clone = $(this).clone();
    		configureObject($clone);
    		configureEmbed($clone.find('embed'));
    		$(this).replaceWith($clone);
    	});
    },

    resetCustomization: function() {
    	this.trigger('reset', [function(){
    		window.location.reload();
    	}]);
    }


});
