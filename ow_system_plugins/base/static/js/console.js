OW_DataModel = function( data )
{
    this.data = data || {};
    this.observers = [];
};

OW_DataModel.PROTO = function()
{
    var isEqual = function( x, y )
    {
        if ( x === y ) return true;
        if ( ! ( x instanceof Object ) || ! ( y instanceof Object ) ) return false;
        if ( x.constructor !== y.constructor ) return false;

        for ( var p in x )
        {
            if ( ! x.hasOwnProperty( p ) ) continue;
            if ( ! y.hasOwnProperty( p ) ) return false;
            if ( x[ p ] === y[ p ] ) continue;
            if ( typeof( x[ p ] ) !== "object" ) return false;
            if ( !isEqual( x[ p ],  y[ p ] ) ) return false;
        }

        for ( p in y )
        {
            if ( y.hasOwnProperty( p ) && ! x.hasOwnProperty( p ) ) return false;
        }

        return true;
    };

    this.get = function( key )
    {
        if ( !key )
        {
            return this.data;
        }

        var dataBranch = this.data, dataPath, out = null;

        dataPath = key.split('.');

        for ( var i = 0; i < dataPath.length; i++ )
        {
            if ( dataPath[i] in dataBranch )
            {
                out = dataBranch[dataPath[i]];
                dataBranch = dataBranch[dataPath[i]];
            }
        }

        return out;
    };

    this.set = function( key, value, notifyObservers )
    {
        var self = this;
        var notify = notifyObservers || true;
        var changed = false;

        if ( value === undefined && key )
        {
            changed = changed || !isEqual(this.data, key);
            this.data = key;
        }
        else
        {
            var i = 0, dataBranch = this.data, dataPath;

            dataPath = key ? key.split('.') : [];

            for ( i = 0; i < dataPath.length - 1; i++ )
            {
                if ( !$.isPlainObject(dataBranch[dataPath[i]]) )
                {
                    dataBranch[dataPath[i]] = {};
                }

                dataBranch = dataBranch[dataPath[i]];
            }

            changed = changed || !isEqual(dataBranch[dataPath[i]], value);
            dataBranch[dataPath[i]] = value;
        }

        if ( notify && changed )
        {
            window.setTimeout(function()
            {
                self.notifyObservers();
            });
        }
    };

    this.addObserver = function( observer, method )
    {
        method = method || 'onDataChange';

        var _observer, self = this;

        if ( $.isFunction(observer) )
        {
            _observer = function() {
                observer.call(self, self);
            };
        }
        else
        {
            _observer = function() {
                observer[method].call(observer, self);
            };
        }

        this.observers.push(_observer);
    };

    this.onChange = function(){};

    this.notifyObservers = function()
    {
        this.onChange(this);

        $.each(this.observers, function(i, obs)
        {
            obs.call();
        });
    };
};

OW_DataModel.prototype = new OW_DataModel.PROTO();


OW_Console = function( params, modelsData )
{
    modelsData = modelsData || {};
    params = params || {};

    var self = this;
    var _models = {};

    this.items = {};

    this.addItem = function( item, key )
    {
        key = key || item.uniqId;
        this.items[key] = item;
    };

    this.getItem = function( key )
    {
        return this.items[key];
    };

    this.getData = function( key )
    {
        _models[key] = _models[key] || new OW_DataModel();

        return _models[key];
    };

    this.hideOtherContents = function( item )
    {
        $.each(this.items, function(i, o)
        {
            if ( item != o )
            {
                o.hideContent();
            }
        });
    };

    this.hideAllContents = function()
    {
        $.each(this.items, function(i, o)
        {
            o.hideContent();
        });
    };

    $.each( modelsData, function (key, data)
    {
        self.getData(key).set(data);
    });

    var command = OW.getPing().addCommand('consoleUpdate',
    {
        before: function()
        {
            var c = this;

            $.each( _models, function (key, model)
            {
                c.params[key] = model.get();
            });
        },
        after: function( dataList )
        {
            $.each( dataList, function (key, data)
            {
                self.getData(key).set(data);
            });

        }
    });

    window.setTimeout(function()
    {
        command.start(params.pingInterval);
    }, params.pingInterval);
};










