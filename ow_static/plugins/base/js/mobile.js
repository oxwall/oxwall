var OWMobile = function(){
    var self = this;
    var langs = {};
    var events = {};        
    var $overlay = $('#owm_overlay'), leftSbClass = 'owm_sidebar_left_active', rightSbClass = 'owm_sidebar_right_active', $main = $('#main');
    var $leftHeaderBtn = $('#owm_header_left_btn'), $rightHeaderBtn = $('#owm_header_right_btn'), $heading = $('#owm_heading'), $title = $('title');
    var state = "content";

    $('.owm_content_header_count').on("click.nav", function(){
        if ( state === "content" ) {
            self.showRightSidebar();
        }
        else if ( state === "right" ) {
            self.showContent();
        }
    });

    var btnDefaultInit = function(){
        $leftHeaderBtn.off('click.nav').on("click.nav", function(){self.showLeftSidebar();});
        $rightHeaderBtn.off('click.nav').on("click.nav", function(){self.showRightSidebar();});
    };

    btnDefaultInit();

    this.showLeftSidebar = function(){
        $('body').removeClass(leftSbClass).removeClass(rightSbClass).addClass(leftSbClass);
        $overlay.css({display:'block'}).off('click.nav').on("click.nav", function(){self.showContent();});
        $leftHeaderBtn.off('click.nav').on("click.nav", function(){self.showContent();});
        state = 'left';
        self.trigger('mobile.show_sidebar', {type: state});
    };

    this.showRightSidebar = function(options){

        options || (options = {});

        silent = options.silent;

        $('body').removeClass(leftSbClass).removeClass(rightSbClass).addClass(rightSbClass);
        $overlay.css({display:'block'}).off('click.nav').on("click.nav", function(){self.showContent();});
        $rightHeaderBtn.unbind('click').click(function(){self.showContent();});

        if (!silent){
            state = 'right';
            self.trigger('mobile.show_sidebar', {type: "right"});
        }
    };

    this.showContent = function(){
        $('body').removeClass(leftSbClass).removeClass(rightSbClass);
        $overlay.css({display:'none'}).off('click.nav');
        btnDefaultInit();
        self.trigger('mobile.hide_sidebar', {type: state});
        state = "content";
    };

    this.Header = (function(){
//        var states = [];
//        var lBtnClass = 'owm_nav_menu';
//        var rBtnClass = 'owm_nav_profile';
//
//        var setData = function( data ){
//            if( data.heading ){
//               $heading.html(data.heading);
//               $title.html(data.heading);
//           }
//
//           if( data.lBtnClick ){
//               $leftHeaderBtn.unbind('click').attr('href', 'javascript://').click(data.lBtnClick);
//           }
//
//           if( data.lBtnHref ){
//               $leftHeaderBtn.unbind('click').attr('href', data.lBtnHref);
//           }
//
//           if( data.lBtnIconClass ){
//               if( data.lBtnIconClass.indexOf(lBtnClass) == -1 ){
//                    data.lBtnIconClass += lBtnClass;
//               }
//
//               $leftHeaderBtn.attr('class', data.lBtnIconClass);
//           }
//
//           if( data.rBtnClick ){
//               $rightHeaderBtn.unbind('click').attr('href', 'javascript://').click(data.rBtnClick);
//           }
//
//           if( data.rBtnHref ){
//               $rightHeaderBtn.unbind('click').attr('href', data.rBtnHref);
//           }
//
//           if( data.rBtnIconClass ){
//               if( data.rBtnIconClass.indexOf(rBtnClass) == -1 ){
//                    data.rBtnIconClass += rBtnClass;
//               }
//
//               $rightHeaderBtn.attr('class', data.rBtnIconClass);
//           }
//        };
//
//        return {
//           addState: function( data ){
//               if( states.length == 0 ){
//                   var initState = {};
//                   initState.heading = $heading.html();
//                   initState.lBtnIconClass = $leftHeaderBtn.attr('class');
//                   initState.rBtnIconClass = $rightHeaderBtn.attr('class');
//                   states.push(initState);
//               }
//
//               states.push(data);
//               setData(data);
//           },
//           removeState: function(){
//               states.pop();
//
//               if( states.length > 0 ){
//                   setData(states[states.length-1]);
//               }
//           }
//        };
    })();

//    this.Header.addState(
//        {
//            lBtnClick: function(){self.showLeftSidebar();},
//            rBtnClick: function(){self.showRightSidebar();}
//        }
//    );

    this.escapeRegExp = function(s) {
        return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    this.error = function( message ){
        this.message(message, 'error');
    };

    this.warning = function( message ){
    	this.message(message, 'warning');
    };

    this.info = function( message ){
        this.message(message, 'info');
    };

     var $messageCont = $('<div class="owm_msg_wrap"></div>');
     var messageTime = 3000;
     $messageCont.appendTo($('body'));

    this.message = function( message, type, paramTime ){
        var $messageNode = $( '<div class="owm_msg_block owm_msg_'+type+' clearfix" style="display:none;"><a href="javascript://" onclick="$(this).closest(\'.owm_msg_block\').slideUp(200, function(){$(this).remove();})" class="owm_close_btn"></a><span class="owm_msg_txt">'+message+'</span></div>').appendTo($messageCont);
        if( paramTime == undefined ){
            paramTime = messageTime;
        }

        $messageNode.fadeIn(1000,
            function(){
                window.setTimeout(
                    function(){
                        $messageNode.fadeOut(1000,
                            function() {
                                $messageNode.remove();
                            }
                    );
                    }, paramTime
                );
            }
        );
    };

    this.getLanguageText = function(prefix, key, assignedVars)
    {
        if ( langs[prefix] === undefined ) {
                return prefix + '+' + key;
        }

        if ( langs[prefix][key] === undefined ) {
                return prefix + '+' + key;
        }

        var langValue = langs[prefix][key];

        if ( assignedVars ) {
                for( varName in assignedVars ) {
                        langValue = langValue.replace('{$'+varName+'}', assignedVars[varName]);
                }
        }

        return langValue;
    };

    this.registerLanguageKey = function(prefix, key, value)
    {   
            if ( langs[prefix] === undefined ) {
                    langs[prefix] = {};
            }

            langs[prefix][key] = value;
    };

    this.inProgressNode = function(node)
    {
    	return $(node).inprogress();
    };

    this.activateNode = function(node)
    {
    	return $(node).activate();
    };

    this.bind = function(type, func)
    {
        if (events[type] == undefined)
        {
            events[type] = [];
        }

        events[type].push(func);

    };

    this.unbind = function( type )
    {
        if (events[type] == undefined) {
            return false;
        }

        events[type] = [];
    };

    this.trigger = function(type, params, applyObject)
    {
        if (events[type] == undefined) {
            return false;
        }

        applyObject = applyObject || this;
        params = params || [];

        if ( !$.isArray(params) )
        {
            params = [params];
        }

        for (var i = 0, func; func = events[type][i]; i++)
        {
            if (func.apply(applyObject, params) === false)
            {
                return false;
            }
        }

        return true;
    };
    
    this.flagContent = function( entityType, entityId )
    {
        OWM.ajaxFloatBox("BASE_MCMP_Flag", [entityType, entityId], {
            width: 315,
            title: OWM.getLanguageText('base', 'flag_as')
        });
    };

    this.authorizationLimitedFloatbox = function( message )
    {
        OWM.ajaxFloatBox("BASE_MCMP_AuthorizationLimited", [message],
            {title: OWM.getLanguageText('base', 'authorization_limited_permissions')}
        );
    };

    this.addCssFile = function( url )
    {
        $('head').append($('<link type="text/css" rel="stylesheet" href="'+$.trim(url)+'" />'));
    };

    this.addCss = function( css ){
        $('head').append($('<style type="text/css">'+css+'</style>'));
    };

    var loadedScriptFiles = {};
    this.loadScriptFiles = function( urlList, callback, options ){
        
        if ( $.isPlainObject(callback) ) {
            options = callback;
            callback = null;
        }
        
        var addScript = function(url) {
            return jQuery.ajax($.extend({
                dataType: "script",
                cache: true,
                url: url
            }, options || {})).done(function() {
                loadedScriptFiles[url] = true;
            });
        };
        
        if( urlList && urlList.length > 0 ) {
            var recursiveInclude = function(urlList, i) {
                if( (i+1) === urlList.length )
                {
                    addScript(urlList[i]).done(callback);
                    return;
                }

                addScript(urlList[i]).done(function() {
                    recursiveInclude(urlList, ++i);
                });
            };
            recursiveInclude(urlList, 0);
        } else {
            callback.apply(this);
        }
    };

    this.addScriptFiles = function( urlList, callback, once ) {
        if ( once === false ) {
            this.loadScriptFiles(urlList, callback);
            return;
        }
        
        $("script").each(function() {
            loadedScriptFiles[this.src] = true;
        });
        
        var requiredScripts = $.grep(urlList, function(url) {
            return !loadedScriptFiles[url];
        });

        this.loadScriptFiles(requiredScripts, callback);
    };

    this.initWidgetMenu = function( items ){
        var $toolbarCont = null;
        var $contex = null;
        var condIds = [];
        var linkIds = [];
        $.each( items, function(key, value){
                if( $toolbarCont === null ){
                    $contex = $('#'+value['contId']).closest('.ow_box, .ow_box_empty');
                    $toolbarCont = $('.ow_box_toolbar_cont', $contex);
                }
                condIds.push('#'+value['contId']);
                linkIds.push('#'+value['id']);
            }
        );

        var contIdSelector = $(condIds.join(','));
        var linkIdSelector = $(linkIds.join(','));

        $.each( items, function(key, value){
                $('#'+value['id']).bind('click', {value:value},
                    function(e){
                        contIdSelector.hide();
                        $('#'+e.data.value.contId).show();
                        linkIdSelector.removeClass('owm_box_menu_item_active');
                        $(this).addClass('owm_box_menu_item_active');
                        
                        if( e.data.value.toolbarId != undefined ){
                            if( e.data.value.toolbarId ){
                                if( $toolbarCont.length === 0 ){
                                    $toolbarCont = $('<div class="ow_box_toolbar_cont"></div>');
                                    $contex.append($toolbarCont);
                                }
                                $toolbarCont.html($('#'+e.data.value.toolbarId).html());
                            }
                            else{
                                if( $toolbarCont.length !== 0 ){
                                    $toolbarCont.remove();
                                    $toolbarCont = [];
                                }
                            }
                        }
                    }
                );
            }
        );
    };

    this.addScript = function( script, scope, context )
    {
        if (!script)
        {
            return;
        }

        context = context || window;
        scope = scope || window;

        Function('_scope', script).call(context, scope);
    };

    /**
     * Loads a component
     *
     * Examples:
     * 1:
     * OW.loadComponent(cmpClass, '.place_to_node');
     *
     * 2:
     * OW.loadComponent(cmpClass, function(){
     *     //onReady event
     *     //Add the html somewhere
     * });
     *
     * 3:
     * OW.loadComponent(cmpClass, [ p1, p2, ... ], '.place_to_node');
     *
     * 4:
     * OW.loadComponent(cmpClass, [ p1, p2, ... ], function(html){
     *     //onReady event
     *     //Add the html somewhere
     * });
     *
     * 5:
     * OW.loadComponent(cmpClass, [ p1, p2, ... ],
     * {
     *     onLoad: function(){},
     *     onError: function(){},
     *     onComplete: function(){},
     *     onReady: function( html )
     *     {
     *        //Add the html somewhere
     *     }
     * });
     *
     * @param string cmpClass
     *
     * Cmp class params or targetNode selector or targetNod HtmlElement or ready callback
     * @param array|string|HTMLElement|jQuery|function p1
     *
     * Options or targetNode selector or targetNod HtmlElement or ready callback
     * @param object|string|HTMLElement|jQuery|function p2
     */
    this.loadComponent = function( cmpClass, p1, p2 )
    {
        function isNode( node )
        {
            return typeof node === 'string' || node.jquery || node.nodeType
        }

        var params = [], options = {};

        if ( isNode(p2) )
        {
            options.place = $(p2);
        }
        else if ( $.isPlainObject(p2) )
        {
            options = p2;
        }
        else if ( $.isFunction(p2) )
        {
            options.onReady = p2;
        }

        if ( isNode(p1) )
        {
            options.place = $(p1);
        }
        else if ( $.isArray(p1) || $.isPlainObject(p1) )
        {
            params = p1;
        }
        else if ( $.isFunction(p1) )
        {
            options.onReady = p1;
        }

        options = $.extend({}, {
            place: false,
            scope: window,
            context: window,
            addClass: '',
            onLoad: function( r ){},
            onReady: function( r ){},
            onError: function( r ){},
            onComplete: function( r ){}
        }, options);

        var rsp = this.ajaxComponentLoaderRsp,
            jsonParams = JSON.stringify(params),
            $preloader = false;

        if ( options.place )
        {
            $preloader = $('<div class="owm_preloader ' + options.addClass + '"></div>');
            $(options.place).html($preloader);
        }

        var ajaxOptions = {
            url: rsp + '?cmpClass=' + cmpClass + '&r=' + Math.random(),
            dataType: 'json',
            type: 'POST',
            data: {params: jsonParams},
            error: function(r)
            {
                options.onError(r);
            },
            complete: function(r)
            {
                options.onComplete(r);
            },
            success: function(markup)
            {
                var contentHtml = markup.content, $contentHtml = $(contentHtml);

                if ( !$contentHtml.length )
                {
                    contentHtml = '<span>' + contentHtml + '</span>';
                    $contentHtml = $(contentHtml)
                }

                if ( $preloader )
                {
                    $preloader.replaceWith($contentHtml);
                }

                options.onReady($contentHtml);

                if (markup.styleSheets)
                {
                    $.each(markup.styleSheets, function(i, o)
                    {
                        OWM.addCssFile(o);
                    });
                }

                if (markup.styleDeclarations)
                {
                    OWM.addCss(markup.styleDeclarations);
                }

                if (markup.beforeIncludes)
                {
                    OWM.addScript(markup.beforeIncludes, options.scope, options.context);
                }

                if (markup.scriptFiles)
                {

                    OWM.addScriptFiles(markup.scriptFiles, function()
                    {
                        if (markup.onloadScript)
                        {
                            OWM.addScript(markup.onloadScript, options.scope, options.context);
                            options.onLoad();
                        }
                    });
                }
                else
                {
                    if (markup.onloadScript)
                    {
                        OWM.addScript(markup.onloadScript, options.scope, options.context);
                    }
                    options.onLoad();
                }
            }
        };

        $.ajax(ajaxOptions);
    };
};

var OWM_Console = function( params )
{
    params.pingInterval = params.pingInterval * 1000;

    var self = this;
    self.params = params;
    self.counter = 0;
    self.consoleState = 'closed';
    self.lastSelectedTab = 'notifications';

    var $counter = $("#console-counter");
    var $tabCounter = $(".owm_sidebar_count_txt", "#console-tab-notifications");
    var $preloader = $("#console_preloader");
    var $body = $("#console_body");

    /* on right sidebar shown */
    OWM.bind("mobile.show_sidebar", function( data ){

        var beforeShowSidebarData = {openDefaultTab: true};
        OW.trigger("mobile.before_show_sidebar", beforeShowSidebarData);

        if ( data.type == "right" ) {

            if (beforeShowSidebarData.openDefaultTab){


                OWM.trigger("mobile.open_sidebar_tab", {key: self.lastSelectedTab});

//                self.loadPage("notifications", "BASE_MCMP_ConsoleNotificationsPage", function(){
//                    self.consoleState = 'open';
//                    self.counter = self.params.pages[0].counter;
//                });
            }
        }
    });

    /* on right sidebar hidden */
    OWM.bind("mobile.hide_sidebar", function( data ){
        if ( data.type == "right" )
        {
            self.consoleState = 'closed';
            self.counter = 0;
            self.setContent("");
            self.hideCounter();
        }
    });

    OWM.bind("mobile.console_item_removed", function( data ){
        self.hideCounter();
    });

    /* open right sidebar and specified tab */
    OWM.bind("mobile.open_sidebar_tab", function( data ){
        OWM.showRightSidebar({silent: true});
        var pageData = self.getPageData(data.key);
        self.activateTab(data.key);
        self.loadPage(data.key, pageData.cmpClass);
    });

    OWM.bind('mobile.console_show_counter', function( data ){
        self.showCounter(data.counter, data.tab, data.options);
    });

    /* console tab clicked */
    $("a.owm_sidebar_console_item_url").click(function() {
        var key = $(this).data('key');
        var data = self.getPageData(key);
        self.activateTab(key);
        self.loadPage(key, data.cmpClass);
    });

    /* loads console page */
    this.loadPage = function( key, cmpClass, onLoad ) {
        self.showPreloader();
        OWM.loadComponent(cmpClass, { },
            {
                onReady: function(html){
                    self.setContent(html);
                    self.hidePreloader();
                    if ( $.isFunction(onLoad) )
                    {
                        onLoad();
                    }
                    OWM.trigger('mobile.console_page_loaded', {key : key});
                }
            }
        );
        self.lastSelectedTab = key;
    };

    this.showPreloader = function() {
        $preloader.show();
    };

    this.hidePreloader = function() {
        $preloader.hide();
    };

    this.setContent = function( html ) {
        $body.html(html);
    };

    this.activateTab = function( key ) {
        $(".owm_sidebar_console_item").removeClass('owm_sidebar_console_item_active');
        $(".owm_sidebar_console_" + key).addClass("owm_sidebar_console_item_active");
    };

    this.showCounter = function(counter, tab, options){

        var options = options || {};

        self.counter = counter;

        // counter is visible, set new value
        if ( $(".owm_content_header_count").is(":visible") ) {
            $counter.html(self.counter);
        }
        else {
            // show counter
            $(".owm_nav_profile").fadeOut("fast", function(){
                $counter.html(self.counter);
                $(".owm_content_header_count").css({display: "inline-block"}).animate({right: "5"}, {duration: 300});
            });
        }

        if ( tab ) {
            var $tab = $("#console-tab-notifications");
            if ( $(".owm_sidebar_count", $tab).is(":visible") ) {
                $tabCounter.html(counter);
            }
            else {
                // show counter
                $tabCounter.html(counter);
                $(".owm_sidebar_count", $tab).fadeIn();
            }
        }
        else
        {
            self.lastSelectedTab = options.tab;
        }
    };

    this.hideCounter = function(){
        $(".owm_content_header_count").hide();
        $counter.html("");
        $(".owm_nav_profile").show();
        $(".owm_sidebar_count", "#console-tab-notifications").hide();
        $tabCounter.html("");
    };

    this.getPageData = function( pageKey ) {
        var result = null;
        $.each(self.params.pages, function(key, value) {
            if ( value.key == pageKey )
            {
                result = value;
            }
        });

        return result;
    };

    /* called after each ping response */
    this.afterPing = function( data ) {
        if ( data.count ) {
            if ( self.consoleState == "open" ) {
                self.updateMarkup(data);
            }
            else {
               self.showCounter(data.count, true);
            }
        }
    };

    this.updateMarkup = function( data ) {
        if ( data.new_items )
        {
            self.showCounter(data.count + self.counter, true);

            // trigger event for each section to show new markup
            $.each(data.new_items, function( section, markup ){
                if ( markup != '' )
                {
                    OWM.trigger('mobile.console_load_new_items', {page: 'notifications', section: section, markup: markup});
                }
            });
        }
    };

    $("body")
        .on("click", "li.owm_sidebar_msg_disabled", function(){
            OWM.error(
                OWM.getLanguageText(
                    'base',
                    'mobile_disabled_item_message',
                    {url : $(this).data("disabled-url") != "" ? decodeURIComponent($(this).data("disabled-url")) : params.desktopUrl} )
            );
        });

    /* console ping initialization */
    this.init = function() {
        OWM.getPing().setRspUrl(params.rspUrl);
        var cmd = OWM.getPing().addCommand("mobileConsole", {
            before: function() {
                this.params.state = self.consoleState;
                this.params.timestamp = self.params.lastFetchTime;
            },
            after: function( data ) {
                self.params.lastFetchTime = data.timestamp;
                self.afterPing(data);
            }
        });

        window.setTimeout(function() {
            cmd.start(self.params.pingInterval);
        }, 1000);
    };
};

var OWM_InvitationsConsole = function( params )
{
    var self = this;
    self.params = params;

    this.consoleAcceptRequest = function( $node )
    {
        var invId = $node.data("ref");
        var cmd = $node.data("cmd");
        var $row = $node.closest(".owm_sidebar_msg_item");
        $.ajax({
            url: self.params.cmdUrl,
            type: "POST",
            data: {"invid": invId, "command" : cmd},
            dataType: "json",
            success : function(data) {
                if ( data ) {
                    $row.remove();
                    OWM.trigger('mobile.console_item_removed', { section : "invitations" });
                    if ( data.result == true ) {
                        OWM.info(data.msg);
                    }
                }
            }
        });
    };

    this.consoleIgnoreRequest = function( $node )
    {
        var invId = $node.data("ref");
        var cmd = $node.data("cmd");
        var $row = $node.closest(".owm_sidebar_msg_item");
        $.ajax({
            url: self.params.cmdUrl,
            type: "POST",
            data: {"invid": invId, "command" : cmd},
            dataType: "json",
            success : function(data) {
                if ( data ) {
                    $row.remove();
                    OWM.trigger('mobile.console_item_removed', { section : "invitations" });
                }
            }
        });
    };

    this.consoleLoadMore = function( $node )
    {
        $node.addClass("owm_sidebar_load_more_preloader");

        var exclude =
            $("li.owm_sidebar_msg_item", "#invitations-list")
                .map(function(){
                    return $(this).data("invid");
                })
                .get();

        OWM.loadComponent(
            "BASE_MCMP_ConsoleInvitations",
            {limit: self.params.limit, exclude: exclude},
            {
                onReady: function(html){
                    $("#invitations-list").append(html);
                    $node.removeClass("owm_sidebar_load_more_preloader");
                }
            }
        );
    };

    this.hideLoadMoreButton = function()
    {
        $("#invitations-load-more").closest(".owm_sidebar_msg_list").hide();
    };

    $("body")
        .on("click", "a.owm_invite_accept", function(){
            self.consoleAcceptRequest($(this));
        })
        .on("click", "a.owm_invite_ignore", function(){
            self.consoleIgnoreRequest($(this));
        })
        .on("click", "a#invitations-load-more", function(){
            self.consoleLoadMore($(this));
        });

    OWM.bind("mobile.console_hide_invitations_load_more", function(){
        self.hideLoadMoreButton();
    });

    OWM.bind("mobile.console_load_new_items", function(data){
        if ( data.page == 'notifications' && data.section == 'invitations' )
        {
            $("#invitations-cap").show();
            $("#invitations-list").prepend(data.markup);
        }
    });

    OWM.bind("mobile.console_item_removed", function( data ){
        if ( data.section == "invitations" )
        {
            if ( $("#invitations-list li").length == 0 )
            {
                $("#invitations-cap").hide();
            }
        }
    });

    // unbind all events
    OWM.bind("mobile.hide_sidebar", function(data){
        if ( data.type == "right" )
        {
            OWM.unbind("mobile.console_hide_invitations_load_more");
            OWM.unbind("mobile.console_load_new_items");
            $("body")
                .off("click", "a.owm_invite_accept")
                .off("click", "a.owm_invite_ignore")
                .off("click", "a#invitations-load-more");
        }
    });
};

jQuery.fn.extend({
	inprogress: function() {
		this.each(function()
		{
			var $this = jQuery(this).addClass('ow_inprogress');
			this.disabled = true;

			if ( this.tagName != 'INPUT' && this.tagName != 'TEXTAREA' && this.tagName != 'SELECT' )
			{
				this.jQuery_disabled_clone = $this.clone().removeAttr('id').removeAttr('onclick').get(0);

				$this.hide()
					.bind('unload', function(){
						$this.activate();
					})
					.after(this.jQuery_disabled_clone);
			}
		});

		return this;
	},

	activate: function() {
		this.each(function()
		{
			var $this = jQuery(this).removeClass('ow_inprogress');
			this.disabled = false;

			if ( this.jQuery_disabled_clone )
			{
				jQuery(this.jQuery_disabled_clone).remove();
				this.jQuery_disabled_clone = null;

				jQuery(this)
					.unbind('unload', function(){
						$this.activate();
					})
					.show();
			}
		});

		return this;
	}
});

window.OWM = new OWMobile();


/* OW Forms */

var OwFormElement = function( id, name ){
    this.id = id;
    this.name = name;
    this.input = document.getElementById(id);
    this.validators = [];
}

OwFormElement.prototype = {

    validate: function(){

        var error = false;

        try{
            for( var i = 0; i < this.validators.length; i++ ){
                this.validators[i].validate(this.getValue());
            }
        }catch (e) {
            error = true;
            this.showError(e);
        }

        if( error ){
            throw e;
        }
    },

    addValidator: function( validator ){
        this.validators.push(validator);
    },

    getValue: function(){
        return $(this.input).val();
    },

    setValue: function( value ){
        $(this.input).val(value);
    },

    resetValue: function(){
        $(this.input).val('');
    },

    showError: function( errorMessage ){
        $('#'+this.id+'_error').append(errorMessage).fadeIn(50);
    },

    removeErrors: function(){
        $('#'+this.id+'_error').empty().fadeOut(50);
    }
}

var OwForm = function( params ){
    $.extend(this, params);
    this.form = document.getElementById(this.id);
    this.elements = {};
    var actionUrl = $(this.form).attr('action');
    this.actionUrl = ( !actionUrl ? location.href : actionUrl );
    this.showErrors = true;
    this.events = {
        submit:[],
        success:[]
    }
};

OwForm.prototype = {

    addElement: function( element ){
        this.elements[element.name] = element;
    },

    getElement: function( name ){
        if( this.elements[name] === undefined ){
            return null;
        }

        return this.elements[name];
    },

    validate: function(){

        var error = false;
        var element = null;
        var errorMessage;

        $.each( this.elements,
            function(index, data){
                try{
                    data.validate();
                }catch (e){
                    error = true;

                    if( element == null ){
                        element = data;
                        errorMessage = e;
                    }
                }
            }
            );

        if( error ){
            element.input.focus();

            if( this.validateErrorMessage ){
                throw this.validateErrorMessage;
            }else{
                throw errorMessage;
            }
        }
    },

    bind: function( event, fnc ){
        this.events[event].push(fnc);
    },

    sucess: function( fnc ){
        this.bind('success', fnc);
    },

    submit: function( fnc ){
        this.bind('submit', fnc);
    },

    trigger: function( event, data ){
        if( this.events[event] == undefined || this.events[event].length == 0 ){
            return;
        }
        
        var result = undefined, returnVal;

        for( var i = 0; i < this.events[event].length; i++ ){
            
            returnVal = this.events[event][i].apply(this.form, [data]);
            if(returnVal === false || returnVal === true ){
                result = returnVal;
            }
        }
        
        if( result !== undefined ){
            return result;
        }
    },

    getValues: function(){

        var values = {};

        $.each(this.elements,
            function( index, data ){
                values[data.name] = data.getValue();
            }
            );

        return values;
    },

    setValues: function( values ){

        var self = this;

        $.each( values,
            function( index, data ){
                if(self.elements[index]){
                    self.elements[index].setValue(data);
                }
            }
            );
    },

    resetForm: function(){
        $.each( this.elements,
            function( index, data ){
                data.resetValue();
            }
            );
    },

    removeErrors: function(){

        $.each( this.elements,
            function( index, data ){
                data.removeErrors();
            }
            );
    },

    submitForm: function(){

        var self = this;

        this.removeErrors();

        try{
            this.validate();
        }catch(e){
            if( this.showErrors ){
                OWM.error(e);
            }
            return false;
        }

        var dataToSend = this.getValues();
        if( self.trigger('submit', dataToSend) === false ){
            return false;
        }

        var buttons = $('input[type=button], input[type=submit], button', '#' + this.id).addClass('ow_inprogress');

        if( this.ajax ){
            OWM.inProgressNode(buttons);
            var postString = '';

            $.each( dataToSend, function( index, data ){
                if ( $.isArray(data) || $.isPlainObject(data) ) {
                    $.each(data, function (key, value){
                        postString += index + '[' + key + ']=' + encodeURIComponent(value) + '&';
                    });
                }
                else{
                    postString += index + '=' + encodeURIComponent(data) + '&';
                }
            } );

            $.ajax({
                type: 'post',
                url: this.actionUrl,
                data: postString,
                dataType: self.ajaxDataType,
                success: function(data){
                    if(self.reset){
                        self.resetForm();
                    }
                    self.trigger('success', data);
                },
                error: function( XMLHttpRequest, textStatus, errorThrown ){
                    OWM.error(textStatus);
                    throw textStatus;
                },
                complete: function(){
                    OWM.activateNode(buttons);
                }
            });

            return false;
        }

        $.each(this.elements,
            function( i, o ){
                if( $(o.input).hasClass('invitation') ){
                    $(o.input).attr('disabled', 'disabled');
                }
            }
            );

        return true;
    }
}

owForms = {};

// custom fields
var addInvitationBeh = function( formElement, invitationString ){
    formElement.invitationString = invitationString;

    formElement.getValue = function(){
        var val = $(this.input).val();
        if( val != '' && val != this.invitationString ){
            $(this.input).removeClass('invitation');
            return val;
        }
        else{
            return '';
        }
    };

    var parentResetValue = formElement.resetValue;

    formElement.resetValue = function(){
        parentResetValue.call(this);

        $(this.input).addClass('invitation');
        $(this.input).val(invitationString);
    };

    $(formElement.input
        ).bind('focus.invitation', {formElement:formElement},
            function(e){
                el = $(this);
                el.removeClass('invitation');
                if( el.val() == '' || el.val() == e.data.formElement.invitationString){
                    el.val('');
                    //hotfix for media panel
                    if( 'htmlarea' in el.get(0) ){
                        el.unbind('focus.invitation').unbind('blur.invitation');
                        el.get(0).htmlarea();
                        el.get(0).htmlareaFocus();
                    }
                }
                else{
                    el.unbind('focus.invitation').unbind('blur.invitation');
                }
            }
        )/*.bind('blur.invitation', {formElement:formElement},
            function(e){
                el = $(this);
                if( el.val() == '' || el.val() == e.data.formElement.invitationString){
                    el.addClass('invitation');
                    el.val(e.data.formElement.invitationString);
                }
                else{
                    el.unbind('focus.invitation').unbind('blur.invitation');
                }
            }
    );*/
}

var OwTextField = function( id, name, invitationString ){
    var formElement = new OwFormElement(id, name);
    if( invitationString ){
        addInvitationBeh(formElement, invitationString);
    }
    return formElement;
}

var OwTextArea = function( id, name, invitationString ){
    var formElement = new OwFormElement(id, name);
    if( invitationString ){
        addInvitationBeh(formElement, invitationString);
    }
    return formElement;
}

var OwWysiwyg = function( id, name, invitationString ){
    var formElement = new OwFormElement(id, name);
    formElement.input.focus = function(){this.htmlareaFocus();};
    addInvitationBeh(formElement, invitationString);
    formElement.resetValue = function(){$(this.input).val('');$(this.input).keyup();};
    formElement.getValue = function(){
                var val = $(this.input).val();
                if( val != '' && val != '<br>' && val != '<div><br></div>' && val != this.invitationString ){
                    $(this.input).removeClass('invitation');
                    return val;
                }
                else{
                    return '';
                }
            };

    return formElement;
}

var OwRadioField = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var value = $("input[name='"+this.name +"']:checked", $(this.input.form)).val();
        return ( value == undefined ? '' : value );
    };

    formElement.resetValue = function(){
        $("input[name='"+this.name +"']:checked", $(this.input.form)).removeAttr('checked');
    };

    formElement.setValue = function(value){
        $("input[name='"+ this.name +"'][value='"+value+"']", $(this.input.form)).attr('checked', 'checked');
    };

    return formElement;
}

