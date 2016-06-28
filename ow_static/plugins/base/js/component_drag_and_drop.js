componentDragAndDrop = function( prototypeObject ) {

	for ( var prop in prototypeObject ) {

		if ( this[prop] !== undefined ) {
			this['parent_' + prop] = this[prop];
		}

		this[prop] = prototypeObject[prop];
	}

}


componentDragAndDrop.prototype = {

	prepare: function()
        {
            OW.WidgetPanel = this;
		var self = this;

		this.sectionSelector = '.place_section';
		this.$panel = $('#place_components');
		this.$sections = $(this.sectionSelector);
	    this.$clonablePanel = $('#clonable_components');
	    this.transfered = false;

		this.defaultSettings = {

		    placeholder: 'hidden-placeholder',
		    connectWith: this.sectionSelector,

		    helper: function(e, ui) {
				return self.getHelper(this, e, ui);
			},

			start: function(e, ui) {
	            return self.handlStart(this, e, ui);
			},

	        stop: function(e, ui) {
	            self.handleStop(this, e, ui);
	            self.complete();
	        },

	        beforeStop: function(e, ui) {
	        	if (ui.item.parent().hasClass('ow_dnd_locked_section'))
	        	{
		        	$(this).sortable('cancel');
		        	self.handleStop(this, e, ui);
	        	}
            }
		};

		this.events = {
		        complete: [],
		        update: [],
		        clone: [],
		        remove: [],
		        loadSettings: [],
		        saveSettings: [],
		        saveScheme: [],
		        moveToPanel: [],
		        reload: [],
		        cloneComplete: [],
                        move: []
		};
	},

	initialize: function() {
		this.initializePanel();
        this.initializeSections();
        this.initializeActions();
        this.initializeClonablePnael();
        this.initializeScheme();
	},

	setHandler: function(handler) {

	    this.bind('update', function(section, state) {
	        handler.changeState(section, state);
	    });

	    this.bind('complete', function(successFunction) {
	        handler.complete(successFunction);
	    });

	    this.bind('clone', function(section, stack, id, success) {
	        handler.clone(section, stack, id, success);
	    });

	    this.bind('remove', function(id) {
	        handler.remove(id);
	    });

	    this.bind('loadSettings', function(id, successFunction) {
	        handler.loadSettings(id, successFunction);
	    });

	    this.bind('saveSettings', function(id, settings, successFunction) {
	        handler.saveSettings(id, settings, successFunction);
	    });

	    this.bind('saveScheme', function(scheme) {
	        handler.saveScheme(scheme);
	    });

	    this.bind('moveToPanel', function(cmpId) {
	        handler.moveToPanel(cmpId);
	    });

            this.bind('reload', function(cmpId, renderView, successFunction)
            {
                handler.reload(cmpId, renderView, successFunction);
            });
	},

	initializePanel: function() {

        var self = this;
        var panelSettings = this.extendSettings({
            stop: function(e, ui) {
                if ( !self.transfered ) {
                    $(this).sortable('cancel');
                    self.handleStop(this, e, ui);
                    return;
                }
                self.handleStop(this, e, ui);
                self.complete();
                self.actionComplete();
            },

            over: function(e, ui) {
                ui.placeholder.removeClass('ow_dnd_placeholder');
                ui.placeholder.addClass('placeholder-hidden');
            }
        });

        this.$panel.sortable(panelSettings).disableSelection();
    },

    initializeSections: function() {
        var self = this;
        var sectionSettings = this.extendSettings({
            cancel: '.ow_dnd_freezed',
            items: '.component:not(.ow_dnd_freezed)',
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
                if (ui.item.cloning && self.transfered) {
                    ui.item.clone = self.clone(this, ui.item);
                } else {
                    self.update(this);
                }

            }

        });

        this.$sections.sortable(sectionSettings).disableSelection();
    },


    initializeClonablePnael: function() {
        var self = this;
        var settings = this.extendSettings({

            stop: function(e, ui) {
                if (self.transfered) {
                    self.handleClone(this, e, ui);
                    ui.item.cloning = false;
                    self.complete();
                }

                $(this).sortable('cancel');
                //self.handleStop(this, e, ui);
                self.actionComplete();
            },

            start: function(e, ui){
                 self.handlStart(this, e, ui);
                 $(ui.item).css('opacity', '1');
                 ui.item.cloning = true;
            },

            over: function(e, ui) {
                ui.placeholder.removeClass('ow_dnd_placeholder');
                ui.placeholder.addClass('placeholder-hidden');
            }

        });

        this.$clonablePanel.sortable(settings).disableSelection();
    },


    initializeActions: function() {
        var self = this;

        var $allContainers = this.$sections.add(this.$panel);

        $allContainers.find('.component').hover(function(){
            $(this).find('.action').show();
        }, function(){
            $(this).find('.action').hide();
        });

        this.$sections.find('.dd_delete').unbind().click(function() {
           var $component = $(this).parents('.component:eq(0)');
           self.deleteFromSection($component);
        });

        this.$panel.find('.dd_edit').unbind().click(function() {
           var $component = $(this).parents('.component:eq(0)');

           self.showSettings($component);

           return false;
        });

        this.$sections.find('.dd_edit').unbind().click(function() {
           var $component = $(this).parents('.component:eq(0)');

           self.showSettings($component);

           return false;
        });

        this.$panel.find('.dd_delete').unbind().click(function() {
           if ( !confirm( OW.getLanguageText('base', 'widgets_delete_component_confirm') ) ) {
               return false;
           }

           var $component = $(this).parents('.component:eq(0)');
           if (!$component.hasClass('clone')) {
               return false;
           }
           self.deleteFromPanel($component.attr('id'));
           self.handleRemoveFromPanel($component);
        });
    },

    initializeScheme: function() {
    	var $sliderNode = $('.ow_dnd_slider');
    	if (!$sliderNode.length) {
    		return;
    	}
        var self = this;
    	var $left = $('.left_section', '#place_sections');
        var $right = $('.right_section', '#place_sections');

        $sliderNode.schemeSwitcher({
    		change: function(event){

	    		$left.removeClass( $(event.lastMarker).attr('dd_leftclass') )
	    			.addClass( $(event.marker).attr('dd_leftclass') );
	    		$right.removeClass( $(event.lastMarker).attr('dd_rightclass') )
				.addClass( $(event.marker).attr('dd_rightclass') );

	    	},

	    	update: function(event){
	    		var schemeId = $(event.marker).attr('ow_scheme');
	    		self.changeScheme(schemeId);
	    	}
	    });
    },

    changeScheme: function( schemeId ) {
    	this.trigger('saveScheme', [schemeId]);
    },

    actionComplete: function() {
    	this.enableSections($('.place_section', '#place_sections '));
        this.initializeActions();
    },

    /* Animation */

    getHelper: function(sortable, e, ui) {
        var itemWidth = ui.outerWidth();

        if (itemWidth > 160)
        {
            var k = 160 / ui.outerWidth();
            var offsetWindow = ui.offset();
            var offset = k * (e.pageX - offsetWindow.left);

            $(sortable).sortable( 'option', 'cursorAt', {left: offset } );
        }

        return $('<div class="ow_dnd_helper" style="width: 160px; height: 30px"></div>');
    },

    handlStart: function(sortable, e, ui) {
        this.transfered = false;
        $(ui.item).show().css('opacity', '0.3');

        var within = $(ui.item).attr('ow_avaliable_sections');
        if (typeof within == 'undefined') {
        	return;
        }

        var withinItmes = within.split(',');
        var lockSelectors = [];

        $.each(withinItmes, function (i, item){
        	var str = '.' + item + '_section';
        	lockSelectors.push(str);
        });
        var selector = lockSelectors.join(',');

        this.disableSections($('.place_section:not(' + selector + ')', '#place_sections '));
    },

    handleStop: function(sortable, e, ui)
    {
        var $item = $(ui.item);
    	this.enableSections($('.place_section', '#place_sections '));
        $item.show().css('opacity', '1');

        var event = {};
        var toSection = $item.parents(this.sectionSelector);
        var fromSection = $(sortable);

        event.widgetName = $item.attr('id');
        event.widget = $item;
        event.to = {
            section: toSection,
            sectionName: toSection.attr('ow_place_section')
        };
        event.from = {
            section: fromSection,
            sectionName: fromSection.attr('ow_place_section')
        };
        event.ui = ui;
        event.originalEvent = e;
        
        this.trigger('move', [event]);
    },

    handleClone: function(sortable, e, ui) {
        ui.item.after(ui.item.clone);
        $(ui.item.clone).show().css('opacity', '1');
        $(sortable).sortable('cancel');
        this.handleStop(sortable, e, ui);
    },

    handleSectionChange: function(sortable, e, ui)
    {
        var id = $(ui.item).attr('id');
        var $ph = $(ui.placeholder);
        var cmpNode = document.getElementById(id);

        if ( $ph.next().is(cmpNode) || $ph.prev().is(cmpNode)	) {
                $ph.removeClass('ow_dnd_placeholder');
                $ph.addClass('hidden-placeholder');
        }
        else {
                $ph.removeClass('hidden-placeholder');
                $ph.addClass('ow_dnd_placeholder');
        }
    },

    handleRemoveFromSection: function($item, completeFnc) {
        var self = this;
        $item.find('.action').hide();
        $item.removeClass('ow_dnd_freezed');
        $item.fadeOut('fast', function(){
            $item.appendTo(self.$panel);
            self.actionComplete();
            $item.fadeIn('fast', completeFnc);
        });

    },

    handleRemoveFromPanel: function($item) {
        $item.fadeOut('fast');
    },

    disableSections: function($sections) {
    	$.each($sections, function(i, item) {
    		$(item).addClass('ow_dnd_locked_section');
    	});
    },

    enableSections: function($sections) {
    	$.each($sections, function(i, item){
    		$(item).removeClass('ow_dnd_locked_section');
    	});
    },

    /* / Animation */

    deleteFromSection: function($item) {
         var self = this;
         var sortable = $item.parents(this.sectionSelector).get(0);
         this.handleRemoveFromSection($item, function(){
            self.update(sortable);
            self.trigger('moveToPanel', [$item.attr('id')]);
            self.complete();
         });

    },

    clone: function(sortable, $cloningItem) {

        var self = this;
        var sourceId = $cloningItem.attr('id');
        var stack = $(sortable).sortable('toArray');
        var section = $(sortable).attr('ow_place_section');
        var $destItem = $cloningItem.clone().removeAttr('id').addClass('clone');

        self.trigger('clone', [section, stack, sourceId, function(id){
            $destItem.attr('id', id);
            self.trigger('cloneComplete', [id]);
        }]);
        return $destItem;
    },

    deleteFromPanel: function(id) {
        this.trigger('remove', [id]);
    },

    update: function(sortable) {

        var $selfNode = $(sortable);
        var section = $selfNode.attr('ow_place_section');
        var stack = [];
        $selfNode.find('.component').each(function(){
        	stack.push(this.id);
        });

        this.changeState(section, stack);
    },

	changeState: function(sectionName, itemStack) {
        this.trigger('update', [
            sectionName,
            itemStack
        ]);
	},

	complete: function(successFunction) {
            this.trigger('complete', [successFunction]);
	},

    showSettings: function($component)
    {
        var self = this;
        var $title = $('.settings_title', '#fb_settings');
        var $content = $('.settings_content', '#fb_settings').addClass('ow_preloader_content');

        var $controls = $('.settings_controls', '#fb_settings');
        $controls.find('input.dd_save').unbind();

        this.settingBox = new OW_FloatBox({
            $title: $title,
            $contents: $content,
            $controls: $controls,
            width: 500
        });

        this.settingBox.bind('close', function(){
            $content.empty();
        });
        var cmpId = $component.attr('id');
        this.trigger('loadSettings', [cmpId, function(settingMarkup){
                $content.empty();
                $content.removeClass('ow_preloader_content');
                var $settingMarkup = $('<form class="settings_form">' + settingMarkup.content + '</form>');
                $content.html($settingMarkup);

                if (settingMarkup.styleSheets) {
                    $.each(settingMarkup.styleSheets, function(i, o){
                        OW.addCssFile(o);
                    });
                }

                if (settingMarkup.styleDeclarations) {
                    OW.addCss(settingMarkup.styleDeclarations)
                }

                if (settingMarkup.scriptFiles) {
                    OW.addScriptFiles(settingMarkup.scriptFiles, function(){
                        if (settingMarkup.onloadScript) {
                            OW.addScript(settingMarkup.onloadScript);
                        }
                    });
                }
                else
                {
                    if (settingMarkup.onloadScript) {
                        OW.addScript(settingMarkup.onloadScript);
                    }
                }

                var $submitControl = $controls.show().find('input.dd_save');
                var $form = $content.find('.settings_form').submit(function(){
                    self.saveSettings(cmpId, this, $submitControl.get(0));
                    return false;
                });

                $submitControl.click(function(){
                    $form.submit();
                });

        }]);
    },

    saveSettings: function(cmpId, form, submitControl) {
        var self = this;
        var formState = this.formToArray(form);
        var renderComponent = this.isComponentRenderable(cmpId);
        OW.inProgressNode(submitControl);
        $(form).find('.setting_error').empty();
        var error = function(message, fieldName) {
            if (fieldName) {
                $('#error_' + fieldName ).html(message);
            } else {
                OW.error(message);
            }
        };

        this.trigger('saveSettings', [cmpId, formState, function( result, sharedData ) {
            if ( result.error ) {
                error(result.error.message, result.error.field);
                sharedData.stop = true;

                return;
            }
            self.applyComponentSettings(cmpId, result.settingList);
        }]);

        this.trigger('reload', [cmpId, renderComponent, function( markup, sharedData ){
            if (markup && !sharedData.stop) {
                self.drawComponent(cmpId, markup);
            }
        }]);

        this.complete(function(r, sharedData) {
            OW.activateNode(submitControl);
            
            if ( !sharedData.stop ) {
                self.settingBox.close();
            }
        });
    },

    reloadWidget: function(cmpId, callBack, render)
    {
        var self = this;

        render = render === false ? false : true;

        this.trigger('reload', [cmpId, render, function( markup, sharedData )
        {
            var callBackResult = true;

            if ($.isFunction(callBack))
            {
                callBackResult = callBack.call(this, markup, sharedData);
            }

            if (callBackResult !== false && markup)
            {
                self.drawComponent(cmpId, markup);
            }
        }]);

        this.trigger('complete');
    },

    drawComponent: function(cmpId, markup) {
    	var $component = $(document.getElementById(cmpId));
        var $newComponent = $(markup.content);
        try {
            $component.replaceWith($newComponent);
        } catch(e) {}

        if (markup.styleSheets) {
           	$.each(markup.styleSheets, function(i, o){
           		OW.addCssFile(o);
        	});
        }

        if (markup.styleDeclarations) {
        	OW.addCss(markup.styleDeclarations)
        }

        if (markup.scriptFiles) {
            OW.addScriptFiles(markup.scriptFiles, function(){
                if (markup.onloadScript) {
                        OW.addScript(markup.onloadScript);
                }
            });
        }
        else
        {
            if (markup.onloadScript) {
                OW.addScript(markup.onloadScript);
            }
        }

        this.actionComplete();
    },

    applyComponentSettings: function(cmpId, settings) {

        var $component = $(document.getElementById(cmpId));
        var $section = $component.parents('.place_section:eq(0)');

        if (settings.freeze > 0) {
            $component.addClass('ow_dnd_freezed');
            this.arrangeFreezed($section);
        }
    },

    arrangeFreezed: function(section) {
    	var $section = $(section);

    	/*var components = $section.find('.component').sort(function(a, b){
    		var x = $(a).is('.ow_dnd_freezed') ? 0 : 1;
    		var y = $(b).is('.ow_dnd_freezed') ? 0 : 1;

    		return x - y;
    	});*/
    	var iteration = function(){
    		$section.append(this);
    	};
    	var components = [];
    	$section.find('.ow_dnd_freezed').each(iteration);
    	$section.find('.component:not(.ow_dnd_freezed)').each(iteration);


    	/*$.each(components, function(){
    		$section.append(this);
    	});*/
    },

    isComponentInSection: function(id) {
    	return $(document.getElementById(id)).is('#place_sections .component');
    },

    isComponentRenderable: function(id) {
    	return false;
    },

	formToArray: function(form) {

            var nameRegex = /^([\w\-]+)(\[([^\]]+)?\])?/i;

	    var state = {};
	    $.each(form.elements, function(i, item){
            if ( !$.trim(item.name).length ) return;

	        var $item = $(item);
                var beforeValue = $item.attr('beforevalue');
                var nameParts = nameRegex.exec(item.name);
                var name = nameParts[1];
                var isMultiple = !!nameParts[2];
                var arrayKey = !!nameParts[3];
                var value;

                if ($item.is('input:checkbox, input:radio')) {
                    value = $item.attr('checked') ? true : false;
                } else {
                    value = $item.val();
                }

                if (  beforeValue && beforeValue == value )
                {
                    return;
                }

                if ( isMultiple ) {
                    state[name] = state[name] || (arrayKey ? {} : []);

                    if ( arrayKey ) {
                        state[name][arrayKey] = value;
                    }
                    else if (value) {
                        state[name].push($item.attr('value') || true);
                    }
                }
                else {
                    state[name] = value;
                }

	    });

	    return state;
	},

	bind: function(type, func) {
		if (this.events[type] == undefined) {
			throw 'undefined form event type "'+type+'"';
		}

		this.events[type].push(func);
	},

	unbind: function(type) {
		if (this.events[type] == undefined) {
			throw 'undefined form event type "'+type+'"';
		}

		this.events[type] = [];
	},

	trigger: function(type, params) {

		if (this.events[type] == undefined) {
			throw 'undefined form event type "'+type+'"';
		}

		params = params || [];

		for (var i = 0, func; func = this.events[type][i]; i++) {
			if (func.apply(this, params) === false) {
				return false;
			}
		}

		return true;
	},

	extendSettings: function(obj) {
		$.each(this.defaultSettings, function(prop, value) {
		    if (obj[prop] === undefined) {
		        obj[prop] = value;
		    }
		});
		return obj;
	}

}