OW_ConsoleItem =
{
    animate: false,
    opened: false,

    init: function( uniqId, contentUniqId )
    {
        var self = this;

        this.uniqId = uniqId;
        this.contentUniqId = contentUniqId;

        this.$node = $('#' + this.uniqId);
        this.$contentNode = $('#' + this.contentUniqId);
        this._$tooltip = this.$contentNode.find('.console_tooltip');

        this.observers = [];
        this.observers.notify = function(method)
        {
            for ( var i = 0; i < this.length; i++ )
            {
                if ( this[i][method] )
                {
                    this[i][method].call(this[i], self);
                }
            }
        };
    },

    getKey: function()
    {
        return this.uniqId;
    },

    showItem: function()
    {
        this.$node.show();
    },

    hideItem: function()
    {
        if ( this.opened )
        {
            this.hideContent();
        }

        this.$node.hide();
    },

    showContent: function()
    {
        var self = this;

        OW.Console.hideOtherContents(this);

        if ( this.opened )
        {
            return;
        }

        this.$contentNode.show();

        this.animate = true;
        this._$tooltip.css({opacity: 0, top: this.$node.height() - 12}).stop(true, true).animate({top: this.$node.height(), opacity: 1}, 'fast', function()
        {
            self.animate = false;
            OW.addScrolls(self.$contentNode);
            self.observers.notify('afterShow');
        });

        this.opened = true;
        this.observers.notify('beforeShow');


        this.onShow();
        this._onShow();
    },

    hideContent: function()
    {
        var self = this;

        if ( !this.opened )
        {
            return;
        }

        this.animate = true;

        this.observers.notify('beforeHide');
        this._$tooltip.stop(true, true).animate({top: this.$node.height() - 12, opacity: 0}, 'fast', function()
        {
            self.animate = false;
            self.observers.notify('afterHide');

            self.onHide();
            self._onHide();

            self.$contentNode.hide();
        });

        this.opened = false;
    },

    //TODO replace this with bind
    onShow: function() {},
    _onShow: function() {},
    onHide: function() {},
    _onHide: function() {},

    addObserver: function( observer )
    {
        this.observers.push(observer);
    }
};




OW_ConsoleDropdownHover = function( uniqId, contentUniqId )
{
    this.init( uniqId, contentUniqId );

    var self = this;
    var hideTimeout;

    this.$node.hover(function()
    {
        if (self.animate)
        {
            return false;
        }

        if ( hideTimeout )
        {
            window.clearTimeout(hideTimeout);
        }

        self.showContent();
    },
    function()
    {
        hideTimeout = window.setTimeout(function()
        {
            self.hideContent();
        }, 300);
    });
}
OW_ConsoleDropdownHover.prototype = OW_ConsoleItem;


OW_ConsoleDropdownClick = function( uniqId, contentUniqId )
{
    this.init( uniqId, contentUniqId );

    this.initDropdown();
}
$.extend(OW_ConsoleDropdownClick.prototype, OW_ConsoleItem,
{
    initDropdown: function()
    {
        var self = this;

        this.addObserver({
           beforeShow: function()
           {
               self.$node.addClass('ow_console_dropdown_pressed');
           },

           beforeHide: function()
           {
               self.$node.removeClass('ow_console_dropdown_pressed');
           }
        });

        $(document).click(function( e )
        {
            if ( !$(e.target).is(':visible') )
            {
                return;
            }

            var isContent = self.$contentNode.find(e.target).length;
            var isTarget = self.$node.is(e.target) || self.$node.find(e.target).length;

            if ( isTarget && !isContent )
            {
                if ( self.opened )
                {
                    self.hideContent();

                }
                else
                {
                    self.showContent();

                }
            }
            else if ( !isContent )
            {
                self.hideContent();
            }
        });
    }
});




OW_ConsoleDropdownList = function( uniqId, contentUniqId )
{
    this.init( uniqId, contentUniqId );
    this.initDropdown();

    var $counter = $('.OW_ConsoleItemCounter', this.$node),
        $number = $counter.find('.OW_ConsoleItemCounterNumber'),
        $place = $counter.find('.OW_ConsoleItemCounterPlace');

    var shown = false,
        currentNumber = null;

    var numberSetActive = function( active )
    {
        active = active === false ? false : true;
        $place[ active ? 'addClass' : 'removeClass' ]('ow_count_active');
    }

    var numberShow = function( number, animate )
    {
        animate = animate === false ? false : true;

        var placeWidth = $place.width();

        $number.text(number);
        $number.css({visibility: "visible"});
        currentNumber = number;

        var numberWidth = $number.outerWidth();
        $place.animate({width: numberWidth}, 'fast');

        if ( animate )
        {
            $number.css({right: -placeWidth}).animate({right: 0}, 'fast');
        }
    };

    var numberHide = function( callBack )
    {
        var placeWidth = $place.width();

        $number.animate({right: placeWidth}, 'fast', callBack);
    };

    var counterShow = function( callBack )
    {
        var placeWidth;

        $counter.show();
        placeWidth = $place.width();
        $place.css({width: 2}).animate({width: placeWidth}, 'fast', callBack);

        shown = true;
    };

    var counterHide = function( callBack )
    {
        numberHide(function()
        {
            $place.animate({width: 0}, 'fast', function()
            {
                $number.text(0);
                $place.css({width: 'auto'});
                $counter.hide();
                if ( $.isFunction(callBack) ) callBack.apply($counter.get(0));
            });
        });

        shown = false;
    };

    // Public Methods

    this.setCounter = function( number, active )
    {
        var intNumber = parseInt(number);
        intNumber = isNaN(intNumber) ? 0 : intNumber;

        if ( intNumber <= 0 )
        {
            counterHide();

            return;
        }

        if ( !shown )
        {
            counterShow(function()
            {
                numberShow(number);
                numberSetActive(active);
            });
        }
        else if ( number == currentNumber )
        {
            numberSetActive(active);
        }
        else
        {
            numberHide(function()
            {
                numberShow(number);
                numberSetActive(active);
            });
        }
    };
}
OW_ConsoleDropdownList.prototype = OW_ConsoleDropdownClick.prototype;