var OwCheckboxGroup = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $inputs = $("input[name='"+ this.name +"[]']:checked", $(this.input.form));
        var values = [];

        $.each( $inputs, function(index, data){
                if( this.checked == true ){
                    values.push($(this).val());
                }
            }
        );

        return values;
    };

    formElement.resetValue = function(){
        var $inputs = $("input[name='"+ this.name +"[]']:checked", $(this.input.form));

        $.each( $inputs, function(index, data){
                $(this).removeAttr('checked');
            }
        );
    };

    formElement.setValue = function(value){
        for( var i = 0; i < value.length; i++ ){
            $("input[name='"+ this.name +"[]'][value='"+value[i]+"']", $(this.input.form)).attr('checked', 'checked');
        }
    };

    return formElement;
}

var OwCheckboxField = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $input = $("input[name='"+this.name+"']:checked", $(this.input.form));
        if( $input.length == 0 ){
            return '';
        }
        return 'on';
    };

    formElement.setValue = function(value){
        var $input = $("input[name='"+this.name+"']:checked", $(this.input.form));
        if( value ){
            $input.attr('checked', 'checked');
        }
        else{
            $input.removeAttr('checked');
        }
    };
    formElement.resetValue = function(){
        var $input = $("input[name='"+this.name+"']:checked", $(this.input.form));
        $input.removeAttr('checked');
    };

    return formElement;
};

