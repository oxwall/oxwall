/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */


var AjaxUserList = (function($)
{
    var excludeList = [];
    var contentNode = undefined;
    var preloader = $('<div class="ow_left ow_base_preloader_box"><div class="ow_fw_menu ow_preloader"></div></div>');
    
    var respunderUrl = undefined;
    
    var orderType = undefined;
    var listId = undefined;
    
    var startPage = 1; 
    var endPage = 1; 
    var count = 20; 
    var process = false
    var allowLoadNext = true;
    var allowLoadPrev = false;
    
    var prevLinkNode;
    
    var utils = {
        
        setPrevProsessStatus: function( value )
        {
            var self = this;
            process = value;
        },
        
        setNextProsessStatus: function( value )
        {
            var self = this;

            process = value;
        }
    };
    
    return {
        init: function(params, node) {
            var self = this;
            
            excludeList = params['excludeList'];
            contentNode = node;
            respunderUrl = params['respunderUrl'];
            orderType = params['orderType'];
            listId = params['listId'];
            
            if ( params['page'] )
            {
                startPage = +params['page'];
                endPage = startPage;
                
                if ( startPage > 1 )
                {
                    allowLoadPrev = true;
                }
            }
            
            if ( params['count'] )
            {
                count = params['count'];
            }
            
            prevLinkNode = $('.base_load_earlier_profiles');
            prevLinkNode.find('a').click( function() { self.loadPrevious(); } );
            
            var timerId;
            
            $(window).scroll(function( event ) {
                self.tryLoadData();
                clearTimeout(timerId);
                timerId = setTimeout(function(){self.changeUrl();},40);
            });
            
            $(document).on('mouseover', '.ow_photo_item_wrap', function(event){
                $(this).find('.ow_usearch_user_info').show();

            });
            
            $(document).on('mouseout', '.ow_photo_item_wrap', function(event){
                $(this).find('.ow_usearch_user_info').hide();
            });
        },
        
        addToExcludeList: function(items) {
            $.each( items, function( key, val ) {
                excludeList.push(val);
            } );
        },
        
        setUrl: function (page) {
            window.history.pushState({}, undefined, '?page=' + page);
        },
        
        loadNext: function() {
            var self = this;

            if ( !allowLoadNext )
            {
                return;
            }
            
            if ( process )
            {
                return;
            }

            utils.setNextProsessStatus(true);
            
            var ajaxOptions = {
                
                url: respunderUrl,
                dataType: 'json',
                type: 'POST',
                
                data: {
                    command: 'getNext',
                    listId: listId,
                    orderType: orderType,
                    excludeList: excludeList,
                    count: count,
                    startFrom: startPage,
                    page: endPage + 1
                },
                
                beforeSend: function()
                {
                    self.showPreloader('d');
                },
                
                success: function(data)
                {
                    utils.setNextProsessStatus(false);
                    
                    if ( !data.items || data.items.length == 0 )
                    {
                        allowLoadNext = false;
                    }
                    else
                    {
                        endPage = endPage + 1; 
                        
                        allowLoadNext = true;
                        self.addToExcludeList(data.items);
                        self.renderNextList(data.content);

                    }
                    
                    self.changeUrl();
                    self.hidePreloader();
                }
            };

            $.ajax(ajaxOptions);
        },
        
        loadPrevious: function() {
            var self = this;

            if ( !allowLoadPrev || (startPage) < 2 )
            {
                return;
            }

            if ( process )
            {
                return;
            }

            utils.setNextProsessStatus(true);
            
            var ajaxOptions = {
                
                url: respunderUrl,
                dataType: 'json',
                type: 'POST',
                
                data: {
                    command: 'getPrev',
                    listId: listId,
                    orderType: orderType,
                    excludeList: excludeList,
                    count: count,
                    startFrom: startPage - 1,
                    page: startPage - 1
                },
                
                beforeSend: function()
                {
                    self.showPreloader('up');
                },
                
                success: function(data)
                {
                    utils.setNextProsessStatus(false);
                    
                    startPage = startPage - 1;
                    
                    if ( startPage < 2 )
                    {
                        allowLoadPrev = false;
                        prevLinkNode.hide();
                    }
                    else
                    {
                        allowLoadPrev = true;
                    }
                    

                    if ( !data.items || data.items.length == 0 )
                    {
                    }
                    else
                    {
                        self.addToExcludeList(data.items);
                        self.renderPrevList(data.content);
                    }
                    
                    self.changeUrl();
                    self.hidePreloader();
                }
            };

            $.ajax(ajaxOptions);
        },
        
        showPreloader: function( position )
        {
            var self = this;
            
            if ( position == 'up' )
            {
                prevLinkNode.addClass('ow_preloader');
            }
            else
            {
                self.renderNextList(preloader);
            }
        },
        
        hidePreloader: function()
        {
            preloader.detach();
            prevLinkNode.removeClass('ow_preloader');
        },
        
        renderNextList: function(content)
        {
            contentNode.append(content);
        },
        
        renderPrevList: function(content)
        {
            contentNode.prepend(content);
            //contentNode.prepend(content);
        },
        
        tryLoadData: function()
        {
            var self = this;

            if ( !allowLoadNext )
                return;

            var diff = $(document).height() - ($(window).scrollTop() + $(window).height());

            if ( diff < 100 )
            {
                self.loadNext();
            }
        },
        
        changeUrl: function()
        {
            var self = this;
            
            var list = $('.base_search_result_page');

            list.sort(function( a, b ) {  
                var p1 = $(a).data('page');
                var p2 = $(b).data('page');
                
                if ( p1 > p2 )
                {
                    return -1;
                }
                else if ( p1 < p2 )
                {
                    return 1;
                }
                
                return 0;
            } );

            var height = $(window).scrollTop() + $(window).height()/2;
            var page;
            $.each( list, function( key, item ) {
                var node = $( item );
                var offset = node.offset();
                page = node.data('page');
                
                if ( offset.top < height )
                {   
                    return false;
                }
            } );
            
            if ( page )
            {
                self.setUrl(page);
            }
        }
    } 
})(jQuery);