var OW_UserList = function( params )
{
    params = params || {};

    var self = this;
    this.node = params.node;
    this.cmp = params.component;
    this.list = params.listType;
    this.showOnline = params.showOnline;
    this.responder = params.responderUrl;
    this.count = params.count;

    this.preloader = $('.owm_user_list_preloader');
    
    this.allowLoadData = true;
    this.process = false;
    this.renderedItems = [];

    if ( $.isArray(params.excludeList) )
    {
        self.addDataToExcludeList(params.excludeList);
    }

    $(window).scroll(function( event ) {
            self.tryLoadData();
        });
        
    self.tryLoadData();
};

OW_UserList.prototype = 
{
    addDataToExcludeList: function( data )
    {
        var self = this;

        $.each( data, function( key, val ) {
            self.renderedItems.push(val);
        } )
    },

    getExcludeList: function()
    {
        var self = this;

        var list = []
        $.each( self.renderedItems, function( key, item ) {
            list.push(item);
        } );
        return list;
    },

    setProsessStatus: function( value )
    {
        var self = this;

        self.process = value;

        if ( value )
        {
            self.preloader.css("visibility","visible");
        }
        else
        {
            self.preloader.css("visibility","hidden");
            if ( !self.allowLoadData )
            {
                self.preloader.hide();
            }
        }
    },

    loadData: function()
    {
        var self = this;

        if ( self.process )
        {
            return;
        }

        self.setProsessStatus(true);

        var exclude = self.getExcludeList();

        var ajaxOptions = {
            url: self.responder,
            dataType: 'json',
            type: 'POST',
            data: {
                list: self.list,
                showOnline: self.showOnline,
                excludeList: exclude,
                count: self.count
            },            
            success: function(data)
            {
                
                
                if ( !data || data.length == 0 )
                {
                    self.allowLoadData = false;
                    self.setProsessStatus(false);
                }
                else
                {
                    self.allowLoadData = true;
                    self.addDataToExcludeList(data);
                    self.renderList(data);
                }

            }
        };
        
        $.ajax(ajaxOptions);
    },

    renderList: function( data )
    {
        var self = this;
        OWM.loadComponent( self.cmp, [self.list, data, self.showOnline], function( content ) {  self.append(content); self.setProsessStatus(false);  } );
        
        self.setProsessStatus(false);
    },

    append: function( content )
    {
        var self = this;

        $(self.node).find(self.preloader).before($(content));
    },

    tryLoadData: function()
    {
        var self = this;

        if ( !self.allowLoadData )
            return;

        var diff = $(document).height() - ($(window).scrollTop() + $(window).height());
        
        if ( diff < 100 )
        {
            self.loadData();
        }
    }
}