var OwRange = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $inputFrom = $("select[name='"+ this.name +"[from]']");
        var $inputTo = $("select[name='"+ this.name +"[to]']");
        var values = [];

        values.push($inputFrom.val());
        values.push($inputTo.val());

        return values;
    };

    formElement.setValue = function(value){
        var $inputFrom = $("select[name='"+ this.name +"[from]']");
        var $inputTo = $("select[name='"+ this.name +"[to]']");

        if( value[1] ){
            $("option[value='"+ value[1] +"']", $inputFrom).attr('selected', 'selected');
        }

        if( value[2] ){
            $("option[value='"+ value[2] +"']", $inputTo).attr('selected', 'selected');
        }

    };

    return formElement;
};

/* end of forms */

/* PING */
OWM_PingCommand = function( commandName, commandObject, stack )
{
    $.extend(this, commandObject);

    this.commandName = commandName;
    this.repeatTime = false;
    this.minRepeatTime = null;

    this.stack = stack;
    this.commandTimeout = null;
    this.stopped = true;
    this.skipped = false;
    this.inProcess = false;
    this.isRootCommand = false;

    this._lastRunTime = null;
};

OWM_PingCommand.PROTO = function()
{
    this._updateLastRunTime = function()
    {
        this._lastRunTime = $.now();
    };

    this._received = function( r )
    {
        this.after(r);
    };

    this._delayCommand = function()
    {
        var self = this;

        if ( this.commandTimeout )
        {
            window.clearTimeout(this.commandTimeout);
        }

        this.commandTimeout = window.setTimeout(function()
        {
            self._run();
            self.skipped = false;
        }, this.repeatTime);
    };

    this._completed = function()
    {
        this.inProcess = false;
        this._updateLastRunTime();

        if ( this.skipped || this.stopped || this.repeatTime === false )
        {
            return;
        }

        this._delayCommand();
    };

    this._getStackCommand = function()
    {
        return {
            "command": this.commandName,
            "params": this.params
        };
    };

    this._beforeStackSend = function()
    {
        if ( this.minRepeatTime === null || this.stopped || this.inProcess || this.isRootCommand )
        {
            return;
        }

        if ( $.now() - this._lastRunTime < this.minRepeatTime )
        {
            return;
        }

        this._run();
    };

    this._run = function()
    {
        if ( !this.stopped )
        {
            this.inProcess = true;
            this.stack.push(this);
        }

        if ( this.onRun )
        {
            this.onRun(this);
        }
    };

    this.params = {};
    this.before = function(){};
    this.after = function(){};

    this.start = function( repeatTime )
    {
        if ( $.isNumeric(repeatTime) )
        {
            this.repeatTime = repeatTime;
        }
        else if ( $.isPlainObject(repeatTime) )
        {
            if ( repeatTime.max )
            {
                this.repeatTime = repeatTime.max;
            }

            if ( repeatTime.min )
            {
                this.minRepeatTime = repeatTime.min == 'each' ? 0 : repeatTime.min;
            }
        }

        this.stop();
        this.stopped = false;

        if ( !this.inProcess )
        {
            this._run();
        }
    };

    this.skip = function()
    {
        this.skipped = true;
        this._delayCommand();
    };

    this.stop = function()
    {
        this.stopped = true;
    };
};

