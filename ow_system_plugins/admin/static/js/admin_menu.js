/*
var AdminMenu = function(menuIdList, closedItems)
{
    this.menuIdList = menuIdList;
    this.closedItems = closedItems;
    
    var self = this;
    
    var copen = function(nodeClass){
        var $context = $('.ow_admin_menu_cont .'+nodeClass);
        var link = $('a.arrow', $context);
        $('.ow_admin_menu_cont .'+nodeClass+' a.arrow').trigger('click');
    };
    
    var open = function(nodeClass){
        var $context = $('.ow_admin_menu_cont .'+nodeClass);
        $('a.arrow', $context).removeClass('open').addClass('close').unbind('click').bind('click', {nodeClass:nodeClass}, function(e){close(e.data.nodeClass);});
        $('.fake_admin_menu_cont', $context).slideDown(100);
        
        var index = self.closedItems.indexOf(nodeClass.toString());
        
        if(index > -1){
        	self.closedItems.splice(index, 1);
        }
        self.updateCookie();
    };

    var close = function(nodeClass){
        var $context = $('.ow_admin_menu_cont .'+nodeClass);
        $('a.arrow', $context).removeClass('close').addClass('open').unbind('click').bind('click', {nodeClass:nodeClass}, function(e){open(e.data.nodeClass);});
        $('.fake_admin_menu_cont', $context).slideUp(100);
        
        self.closedItems.push(nodeClass.toString());
        self.updateCookie();
    };

    $.each( this.menuIdList,
        function(i, o){
            $el = $('.ow_admin_menu_cont .'+this+' a.arrow');
            $('.ow_admin_menu_cont .'+this+' a.label_link').bind('click', {data:this}, function(e){copen(e.data.data)});
            
            if( $el.hasClass('open') )
            {
                $el.bind('click', {data:this}, function(e){open(e.data.data)});
            }
            else
            {
                $el.bind('click', {data:this}, function(e){close(e.data.data)});
            }
        }
    );
    
    this.updateCookie = function(){//console.log(JSON.stringify(self.closedItems));
    	//$.cookie("admin_menu_state", null);
    	$.cookie("admin_menu_state", JSON.stringify(self.closedItems), { expires: 365 });
    	//console.log($.cookie("admin_menu_state"));
    }
}


$('#top_link').click(function(){window.adsForm = new OW_FloatBox({$title:'Add Banner', $contents: $('#top_form'), width: '550px', height: '350px', class: 'ow_ic_add'})});
*/