OW_ConsoleList =
{
    construct: function( params )
    {
        var self = this;

        this.rsp = params.rsp;
        this.key = params.key;
        this.data = OW.Console.getData(this.key);
        this.item = OW.Console.getItem(this.key);

        this.$container = function() {
            return $('.OW_ConsoleListContainer', self.$contentNode);
        };

        this.$list = function() {
            return $('.OW_ConsoleList', self.$container());
        };

        this.$preloader = function() {
            return $('.OW_ConsoleListPreloader', self.$container());
        };

        this.$noContent = function() {
            return $('.OW_ConsoleListNoContent', self.$container());
        };

        this.isListFull = false;
        this.isListLoading = false;
        this.isListLoaded = false;

        this.item.onShow = function()
        {
            this.loadList();
        };

        this.item._onShow = function()
        {
            if ( this.isListLoaded )
            {
                this.updateScroll(true);
            }
        };

        this.item._onHide = function()
        {
            OW.removeScroll(this.$container().get(0));
        };
    },

    clearList: function()
    {
        OW.removeScroll(this.$container().get(0));

        this.$list().empty();
        this.setIsListFull(false);
    },

    loadList: function()
    {
        this.clearList();
        this.showPreloader();

        this.isListLoaded = false;

        this._loadList();
    },

    _loadList: function()
    {
        if ( this.isListLoading )
        {
            return;
        }

        var target = this.key;
        var request = JSON.stringify({
            console: OW.Console.getData('console').get(),
            data: this.data.get(),
            target: target,
            offset: this.getItemsCount(),
            ids: this._getItemIds()
        });

        this._ajaxStart();

        $.ajax({
            type: 'post',
            url: this.rsp,
            context: this,
            dataType: 'json',
            data: {
                request: request
            },
            success: this._ajaxSuccess,
            complete: this._ajaxComplete
        });
    },

    getItemsCount: function()
    {
        return this.getItems().length;
    },

    _getItemIds: function()
    {
        var ids = [];

        this.getItems().each(function()
        {
            var id = $(this).data('id');
            if ( id )
            {
                ids.push(id);
            }
        });

        return ids;
    },

    getItems: function()
    {
        return this.$list().children();
    },

    getItem: function( itemId )
    {
        return $('#' + itemId, this.$list());
    },

    removeItem: function( item )
    {
        var itemObject = $.type(item) == 'object' ? item : this.getItem(item);
        itemObject.remove();

        if ( this.getItemsCount() == 0 )
        {
            this.showNoContent();
        }

        this.updateScroll();
    },

    addItems: function( items, updateScrooll )
    {
        var self = this;

        $.each(items, function(i, item)
        {
            var $item = $(item.html).data('id', item.id);

            $item.find('.console_item_with_url').click(function( e )
            {
                if ( !$(e.target).is('a') )
                {
                    //window.open($(this).data('url'));
                    window.location.href = $(this).data('url');
                }
            });

            self.$list().append($item);
            OW.trigger('base.onAddConsoleItem', [], $item);
        });

        if ( updateScrooll !== false )
        {
            this.updateScroll();
        }

    },

    showPreloader: function()
    {
        this.$preloader().css({'visibility': 'visible'});
        this.$noContent().hide();
    },

    hidePreloader: function()
    {
        this.$preloader().css({'visibility': 'hidden'});
    },

    showNoContent: function()
    {
        this.$list().hide();
        this.$preloader().hide();
        this.$noContent().show();
    },

    hideNoContent: function()
    {
        this.$noContent().hide();
        this.$list().show();
    },

    updateScroll: function( toTop )
    {
        var self = this;

        toTop = toTop || false;

        if ( this.opened )
        {
            var hasScroll = false;
            if ( this.$container().data().jsp )
            {
                hasScroll = this.$container().data().jsp.getIsScrollableV();
            }
            else
            {
                hasScroll = this.$container().innerHeight() < this.$container().get(0).scrollHeight
            }

            OW.removeScroll(this.$container().get(0));

            if ( !hasScroll )
            {
                this.setIsListFull(true);

                return;
            }

            var jsp = OW.addScroll(this.$container().get(0));

            if ( toTop )
            {
                jsp.scrollToY(0, false);
            }

            this.$container().on('jsp-arrow-change', function( event, isAtTop, isAtBottom, isAtLeft, isAtRight )
            {
                if ( self.isListFull )
                {
                    return;
                }

                if ( isAtBottom )
                {
                    self.showPreloader();
                    self._loadList();
                }
            });
        }
    },

    setIsListFull: function( full )
    {
        this.isListFull = full;
        if ( full )
        {
            this.$preloader().hide();
        }
        else
        {
            this.$preloader().show();
        }
    },

    _ajaxStart: function()
    {
        this.isListLoading = true;
    },

    _ajaxComplete: function()
    {
        this.isListLoading = false;
        this.hidePreloader();
    },

    _ajaxSuccess: function( resp )
    {
        var self = this;

        if ( resp.data )
        {
            this.data.set(resp.data);
        }

        if ( resp.items.length )
        {
            this.addItems(resp.items, false);
        }
        else
        {
            this.setIsListFull(true);
        }

        if( this.getItemsCount() == 0 )
        {
            this.showNoContent();
        }
        else
        {
            this.hideNoContent();
        }

        if ( resp.markup )
        {
            if (resp.markup.styleSheets)
            {
                $.each(resp.markup.styleSheets, function(i, o)
                {
                    OW.addCssFile(o);
                });
            }

            if (resp.markup.styleDeclarations)
            {
                OW.addCss(resp.markup.styleDeclarations);
            }

            if (resp.markup.beforeIncludes)
            {
                OW.addScript(resp.markup.beforeIncludes);
            }

            if (resp.markup.scriptFiles)
            {
                OW.addScriptFiles(resp.markup.scriptFiles, function()
                {
                    if (resp.markup.onloadScript)
                    {
                        OW.addScript(resp.markup.onloadScript);
                    }
                });
            }
            else
            {
                if (resp.markup.onloadScript)
                {
                    OW.addScript(markup.onloadScript);
                }
            }
        }

        var scrollToTop = !this.isListLoaded;

        window.setTimeout(function() {
            self.updateScroll(scrollToTop);
        });


        this.isListLoaded = true;
    }
};