OWM_PingCommand.prototype = new OWM_PingCommand.PROTO();

OWM_Ping = function()
{
    var _stack = [], _commands = {};

    var rspUrl;
    var _rootCommand = null;

    var beforeStackSend, sendStack, refreshRootCommand, rootOnCommandRun, genericOnCommandRun, setRootCommand;

    rootOnCommandRun = function( command )
    {
        window.setTimeout(function(){
            sendStack();
        }, 10);
    };

    genericOnCommandRun = function( command )
    {
        if ( !_rootCommand )
        {
            setRootCommand(command);
            rootOnCommandRun(command);

            return;
        }

        if ( command.repeatTime === false )
        {
            return;
        }

        if ( _rootCommand.repeatTime === false || _rootCommand.repeatTime > command.repeatTime )
        {
            setRootCommand(command);
            rootOnCommandRun(command);
        }
    };

    refreshRootCommand = function()
    {
        var rootCommand = null;

        for ( var c in _commands )
        {
            if ( _commands[c].repeatTime === false || _commands[c].stopped  )
            {
                continue;
            }

            if ( !rootCommand || _commands[c].repeatTime < rootCommand.repeatTime )
            {
                rootCommand = _commands[c];
            }
        }

        if ( rootCommand )
        {
            setRootCommand(rootCommand);
        }
    };

    setRootCommand = function( command )
    {
        if ( _rootCommand )
        {
            _rootCommand.onRun = genericOnCommandRun;
            _rootCommand.isRootCommand = false;
        }

        command.isRootCommand = true;
        _rootCommand = command;
        _rootCommand.onRun = rootOnCommandRun;
    };

    beforeStackSend = function()
    {
        for ( var c in _commands )
        {
            _commands[c]._beforeStackSend();
        }
    };

    sendStack = function()
    {
        beforeStackSend();

        if ( !_stack.length )
        {
            return;
        }

        var stack = [], commands = [];

        while ( _stack.length )
        {
            var c = _stack.pop();
            commands.push(c);

            if ( c.before() === false )
            {
                c.skip();
                continue;
            }

            stack.push(c._getStackCommand());
        }

        if ( !stack.length )
        {
            return;
        }

        var request = {
            "stack": stack
        };

        var jsonRequest = JSON.stringify(request);

        var ajaxOptions =
        {
            url: rspUrl,
            dataType: 'json',
            type: 'POST',
            data: {request: jsonRequest},
            success: function(result)
            {
                if ( !result || !result.stack )
                {
                    return;
                }

                $.each(result.stack, function(i, command)
                {
                    if ( _commands[command.command] )
                    {
                        _commands[command.command]._received(command.result);
                    }
                });
            },

            complete: function()
            {
                $(commands).each(function(i, command)
                {
                    command._completed();
                });

                refreshRootCommand();
            }
        };

        $.ajax(ajaxOptions);
    };

    this.addCommand = function( commandName, commandObject )
    {
        if ( _commands[commandName] )
        {
            return _commands[commandName];
        }

        commandObject = commandObject || {};

        _commands[commandName] = new OWM_PingCommand(commandName, commandObject, _stack);
        _commands[commandName].onRun = genericOnCommandRun;

        return _commands[commandName]
    };

    this.getCommand = function( commandName )
    {
        return _commands[commandName] || null;
    };

    this.setRspUrl = function( url )
    {
        rspUrl = url;
    };
};

