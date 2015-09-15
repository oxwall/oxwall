OW_Components_DragAndDropAjaxHandler = function(urlResponder, sharedData) {
	this.ajax = new OW_Component_Ajax(urlResponder);
	this.sharedData = sharedData;
};

OW_Components_DragAndDropAjaxHandler.prototype = {

	getParams: function(params) {
		return $.extend({}, this.sharedData, params);
	},

	changeState: function( sectionName, itemStack) {
		this.ajax.addRequest('saveComponentPlacePositions', this.getParams({
			stack: itemStack,
			section: sectionName
		}));
	},

        clone: function(section, stack, id, success) {
            this.ajax.send('cloneComponent', this.getParams({
                section: section,
                componentId: id,
                stack: stack
            }), success);
        },

        remove: function(id) {
             this.ajax.send('deleteComponent', this.getParams({
                componentId: id
            }));
        },

        loadSettings: function(id, successFunction) {
             this.ajax.send('getSettingsMarkup', this.getParams({
                componentId: id
            }), successFunction);
        },

        saveSettings: function(id, settings , successFunction) {
             this.ajax.addRequest('saveSettings', this.getParams({
                componentId: id,
                settings: settings
            }), successFunction);
        },

        saveScheme: function(scheme , successFunction) {
             this.ajax.send('savePlaceScheme', this.getParams({
                scheme: scheme
            }), successFunction);
        },

        moveToPanel: function(id, successFunction) {
            this.ajax.addRequest('moveComponentToPanel', this.getParams({
                componentId: id
            }), successFunction);
        },

        complete: function(successFunction) {
            this.ajax.sendQueue(successFunction);
        },

        reload: function( id, renderView, successFunction )
        {
            this.ajax.addRequest('reload', this.getParams({
                componentId: id,
                render: renderView
            }), successFunction);
        },

		allowCustomize: function( state, successFunction ) {
			 this.ajax.send('allowCustomize', this.getParams({
				 	state: state
	           }), successFunction);
		},

		reset: function( successFunction ) {
			 this.ajax.send('reset', this.getParams({}), successFunction);
		}
};