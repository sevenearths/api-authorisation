(function () {
    'use strict';

    angular.module('admin-app')
    .controller('users-controller', function($scope, $rootScope, $http, $log, $uibModal, messagesFactory) {

        $scope.animationsEnabled = true;

        $scope.form = null;

        $rootScope.$on('loading:progress', function (){
            $scope.disable_buttons = true;
        });

        $rootScope.$on('loading:finish', function (){
            $scope.disable_buttons = false;
        });

        setNewUserData();
        setUpdateUserData();
        setDeleteUserData();
        getUsers();

        function setNewUserData() {
            $scope.newUser = {'name': null, 'email': null};
        }

        function setUpdateUserData() {
            $scope.updateUser = {'id': null, 'name': null, 'email': null};
        }

        function setDeleteUserData() {
            $scope.deleteUser = {'id': null, 'name': null};
        }

        function getUsers() {
            delete $scope.users;
            $http.get($rootScope.apiUrl + 'user')
            .then(
                function (response) {
                    if (response.data.code == 200) {
                        $scope.users = response.data.data;
                    }
                },
                function (reject) {
                    // handled by RequestInterceptorFactory
                }
            );
        }

        $scope.addNewUser = function() {
            $http.post($rootScope.apiUrl + 'user', $scope.newUser)
            .then(
                function(response) {
                    if(response.data.code == 201) {
                        $scope.form = null;
                        setNewUserData();
                        // done by RequestInterceptorFactory
                        //messagesFactory.addSuccess('User Saved');
                        getUsers();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.editUser = function() {
            $http.patch($rootScope.apiUrl + 'user/'+$scope.updateUser.id, $scope.updateUser)
            .then(
                function(response) {
                    if(response.data.code == 200) {
                        $scope.form = null;
                        setUpdateUserData();
                        messagesFactory.addSuccess('User Updated');
                        getUsers();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.deleteUserModal = function (user) {

            $scope.deleteUser = angular.copy(user);

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                component: 'deleteUserModalComponent',
                resolve: {
                    user: function () {
                        return user;
                    }
                }
            });

            modalInstance.result.then(function (user) {
                //$log.info('index: ' + index);
                var index = $scope.users.indexOf($scope.deleteUser);
                $scope.users.splice(index, 1);
                //messagesFactory.addSuccess('User ' + $scope.deleteUser['name'] + ' Deleted');
            }, function () {
                // When cancel is clicked
                delete $scope.deleteUser;
            });
        };

        $scope.populateUpdateUserForm = function(index) {
            $scope.form = 'edit';
            $scope.updateUser = angular.copy($scope.users[index]);
        };

    });
})();