OWM_Ping.getInstance = function()
{
    if ( !OWM_Ping.pingInstance )
    {
        OWM_Ping.pingInstance = new OWM_Ping();
    }

    return OWM_Ping.pingInstance;
};

OWM.getPing = function()
{
    return OWM_Ping.getInstance();
};


/* FloatBox */

OWM.getActiveFloatBox = function() {
    return window.OWActiveFloatBox || null;
};

OWM.FloatBox = (function() {
    var _overlay = $(".owm_overlay");
    var _tpl = $("[data-tpl=wrap]", "#floatbox_prototype");
    var _stack = [];
    
    _overlay.on("click.fb", function() {
        while (_stack.length) {
            _stack[0].close();
        }
    });
    
    function FloatBox(params)
    {
        var self = this;
        params = params || {};
        
        if ( _stack.length ) {
            _stack[0]._disappear();
        }
        _stack.unshift(this);
        
        window.OWActiveFloatBox = this;
        $(window).scrollTop(0);
        
        _overlay.show();
        
        this.container = _tpl.clone();
        $("section", "#main").append(this.container);
        
        this.body = this.container.find("[data-tpl=body]");
        this.leftBtn = this.container.find("[data-tpl=left-btn]").hide();
        this.rightBtn = this.container.find("[data-tpl=right-btn]").hide();
        this.heading = this.container.find("[data-tpl=heading]");
        
        this.events = {};
        this._contentParent = null;
       
        if ( params.addClass ) {
            this.container.addClass(params.addClass);
        }
       
        if ( params.content ) {
            this.setContent(params.content);
        }
        
        if ( params.height ) {
            this.body.height(params.height);
        }
        
        this.title = params.title || "";
        
        if ( this.title ) {
            this.heading.text(this.title);
        }
        
        this._setupBtn(this.leftBtn, params.leftBtn || {
            "iconClass": "owm_nav_back",
            "click": function() {
                self.close();
            }
        });
        
        if ( params.rightBtn ) {
            this._setupBtn(this.leftBtn, params.rightBtn);
        }
        
        this.trigger("show");
    }
    
    var proto = {
        _appear: function() {
            this.container.show();
        },
        
        _disappear: function() {
            this.container.hide();
        },
        
        _setupBtn: function( btn, params ) {
            btn.show();
            
            if ( params.iconClass ) {
                btn.addClass(params.iconClass);
            }
            
            if ( params.click && $.isFunction(params.click) ) {
                btn.on("click.fb", params.click);
            }
            
            if ( params.url ) {
                btn.attr("href", params.url);
            }
        },
        
        setContent: function( content ) {
            if (typeof content === 'string')
            {
                if ( !$(content).length ) {
                    content = $('<span>' + content + '</span>');
                }
            }
            else
            {
                this._contentParent = content.parent();
            }
            
            this.body.html(content);
        },
        
        close: function() {
            if (this.trigger('close') === false) {
                return false;
            }
            
            if ( this._contentParent )
            {
                this._contentParent.append(this.body.children());
            }
            
            this.container.remove();
            
            _stack.shift();
            
            if ( !_stack.length ) {
                _overlay.hide();
            }
            else {
                _stack[0]._appear();
            }
            
            return true;
        },

        bind: function(type, func) {
            this.events[type] = this.events[type] || [];
            this.events[type].push(func);
        },

        trigger: function(type, params) {
            params = params || [];
            var stack = this.events[type] || [];

            for ( var i = 0; stack[i]; i++ ) {
                if ( stack[i].apply(this, params) === false ) {
                    return false;
                }
            }

            return true;
        }
    };
        
    return function () {
        function F() {};
        F.prototype = proto;
        
        var o = new F();
        FloatBox.apply(o, arguments);

        return o;
    };
})();


