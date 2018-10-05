(function () {
    'use strict';

    angular.module('admin-app')
    .controller('messages-controller', function($scope, messagesFactory) {
        $scope.messages = messagesFactory.messages;

        $scope.closeMessage = function (index) {
            $scope.messages.splice(index, 1);
        };
    });
})();