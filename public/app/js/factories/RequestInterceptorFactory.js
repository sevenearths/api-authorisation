(function () {
    'use strict';

    angular.module('admin-app')
    .factory('RequestInterceptorFactory', function ($q, messagesFactory) {
        var factory = {
            showErrors: true,
            response: function (response) {
                if (
                    response.config.url.indexOf('ajax/') !== -1 &&
                    angular.isDefined(response.data.code) && (
                        angular.isDefined(response.data.data) ||
                        angular.isDefined(response.data.error) ||
                        angular.isDefined(response.data.validate)
                    ))
                {
                    // for create or patch
                    if(response.data.code == 201) {
                        messagesFactory.addSuccess('New entry created');
                    } else if(response.data.code == 204) {
                        messagesFactory.addSuccess('Entry deleted');
                    } else if(response.data.code == 404) {
                        messagesFactory.addError(response.data.error);
                    } else if(angular.isDefined(response.data.validate)) {
                        if(angular.isArray(response.data.validate)) {
                            var text = '<ul>';
                            angular.forEach(response.data.validate, function (value, key) {
                                text += '<li>' + value + '</li>';
                            });
                            text += '</ul>';
                        } else {
                            var text = response.data.validate;
                        }
                        messagesFactory.addError(text);
                    } else if(angular.isDefined(response.data.error)) {
                        messagesFactory.addError(response.data.error);
                    } else if(response.data.code == 200) {
                        //
                    } else {
                        messagesFactory.addError('Unrecognised response format');
                    }
                } else {
                    //console.log(response);
                    //messagesFactory.addError('Data is not in the correct format');
                }
                return $q.resolve(response);
            },
            // HTTP status codes 300 and up
            responseError: function (response) {
                messagesFactory.addError('Server Error');
                return $q.reject(response);
            }
        };
        return factory;
    });

})();