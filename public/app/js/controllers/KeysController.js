(function () {
    'use strict';

    angular.module('admin-app')
    .controller('keys-controller', function($scope, $rootScope, $http, $log, $uibModal, messagesFactory) {

        $scope.animationsEnabled = true;

        $scope.form = null;

        $rootScope.$on('loading:progress', function (){
            $scope.disable_buttons = true;
        });

        $rootScope.$on('loading:finish', function (){
            $scope.disable_buttons = false;
        });

        setNewKeyData();
        setUpdateKeyData();
        setDeleteKeyData();
        getKeys();

        function setNewKeyData() {
            $scope.newKey = {'name': null, 'key': null, 'secret': null};
        }

        function setUpdateKeyData() {
            $scope.updateKey = {'id': null, 'name': null, 'key': null, 'secret': null};
        }

        function setUpdateKeySecretData() {
            $scope.updateSecret = {'id': null, 'secret': null};
        }

        function setDeleteKeyData() {
            $scope.deleteKey = {'id': null, 'name': null};
        }

        function getKeys() {
            delete $scope.keys;
            $http.get($rootScope.apiUrl + 'key')
            .then(
                function (response) {
                    if (response.data.code == 200) {
                        $scope.keys = response.data.data;
                    }
                },
                function (reject) {
                    // handled by RequestInterceptorFactory
                }
            );
        }

        $scope.addNewKey = function() {
            $http.post($rootScope.apiUrl + 'key', $scope.newKey)
            .then(
                function(response) {
                    if(response.data.code == 201) {
                        $scope.form = null;
                        setNewKeyData();
                        // done by RequestInterceptorFactory
                        //messagesFactory.addSuccess('Key Saved');
                        getKeys();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.editKey = function() {
            $http.patch($rootScope.apiUrl + 'key/'+$scope.updateKey.id, $scope.updateKey)
            .then(
                function(response) {
                    if(response.data.code == 200) {
                        $scope.form = null;
                        setUpdateKeyData();
                        messagesFactory.addSuccess('Key Updated');
                        getKeys();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.editSecret = function() {
            $http.patch($rootScope.apiUrl + 'key/secret/'+$scope.updateSecret.id, $scope.updateSecret)
            .then(
                function(response) {
                    if(response.data.code == 200) {
                        $scope.form = null;
                        setUpdateKeySecretData();
                        messagesFactory.addSuccess('Secret Updated');
                        getKeys();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.deleteKeyModal = function (key) {

            $scope.deleteKey = angular.copy(key);

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                component: 'deleteKeyModalComponent',
                resolve: {
                    key: function () {
                        return key;
                    }
                }
            });

            modalInstance.result.then(function (key) {
                //$log.info('index: ' + index);
                var index = $scope.keys.indexOf($scope.deleteKey);
                $scope.keys.splice(index, 1);
                //messagesFactory.addSuccess('Key ' + $scope.deleteKey['name'] + ' Deleted');
            }, function () {
                // When cancel is clicked
                delete $scope.deleteKey;
            });
        };

        $scope.populateUpdateKeyForm = function(index) {
            $scope.form = 'edit';
            $scope.updateKey = angular.copy($scope.keys[index]);
            delete $scope.updateKey.secret;
        };

        $scope.populateUpdateKeySecretForm = function(index) {
            $scope.form = 'secret';
            $scope.updateSecret = angular.copy($scope.keys[index]);
            delete $scope.updateSecret.key;
            $scope.updateSecret.secret = null;
        };

    });
})();