OWM.ajaxFloatBox = function(cmpClass, params, options)
{
    params = params || [];

    options = options || {};
    options = $.extend({}, {
        title: '',
        height: false,
        iconClass: false,
        addClass: false,
        leftBtn: false,
        rightBtn: false,
        scope: {},
        context: null,
        onLoad: function(){},
        onReady: function(){},
        onError: function(){},
        onComplete: function(){}
    }, options);

    var floatBox = new OWM.FloatBox({
        title: options.title,
        height: options.height,
        iconClass: options.iconClass,
        addClass: options.addClass
    });

    options.scope = $.extend({floatBox: floatBox}, options.scope);
    options.context = options.context || floatBox;

    this.loadComponent(cmpClass, params,
    {
        scope: options.scope,
        context: options.context,
        onLoad: options.onLoad,
        onError: options.onError,
        onComplete: options.onComplete,
        onReady: function( r )
        {
            floatBox.setContent(r);

            if ( $.isFunction(options.onReady) )
            {
                options.onReady.call(this, r);
            }
        }
    });

    return floatBox;
};

window.OW = window.OWM;

/* Comments */
var OwMobileComments = function( contextId, formName, genId ){
	this.formName = formName;
	this.$cmpContext = $('#' + contextId);
    this.genId = genId;
    
};

