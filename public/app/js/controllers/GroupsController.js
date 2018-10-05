(function () {
    'use strict';

    angular.module('admin-app')
    .controller('groups-controller', function($scope, $rootScope, $http, $log, $uibModal, messagesFactory) {

        $scope.animationsEnabled = true;

        $scope.form = null;

        $rootScope.$on('loading:progress', function (){
            $scope.disable_buttons = true;
        });

        $rootScope.$on('loading:finish', function (){
            $scope.disable_buttons = false;
        });

        setNewGroupData();
        setUpdateGroupData();
        setDeleteGroupData();
        getGroups();

        function setNewGroupData() {
            $scope.newGroup = {'name': null, 'description': null};
        }

        function setUpdateGroupData() {
            $scope.updateGroup = {'id': null, 'name': null, 'description': null};
        }

        function setDeleteGroupData() {
            $scope.deleteGroup = {'id': null, 'name': null};
        }

        function getGroups() {
            delete $scope.groups;
            $scope.disable_buttons = true;
            $http.get($rootScope.apiUrl + 'group')
            .then(
                function (response) {
                    $scope.disable_buttons = false;
                    if (response.data.code == 200) {
                        $scope.groups = response.data.data;
                    }
                },
                function (reject) {
                    $scope.disable_buttons = false;
                    // handled by RequestInterceptorFactory
                }
            );
        }

        function getGroupUsersAvailable() {
            delete $scope.groupUsersAvailable;
            $http.get($rootScope.apiUrl + 'group/' + $scope.groupSelected.id + '/user/available')
            .then(
                function (response) {
                    if (response.data.code == 200) {
                        $scope.groupUsersAvailable = response.data.data;
                    }
                },
                function (reject) {
                    // handled by RequestInterceptorFactory
                }
            );
        }

        function getGroupUsers() {
            delete $scope.groupUsers;
            $http.get($rootScope.apiUrl + 'group/' + $scope.groupSelected.id + '/user')
            .then(
                function (response) {
                    if (response.data.code == 200) {
                        $scope.groupUsers = response.data.data;
                    }
                },
                function (reject) {
                    // handled by RequestInterceptorFactory
                }
            );
        }

        // run after the data has changed
        function populateGroupUsersSelectionLists() {
            getGroupUsers();
            getGroupUsersAvailable();
        }

        $scope.addNewGroup = function() {
            $scope.disable_buttons = true;
            $http.post($rootScope.apiUrl + 'group', $scope.newGroup)
            .then(
                function(response) {
                    $scope.disable_buttons = false;
                    if(response.data.code == 201) {
                        $scope.form = null;
                        setNewGroupData();
                        // done by RequestInterceptorFactory
                        //messagesFactory.addSuccess('Group Saved');
                        getGroups();
                    }
                },
                function(reject) {
                    $scope.disable_buttons = false;
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.editGroup = function() {
            $scope.disable_buttons = true;
            $http.patch($rootScope.apiUrl + 'group/'+$scope.updateGroup.id, $scope.updateGroup)
            .then(
                function(response) {
                    $scope.disable_buttons = false;
                    if(response.data.code == 200) {
                        $scope.form = null;
                        setUpdateGroupData();
                        messagesFactory.addSuccess('Group Updated');
                        getGroups();
                    }
                },
                function(reject) {
                    $scope.disable_buttons = false;
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.deleteGroupModal = function (group) {

            $scope.deleteGroup = angular.copy(group);

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                component: 'deleteGroupModalComponent',
                resolve: {
                    group: function () {
                        return group;
                    }
                }
            });

            modalInstance.result.then(function (group) {
                //$log.info('index: ' + index);
                var index = $scope.groups.indexOf($scope.deleteGroup);
                $scope.groups.splice(index, 1);
                //messagesFactory.addSuccess('Group ' + $scope.deleteGroup['name'] + ' Deleted');
            }, function () {
                // When cancel is clicked
                delete $scope.deleteGroup;
            });
        };

        $scope.addUserToGroup = function(user) {
            $http.post($rootScope.apiUrl + 'group/' + $scope.groupSelected.id + '/user', {'user_id': user.id})
            .then(
                function(response) {
                    if(response.data.code == 201) {
                        populateGroupUsersSelectionLists()
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.removeUserFromGroup = function(user) {
            $http.delete($rootScope.apiUrl + 'group/' + $scope.groupSelected.id + '/user/' + user.id)
            .then(
                function(response) {
                    if(response.data.code == 204) {
                        populateGroupUsersSelectionLists()
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        // called from the close button
        $scope.deleteGroupUsersArrays = function() {
            $scope.form = null;
            delete $scope.groupSelected;
            delete $scope.groupUsers;
            delete $scope.groupUsersAvailable;
        };

        $scope.populateUpdateGroupForm = function(index) {
            $scope.form = 'edit';
            $scope.updateGroup = angular.copy($scope.groups[index]);
        };

        $scope.populateUpdateGroupUsersForm = function(index) {
            $scope.form = 'users';
            $scope.groupSelected = angular.copy($scope.groups[index]);

            getGroupUsers();
            getGroupUsersAvailable();
        };

    });
})();