(function () {
    'use strict';

    angular.module('admin-app')
    .factory('messagesFactory', function ($rootScope) {
        var sharedMessages = {
            messages: [],
            clearMessages: function() {
                sharedMessages.messages = [];
            },
            addSuccess: function(text) {
                sharedMessages.addMessage('success', text);
            },
            addInfo: function(text) {
                sharedMessages.addMessage('info', text);
            },
            addWarning: function(text) {
                sharedMessages.addMessage('warning', text);
            },
            addError: function(text) {
                sharedMessages.addMessage('danger', text);
            },
            addMessage: function(type, text) {
                sharedMessages.messages.push({type: type, text: text});
                //sharedMessages.messages = [{'type': type, 'text': text}];
            }
        };
        return sharedMessages;
    });
})();