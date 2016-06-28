var OW_Component_Ajax = function(urlResponder) {
	this.urlResponder = urlResponder;
	this.queue = [];
};

OW_Component_Ajax.prototype = {
	
	addRequest: function(command, data, success) {
		var request = {
			command: command,
			data: data
		};
		this.queue.push({
			request: request,
			success: success
		});
	},
		
	sendQueue: function(successCallback) {
		
		if ( !this.queue.length ) {
			return;
		}
		
		var request = [];
		var success = {};
		
		for (var i in this.queue) {
			this.queue[i].request.commandId = i;
			request.push(this.queue[i].request);
			if (this.queue[i].success) {
				success[i] = this.queue[i].success;
			}
		}
		
		this._ajaxSend(request, success, successCallback)
                
		this.queue = [];
	},

        _ajaxSend: function(request, callbackStack, resultCallback) {
            var jsonRequest = encodeURIComponent( JSON.stringify(request) );
		
            var ajaxOptions = {
                    url: this.urlResponder,
                    dataType: 'json',
                    type: 'POST',
                    data: {request: jsonRequest},
                    success: function(result) {
            				if ( typeof result !== "object" ) {
            					var fb = new OW_FloatBox({
            						$title: 'Incorrect responce',
            						$contents: result.toString(),
            						width: '100%'
            					});
            				}
                            var sharedData = {};
                            $.each(result.responseQueue, function(commandID, resultData) {
                                    if (callbackStack[commandID]) {
                                    	callbackStack[commandID](resultData, sharedData);
                                    }
                            });

                            $.each(result.debug, function() {
                                    if (console) {
                                            console.log(this);
                                    } else {
                                            alert(JSON.stringify(this));
                                    }
                            });
                            
                            if (resultCallback) {
                                    resultCallback(result, sharedData);
                            }
            		},
            		error: function(r) {
            			var fb = new OW_FloatBox({
    						$title: 'Incorrect responce',
    						$contents: r.responseText,
    						width: '100%'
    					});
            		}
            	};

                $.ajax(ajaxOptions);
        },

        send: function(command, data, success) {
            
            var request = {
                command: command,
                data: data,
                commandId: 0
            };

            this._ajaxSend([request],[success] );
        }
}
