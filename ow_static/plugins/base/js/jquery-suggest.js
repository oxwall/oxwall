	
	/*
	 *	jquery.suggest 1.2 - 2007-08-21
	 *	
	 *  Original Script by Peter Vulgaris (www.vulgarisoip.com)
	 *  Updates by Chris Schuld (http://chrisschuld.com/)
	 *
	 */
	
	(function($) {

		$.suggest = function(input, options) {
	
			var $input = $(input).attr("autocomplete", "off");
			var $results;

			var timeout = false;		// hold timeout ID for suggestion results to appear	
			var prevLength = 0;			// last recorded length of $input.val()
			var cache = [];				// cache MRU list
			var cacheSize = 0;			// size of cache in chars (bytes?)
            
            if (!options.attachObject) {
                options.attachObject = addListContainer();
            }
            
            var firstFocus = true;
            $input.focus(function(){
                if (typeof options.onFocus == 'function') {
                    options.onFocus.apply(this, [firstFocus]);
                }
               
                if (options.autoSuggest) {
                    var value = $(this).val();
                    if (typeof options.onAutoSuggest == 'function') {
                        value = options.onAutoSuggest.apply(this, [value, firstFocus])
                    }

                    if ( value !== false ) {
                        value = value || '';
                        suggestByStartValue( value );
                        prevLength = value.length;
                        options.onSelect = function(){
			                 prevLength = $(this).val().length;
			            };
                    }
                }
                
                firstFocus = false;
            });
            
            $input.blur(function(){
                if (typeof options.onBlur == 'function') {
                    var self = this;
                    window.setTimeout(function(){
                        var value = options.onBlur.apply(self);
                        if (options.dataContainer && value !== undefined) {
                            $(options.dataContainer).val(value);
                        }
                    }, 300);
                }
            });

			if( ! options.attachObject )
				options.attachObject = $(document.createElement("ul")).appendTo('body');

			$results = $(options.attachObject);
			$results.addClass(options.resultsClass);
			
			/*
			resetPosition();
			$(window)
				.load(resetPosition)		// just in case user is changing size of page while loading
				.resize(resetPosition);
            */
			$input.blur(function() {
				setTimeout(function() { $results.hide() }, 200);
			});
			
			
			// help IE users if possible
			try {
				$results.bgiframe();
			} catch(e) { }


			// I really hate browser detection, but I don't see any other way
			if ($.browser.mozilla)
				$input.keypress(processKey);	// onkeypress repeats arrow keys in Mozilla/Opera
			else
				$input.keydown(processKey);		// onkeydown repeats arrow keys in IE/Safari
			


            /*
			function resetPosition() {
				// requires jquery.dimension plugin
				var offset = $input.offset();
				$results.css({
					top: (offset.top + input.offsetHeight) + 'px',
					left: offset.left + 'px'
				});
			}
			*/
			
			function addListContainer() {
                var $list = $('<ul></ul>');
			    $input.after( $('<div class="ac_list_container"></div>').append($list) );
			    return $list; 
			}
			
			function processKey(e) {
				
				// handling up/down/escape requires results to be visible
				// handling enter/tab requires that AND a result to be selected
				if ((/27$|38$|40$/.test(e.keyCode) && $results.is(':visible')) ||
					(/^13$|^9$/.test(e.keyCode) && getCurrentResult())) {
		            
		            if (e.preventDefault)
		                e.preventDefault();
					if (e.stopPropagation)
		                e.stopPropagation();

	                e.cancelBubble = true;
	                e.returnValue = false;
				
					switch(e.keyCode) {
	
						case 38: // up
							prevResult();
							break;
				
						case 40: // down
							nextResult();
							break;
	
						case 9:  // tab
						case 13: // return
							selectCurrentResult();
							break;
							
						case 27: //	escape
							$results.hide();
							break;
	
					}
					
				} else if ($input.val().length != prevLength) {

					if (timeout) 
						clearTimeout(timeout);
					timeout = setTimeout(suggest, options.delay);
					prevLength = $input.val().length;
					
				}			
					
				
			}
			
			function suggestByStartValue(val)
			{
                if (val) {
                    val = $.trim(val);
                    cached = checkCache(val);
                    $input.val(val);
                    if (cached) {
                        displayItems(cached['items']);
                        return;
                    }
                }
                    
                $.get(options.source, {q: val}, function(txt) {

                    $results.hide();
                    
                    var items = parseTxt(txt, val);
                    
                    displayItems(items);
                    if (val !== undefined) {
                        addToCache(val, items, txt.length);
                    }
                    
                });
                        
                    
			}
			
			function suggest() {
			    	
				var q = $.trim($input.val());

				if (q.length >= options.minchars || q.length == 0) {
					
					cached = checkCache(q);
					
					if (cached) {
					
						displayItems(cached['items']);
						
					} else {
					
						$.get(options.source, {q: q}, function(txt) {

							$results.hide();
							
							var items = parseTxt(txt, q);
							
							displayItems(items);
							addToCache(q, items, txt.length);
							
						});
						
					}
					
				} else {
				
					$results.hide();
					
				}
					
			}
			
			
			function checkCache(q) {

				for (var i = 0; i < cache.length; i++)
					if (cache[i]['q'] == q) {
						cache.unshift(cache.splice(i, 1)[0]);
						return cache[0];
					}
				
				return false;
			
			}
			
			function addToCache(q, items, size) {

				while (cache.length && (cacheSize + size > options.maxCacheSize)) {
					var cached = cache.pop();
					cacheSize -= cached['size'];
				}
				
				cache.push({
					q: q,
					size: size,
					items: items
					});
					
				cacheSize += size;
			
			}
			
			function displayItems(items) {
				
				if (!items)
					return;
					
				if (!items.length) {
					$results.hide();
					return;
				}
				
				var html = '';
				for (var i = 0; i < items.length; i++)
					html += '<li' + (items[i]['key'] != '' ? ' id="s_'+ items[i]['key']+'"' : '' ) + '>' + items[i]['value'] + '</li>';

				$results.html(html).show();
				
				$results
					.children('li')
					.mouseover(function() {
						$results.children('li').removeClass(options.selectClass);
						$(this).addClass(options.selectClass);
					})
					.click(function(e) {
						e.preventDefault(); 
						e.stopPropagation();
						selectCurrentResult();
					});
							
			}
			
			function parseTxt(txt, q) {
				
				var items = [];
				var tokens = txt.split(options.delimiter);
				
				// parse returned data for non-empty items
				for (var i = 0; i < tokens.length; i++) {
					var data = $.trim(tokens[i]).split(options.dataDelimiter);
					if( data.length > 1 ) {
						token = data[0];
						key = data[1];
					}
					else {
						token = data[0]
						key = '';
					}
					
					if (token) {
					   if (q) {
						token = token.replace(
							new RegExp(q, 'ig'),
							function(q) { return '<span class="' + options.matchClass + '">' + q + '</span>' }
							);
						}
						items[items.length] = {'value':token,'key':key};
					}
				}
				
				return items;
			}
			
			function getCurrentResult() {
			
				if (!$results.is(':visible'))
					return false;
			
				var $currentResult = $results.children('li.' + options.selectClass);
				
				if (!$currentResult.length)
					$currentResult = false;
					
				return $currentResult;

			}
			
			function selectCurrentResult() {
			
				$currentResult = getCurrentResult();
			
				if ($currentResult) {
					$input.val($currentResult.text());
					$results.hide();

					if( $(options.dataContainer) ) {
						$(options.dataContainer).val($currentResult.attr('id').replace('s_',''));
					}
	
					if (options.onSelect) {
						options.onSelect.apply($input[0]);
					}
				}
			
			}
			
			function nextResult() {
			
				$currentResult = getCurrentResult();
			
				if ($currentResult)
					$currentResult
						.removeClass(options.selectClass)
						.next()
							.addClass(options.selectClass);
				else
					$results.children('li:first-child').addClass(options.selectClass);
			
			}
			
			function prevResult() {
			
				$currentResult = getCurrentResult();
			
				if ($currentResult)
					$currentResult
						.removeClass(options.selectClass)
						.prev()
							.addClass(options.selectClass);
				else
					$results.children('li:last-child').addClass(options.selectClass);
			
			}
		}
		
		$.fn.suggest = function(source, options) {
		
			if (!source)
				return;

		    options = options || {};
		    
		    options.onFocus = options.onFocus || false;
		    options.onBlur = options.onBlur || false;
		    options.onAutoSuggest = options.onAutoSuggest || false;
		    options.autoSuggest = options.autoSuggest || false;
		     
			options.source = source;
			options.delay = options.delay || 150;
			options.resultsClass = options.resultsClass || 'ac_results';
			options.selectClass = options.selectClass || 'ac_over';
			options.matchClass = options.matchClass || 'ac_match';
			options.minchars = options.minchars || 2;
			options.delimiter = options.delimiter || '\n';
			options.onSelect = options.onSelect || false;
			options.maxCacheSize = options.maxCacheSize || 65536;
			options.dataDelimiter = options.dataDelimiter || '\t';
			options.dataContainer = options.dataContainer || '#SuggestResult';
			options.attachObject = options.attachObject || null;
	
			this.each(function() {
				new $.suggest(this, options);
			});
	
			return this;
			
		};
		
	})(jQuery);
	