OW_Invitation = function( itemKey, params )
{
    var listLoaded = false;

    var listLoaded = false;
    var model, list, counter;

    //public methods

    this.removeItem = function( invitationKey )
    {
        var item = list.getItem(invitationKey);
        var c = {};

        if ( item.hasClass('ow_console_new_message') )
        {
            c["new"] = counter.get("new") - 1;
        }
        c["all"] = counter.get("all") - 1;
        counter.set(c);

        list.removeItem(item);

        return this;
    };

    this.send = function( command, data )
    {
        var request = $.ajax({
            url: params.rsp,
            type: "POST",
            data: {
                "command": command,
                "data": JSON.stringify(data)
            },
            dataType: "json"
        });

        request.done(function( res )
        {
            if ( res && res.script )
            {
                OW.addScript(res.script);
            }
        });

        return this;
    };

    //code

    model = OW.Console.getData(itemKey);
    list = OW.Console.getItem(itemKey);
    counter = new OW_DataModel();

    counter.addObserver(function()
    {
        var counterNumber = 0,
        newCount = counter.get('new');
        counterNumber = newCount > 0 ? newCount : counter.get('all');

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 )
        {
            list.showItem();
        }
    });

    model.addObserver(function()
    {
        if ( !list.opened )
        {
            counter.set(model.get('counter'));
        }

        if ( model.get('listFull') )
        {
            list.setIsListFull(true);
        }
    });

    list.onHide = function()
    {
        list.getItems().removeClass('ow_console_new_message');
        counter.set('new', 0);
        model.set('counter', counter.get());
    };

    list.onShow = function()
    {
        if ( counter.get('all') <= 0 )
        {
            this.showNoContent();

            return;
        }

        if ( counter.get('new') > 0 || !listLoaded )
        {
            this.loadList();
            listLoaded = true;
        }
    };
}

OW.Invitation = null;