OwMobileComments.prototype = {
    repaintCommentsList: function( data ){
        owForms[this.formName].getElement('commentText').resetValue();
        if(data.error){
            OW.error(data.error);
            return;
        }
        window.owCommentListCmps.items[this.genId].updateMarkup(data);
    },

    setCommentsCount: function( count ){
        $('input[name=commentCountOnPage]', this.$cmpContext).val(count);
    },

    getCommentsCount: function(){
        return parseInt($('input[name=commentCountOnPage]', this.$cmpContext).val());
    },
    
    initForm: function( textareaId, submitId ){
        var self = this;

        // init pseudo auto click
        var $textA = $('#'+textareaId, this.$cmpContext), $submitCont = $('#'+submitId, this.$cmpContext).closest('.comment_submit');
        if( !this.taMessage ){
            this.taMessage = $textA.val();
        }

        var taHandler = function(){
            $(this).removeClass('invitation').val('');
            $submitCont.show();
            //setTimeout(function(){$('body').animate({scrollTop: 1000})}, 2000);

        };

        var resetTa = function(){
            $textA.unbind('focus').one('focus', taHandler).val(self.taMessage).addClass('invitation');
            $submitCont.hide();
        };

        $textA.unbind('focus').one('focus', taHandler).bind('blur',
            function(){
                if( $(this).val() == '' ){
                    resetTa();
                }
            }
        );

        //end
        owForms[this.formName].bind('success',
            function(data){                
                resetTa();
                self.repaintCommentsList(data);
                OW.trigger('base.comment_add', {entityType: data.entityType, entityId: data.entityId}, this);
                $textA.focus();
            }
        );

       owForms[this.formName].bind('submit',
            function(data){
                var cmpObj = owCommentCmps[self.genId], count = cmpObj.getCommentsCount()+1;
                data['commentCountOnPage'] = count;
                cmpObj.setCommentsCount(count);
            }
        );

        owForms[this.formName].reset = false;
    }

};

