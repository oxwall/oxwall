MOBILE = {};

MOBILE.Navigation = (function() {
   
    var _panels = {}, _settings = {};
    var _bind, _query, _queryCallBack, _newCounter = 0;
    
   // System functions
   
    _bind= function( fnc, obj, args ) {
        fnc = fnc || function(){};
        obj = obj || window;
        args = args || [];

        return function() {
            return fnc.apply(obj, args.concat(Array.prototype.slice.call(arguments)));
        };
    };
    
    _query = function( command, params, callBack ) {
        $.post(_settings.rsp, {
                "command": command,
                "data": JSON.stringify(params),
                "shared": JSON.stringify(_settings.shared)
            }, function( r ) {
                if ( $.isFunction(callBack) ) {
                    callBack(r);
                }

                _queryCallBack(r);
            }, "json");
    };
    
    _queryCallBack = function( r ) {
        if ( !r ) return;
        
        $.each(r.items || {}, function(itemKey, itemData) {
            changeItem(itemKey, itemData);
        });
    };
    

    var init, savePanels, changeItem;
    var Panel, DefaultDelegate, NewDelegate, HiddenDelegate;

    Panel = function( panelKey, delegate ) {
        
        this.key = panelKey;
        this.node = $(".dnd-section[data-key=\"" + this.key + "\"]");
        this.alias = this.node.data("alias");
        
        this.delegate = delegate || new DefaultDelegate();

        this.options = {
            cancel: '.ow_dnd_freezed',
            items: '.component:not(.ow_dnd_freezed)',
            placeholder: 'ow_dnd_placeholder',
            clone: "original",
            
            start: _bind(this.delegate.start, this.delegate, [this]),
            sort: _bind(this.delegate.sort, this.delegate, [this]),
            stop: _bind(this.delegate.stop, this.delegate, [this]),
            beforeStop: _bind(this.delegate.beforeStop, this.delegate, [this]),
            receive: _bind(this.delegate.receive, this.delegate, [this]),
            over: _bind(this.delegate.over, this.delegate, [this]),
            change: _bind(this.delegate.change, this.delegate, [this]),
            update: _bind(this.delegate.update, this.delegate, [this]),
            helper: _bind(this.delegate.helper, this.delegate, [this])
            
        };
        
        this.connections = {};

        _bind(this.delegate.beforeInit, this.delegate, [this])();
        this.init();
        _bind(this.delegate.afterInit, this.delegate, [this])();
    };

    Panel.prototype = {
        init: function() {
            var self = this;
            
            this.node.sortable(this.options).disableSelection();
            
            this.node.on("click", ".dnd-control", function() {
                var item = $(this).parents(".component:eq(0)");
                _bind(self.delegate.actionClick, self.delegate, [self, item])( $(this).data("action") );
            });
            
            return this;
        },
                
        connect: function( panel ) {
            this.connections[panel.key] = panel;
            this.node.sortable("option", "connectWith", this._connectedNodes());
            
            return this;
        },
        
        disconnect: function( panel ) {
            if ( !this.connections[panel.key] ) {
                return this;
            }
            
            delete this.connections[panel.key];
            this.node.sortable("option", "connectWith", this._connectedNodes());
            
            return this;
        },
                
        cancel: function() {
            this.node.sortable("cancel");
        },
                
        save: function() {
            var data = this.node.sortable("toArray", {attribute: "data-key"});
            return _bind(this.delegate.save, this.delegate, [this])(data);
        },
                
        _connectedNodes: function() {
            var connections = false;
            
            $.each(this.connections, function(panelKey, panel) {
                connections = connections ? connections.add(panel.node) : panel.node;
            });
            
            return connections;
        }
    };


    // Delegates

    var _delegates = {};

    _delegates.DefaultDelegate = DefaultDelegate = function() {
        this.sender = null;
    };
    
    $.extend(DefaultDelegate.prototype, {
        
        helper: function(panel, e, item) {
            var itemWidth = item.outerWidth();

            if (itemWidth > 160)
            {
                var k = 160 / item.outerWidth();
                var offsetWindow = item.offset();
                var offset = k * (e.pageX - offsetWindow.left);

                panel.node.sortable( 'option', 'cursorAt', {left: offset } );
            }

            return $('<div class="ow_dnd_helper" style="width: 160px; height: 30px"></div>');
        },
        
        start: function( panel, e, ui ) {
            ui.item.show().addClass("dnd-item-moving");
            this.sender = null;
        },
                
        stop: function( panel, e, ui ) {
            ui.item.removeClass("dnd-item-moving");
            savePanels([panel]);
        },
                
        receive: function( panel, e, ui ) {
            savePanels([panel]);
        },
        
        save: function( panel, data ) {
            return data;
        },
        
        actionClick: function( panel, item, action ) {
            
            var editFloatBox;
            
            if ( action === "delete" ) {
                item.appendTo(_panels.hidden.node);
                savePanels([panel, _panels.hidden]);
            }
            
            if ( action === "edit" ) {
                editFloatBox = OW.ajaxFloatBox("ADMIN_CMP_MobileNavigationItemSettings", [item.data("key")], {
                    "title": OW.getLanguageText("mobile", "admin_nav_settings_fb_title"),
                    "scope": {
                        "callBack": _queryCallBack,
                        "floatBox": editFloatBox
                    }
                });
            }
        }
    });
    
    
    _delegates.NewDelegate = NewDelegate = function() {
        DefaultDelegate.call(this);
        
        this.cloning = false;
        this.clone = null;
    };
    $.extend(NewDelegate.prototype, DefaultDelegate.prototype, {
        createItem: function( from ) {
            var item = from.clone();
            item.hide();
            
            return changeItem(item, {
                "key": (_settings.prefix ? _settings.prefix + ':' : '') + "new-item-" + (_newCounter++),
                "title": OW.getLanguageText("mobile", "admin_nav_adding_message"),
                "locked": true,
                "parent": from.data("key")
            });
        },
        
        start: function( panel, e, ui ) {
            this.cloning = true;
            ui.item.show();
            this.clone = this.createItem(ui.item);
        },
                
        update: function( panel, e, ui ) {
            this.cloning = false;
            ui.item.after(this.clone);

            panel.cancel();
            this.clone.show();
            this.clone = null;
        },
                
        stop: function( panel, e, ui ) {

        },
                
        save: function( panel, data ) {
            return null;
        },
                
        actionClick: function( panel, item, action ) {
            // Skip
        }
    });
    
    
    _delegates.HiddenDelegate = HiddenDelegate= function() {
        DefaultDelegate.call(this);
        
    };
    $.extend(HiddenDelegate.prototype, DefaultDelegate.prototype, {
        actionClick: function( panel, item, action ) {
            if ( action !== "delete" ) {
                DefaultDelegate.prototype.actionClick.call(this, panel, item, action);
                
                return;
            }
            
            if ( !item.data("custom") ) {
                return;
            }
            
            if ( confirm("Are you sure?") ) {
                item.remove();
                
                _query("deleteItem", {
                    "key": item.data("key")
                });
            }
        }
    });
    
    
    // Global functions
    
    savePanels = function( panels ) {
        
        var query = {
            "panels": {},
            "items": {}
        };
        
        $.each(panels, function(index, panel) {
            var data = panel.save();
            if ( data ) {
                $.each(data, function(i, itemKey){
                    var item = $(".component[data-key=\"" + itemKey + "\"]");
                    query.items[itemKey] = {
                       "type": item.data("type"),
                       "parent": item.data("parent"),
                       "key": item.data("key")
                    };
                });
                
                query.panels[panel.alias] = data;
            }
        });

        console.log("savePanels", panels);
        _query("saveOrder", query);
    };
    
    changeItem = function( key, options ) {
        var item;
        
        if ( $.type(key) === "string" ) {
            item = $(".component[data-key=\"" + key + "\"]");
        } else {
           item = key; 
        }

        if ( options.key ) {
            item.attr("data-key", options.key);
            item.data("key", options.key);
        }
        
        if ( options.type ) {
            item.attr("data-type", options.type);
        }
        
        if ( options.parent ) {
            item.attr("data-parent", options.parent);
        }
        
        if ( options.title ) {
            item.find(".dnd-title").text(options.title);
        }
        
        if ( options.locked ) {
            item.addClass("dnd-item-locked");
        } else {
            item.removeClass("dnd-item-locked");
        }
        
        if ( options.custom !== undefined ) {
            item.attr("data-custom", options.custom);
            item[options.custom ? "addClass" : "removeClass"]("dnd-item-custom");
        }
        
        return item;
    };
    
    init = function( settings ) {
        _settings = settings;
        var sections = $(".dnd-section");
        
        sections.each(function() {
           var s =  $(this);
           _panels[s.data("key")] = new Panel(s.data("key"), new _delegates[s.data("delegate")]());
        });
        
        sections.each(function() {
           var s =  $(this);
           var relation = s.data("relation");
           
           if ( !relation ) return;
           
           $.each(relation.split(','), function(i, rel) {
               _panels[s.data("key")].connect(_panels[$.trim(rel)]);
           });
        });
    };


    // Public methods
    return {
        init: init
    };
})();