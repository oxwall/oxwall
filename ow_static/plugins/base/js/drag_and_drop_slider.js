(function($){
	
	$.fn.schemeSwitcher = function(options) {
		var $node = $(this);
		var $handle = $node.find('.ow_dnd_slider_handle'); 
		var $markers = $node.find(".ow_dnd_slider_marker");
		var $activeMarker = $node.find('.ow_dnd_slider_item .current');
		var markersPositionMap = [];
		var moveTo = function( $toMarker ) {
			prePosition = $toMarker.outerWidth() + $toMarker.position().left - ( $handle.outerWidth() / 2 ) -2;
			$handle.css('left', prePosition);
		};
		
		var changeDelegate = function() {
			
			var $this = $(this); 
	        moveTo($this);
	        
	        if ( $this.hasClass('current') ) {
	        	return;
	        }
	        var lastMarker = $node.find('.current').removeClass('current').get(0);
	        $this.addClass('current');
	        $activeMarker = $this;
	        if ( options.change !== undefined ) {
	        	var event = {
	        		marker: this,
	        		markerPoint: this.pointer,
	        		lastMarker: lastMarker  
	        	};
	        	
	        	options.change.apply($node.get(0), [event]);
	        }
		};
		
		var updateDelegate = function() {
			
			if ( options.update !== undefined ) {
	        	var event = {
	        		marker: this
	        	};
	        	
	        	options.update.apply($node.get(0), [event]);
	        }
		};
				
		if ( ! $activeMarker.length ) {
			$activeMarker = $( $markers.get(0) ).addClass('current');
		}
		moveTo($activeMarker);
		
		$handle.draggable({
		    containment: 'parent',
		    axis: 'x',
		    
		    start: function() {
				$(this).addClass('ow_dnd_slider_in_move');
			},
			stop: function() {
				$(this).removeClass('ow_dnd_slider_in_move');
				updateDelegate.apply($activeMarker.get(0));
			},
			helper: function(){
		        return $(this).clone(false).addClass('ow_dnd_slider_helper');  
		    }
		});
		
		$markers.droppable({
		    tolerance: 'touch',
		    accept: '.ow_dnd_slider_handle',
			over: changeDelegate
		});
		
		for (var i = 0; i < $markers.length; i++) {
			var $this = $($markers[i]);
			
			var $pointer = $('<div class="ow_dnd_slider_marker_point"></div>');
			$node.append($pointer);
			var left = $this.position().left + $this.outerWidth() - 2;
			$pointer.css('left', left);
			this.pointer = $pointer.get(0);
			
			var np = $node.position(); 
			var markerX = left + np.left;
			var nml = $markers[i+1] ? np.left + $($markers[i+1]).position().left : np.left + $node.outerWidth();
			var x1, x2;
			if ( i == 0) {
				x1 = np.left;
			} else {
				x1 = markersPositionMap[i-1].x2;
			}
			
			if ($markers[i+1]) {
				x2 = np.left + left + ( $($markers[i+1]).position().left - left ) / 2;
			} else {
				x2 = np.left + $node.outerWidth();
			}
			
			markersPositionMap.push({
				x1: x1,
				x2: x2,
				m: $markers[i]
			});
		}

		
		$node.click(function(e){
			var mpm = markersPositionMap;
			
			for( var i=0; i < mpm.length; i++ ) {
				if ( e.clientX > mpm[i].x1 && e.clientX < mpm[i].x2 ) {
					changeDelegate.apply(mpm[i].m);
					updateDelegate.apply(mpm[i].m);
					return;
				}
			}
		});
		
		return this;
	}
	
})(jQuery)