var OwMobileCommentsList = function( params ){
	this.$context = $('#' + params.contextId);
	$.extend(this, params, owCommentListCmps.staticData);
};

OwMobileCommentsList.prototype = {
	init: function(){
		var self = this;
//
//        OW.bind('base.comments_list_update',
//            function(data){
//                if( data.entityType == self.entityType && data.entityId == self.entityId && data.id != self.cid ){
//                    self.reload();
//                }
//            }
//        );

          $('.cmnt_load_more_cont', this.$context).click(
            function(){
                $(this).addClass('owm_load_more');
                self.commentCountOnPage += self.loadCount;
                self.commentsToLoad -= self.loadCount;
                window.owCommentCmps[self.cid].setCommentsCount(self.commentCountOnPage);
                self.reload();
                $(this).removeClass('owm_load_more');
            }
          );

         OW.trigger('base.comments_list_init', {entityType: this.entityType, entityId: this.entityId}, this);
	},

    updateMarkup: function( data ){
        this.$context.replaceWith(data.commentList);
        OW.addScript(data.onloadScript);
        OW.trigger('base.comments_list_update', {entityType: data.entityType, entityId: data.entityId, id:this.genId});
    },

	reload:function(){
		var self = this;
		$.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: 'cid='+self.cid+'&commentCountOnPage='+self.commentCountOnPage+'&ownerId='+self.ownerId+'&pluginKey='+self.pluginKey+'&displayType='+self.displayType+'&entityType='+self.entityType+'&entityId='+self.entityId,
            dataType: 'json',
            success : function(data){
               if(data.error){
                        OW.error(data.error);
                        return;
                }
                self.updateMarkup(data);
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
	}
};

owCommentCmps = {};
owCommentListCmps = {items:{},staticData:{}};



OWM.Utils = (function() {

    return {
        toggleText: function( node, value, alternateValue ) {
            var $node = $(node), text = $node.text();

            if ( !$node.data("toggle-text") )
                $node.data("toggle-text", text);

            alternateValue = alternateValue || $node.data("toggle-text");
            $node.text(text === alternateValue ? value : alternateValue);
        },

        toggleAttr: function( node, attributeName, value, alternateValue ) {
            var $node = $(node), attributeValue = $node.attr(attributeName);

            if ( !$node.data("toggle-" + attributeName) )
                $node.data("toggle-" + attributeName, attributeValue);

            alternateValue = alternateValue || $node.data("toggle-" + attributeName);
            $node.attr(attributeName, attributeValue === alternateValue ? value : alternateValue);
        }
    };
})();


OWM.Users = null;
OWM_UsersApi = function( _settings )
{
    var _usersApi = this;

    var _query = function(command, params, callback) {
        callback = callback || function( r ) {
            if ( !r ) return;
            if (r.info) OW.info(r.info);
            if (r.error) OW.error(r.error);
        };

        return $.getJSON(_settings.rsp, {"command": command, "params": JSON.stringify(params)}, callback);
    };

    OWM.Users = this;

    //Public methods

    this.deleteUser = function(userId, callBack)
    {
        var redirectUrl = null;

        if ( typeof callBack === "string" )
        {
            redirectUrl = callBack;
            
            callBack = function() {
                window.location.assign(redirectUrl);
            };
        }
        
        return _query("deleteUser", {"userId": userId}, callBack);
    },

    this.suspendUser = function( userId, callback ) {
        return _query("suspend", {"userId": userId}, callback);
    };

    this.unSuspendUser = function( userId, callback ) {
        return _query("unsuspend", {"userId": userId}, callback);
    };

    this.blockUser = function( userId, callback ) {
        return _query("block", {"userId": userId}, callback);
    };

    this.unBlockUser = function( userId, callback ) {
        return _query("unblock", {"userId": userId}, callback);
    };

    this.featureUser = function( userId, callback ) {
        return _query("feature", {"userId": userId}, callback);
    };

    this.unFeatureUser = function( userId, callback ) {
        return _query("unfeature", {"userId": userId}, callback);
    };
};