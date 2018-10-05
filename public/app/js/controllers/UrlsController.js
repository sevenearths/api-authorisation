(function () {
    'use strict';

    angular.module('admin-app')
    .controller('urls-controller', function($scope, $rootScope, $http, $log, $filter, $uibModal, messagesFactory) {

        $scope.animationsEnabled = true;

        $scope.form = null;

        $rootScope.$on('loading:progress', function (){
            $scope.disable_buttons = true;
        });

        $rootScope.$on('loading:finish', function (){
            $scope.disable_buttons = false;
        });

        setNewUrlData();
        setUpdateUrlData();
        getGroups();

        function setNewUrlData() {
            $scope.newUrl = {'deny': false, 'url': null, 'group_id': null, 'method': 'ALL'};
        }

        function setUpdateUrlData() {
            $scope.updateUrl = {'id': null, 'deny': null, 'url': null, 'group_id': null, 'method': null};
        }

        $scope.getUrls = function() {
            if(angular.isUndefined($scope.urlGroup) == false) {
                delete $scope.urls;
                $http.get($rootScope.apiUrl + 'url/group/' + $scope.urlGroup.id)
                .then(
                    function (response) {
                        if (response.data.code == 200) {
                            $scope.urls = response.data.data;
                        }
                    },
                    function (reject) {
                        // handled by RequestInterceptorFactory
                    }
                );
            }
        };

        function getGroups() {
            delete $scope.groups;
            $http.get($rootScope.apiUrl + 'group')
            .then(
                function (response) {
                    if (response.data.code == 200) {
                        $scope.groups = response.data.data;
                    }
                },
                function (reject) {
                    // handled by RequestInterceptorFactory
                }
            );
        }

        $scope.addNewUrl = function() {
            // set this using the selected group
            $scope.newUrl.group_id = $scope.urlGroup.id;

            $http.post($rootScope.apiUrl + 'url', $scope.newUrl)
            .then(
                function(response) {
                    if(response.data.code == 201) {
                        $scope.form = null;
                        setNewUrlData();
                        // done by RequestInterceptorFactory
                        //messagesFactory.addSuccess('Url Saved');
                        $scope.getUrls();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.editUrl = function() {
            $http.patch($rootScope.apiUrl + 'url/'+$scope.updateUrl.id, $scope.updateUrl)
            .then(
                function(response) {
                    if(response.data.code == 200) {
                        $scope.form = null;
                        setUpdateUrlData();
                        messagesFactory.addSuccess('Url Updated');
                        $scope.getUrls();
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            )
        };

        $scope.deleteUrlModal = function (url) {

            $scope.deleteUrl = angular.copy(url);

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                component: 'deleteUrlModalComponent',
                resolve: {
                    url: function () {
                        return url;
                    }
                }
            });

            modalInstance.result.then(function (url) {
                //$log.info('index: ' + index);
                var index = $scope.urls.indexOf($scope.deleteUrl);
                $scope.urls.splice(index, 1);
                //messagesFactory.addSuccess('Url ' + $scope.deleteUrl['name'] + ' Deleted');
            }, function () {
                // When cancel is clicked
                delete $scope.deleteUrl;
            });
        };

        $scope.populateUpdateUrlForm = function(url) {
            $scope.form = 'edit';
            $scope.updateUrl = url;
        };


        /* Sortable functions */

        $scope.onHover = function(item) {
            return function(dragItem, mouseEvent) {
                if(item != dragItem)
                    dragItem.order = item.order + ((mouseEvent.offsetY || -1) > 0 ? 0.5 : -0.5)
            }
        };

        $scope.reorder = function reorder() {
            var _orderedItems = $filter('orderBy')($scope.urls, 'order');
            for(var i = 0; i < _orderedItems.length; i++) {
                _orderedItems[i].number = _orderedItems[i].order = i + 1;
            }

            var urls = [];
            angular.forEach($filter('orderBy')($scope.urls, 'order'), function (value, key) {
                urls.push(value.id);
            });

            delete $scope.urls;
            $http.patch($rootScope.apiUrl + 'url/order/'+$scope.urlGroup.id, {'order': urls})
            .then(
                function(response) {
                    if(response.data.code == 200) {
                        $scope.urls = response.data.data;
                    }
                },
                function(reject) {
                    // handled by RequestInterceptorFactory
                }
            );
        };

        $scope.getDropHandler = function(category) {
            return function(dragOb) {
                if(category.items.indexOf(dragOb.item) < 0) {
                    dragOb.category.items.splice(dragOb.category.items.indexOf(dragOb.item), 1);
                    category.items.push(dragOb.item);
                    return true;  // Returning truthy value since we're modifying the view model
                }
            }
        };

        $scope.methodTableDots = function(url, method) {
            if (url.method == method) {
                var color;
                if (method == 'get') { color = 'info'; }
                if (method == 'post') { color = 'secondary'; }
                if (method == 'patch') { color = 'warning'; }
                if (method == 'delete') { color = 'danger'; }
                return 'fa-circle ' + color;
            } else if (url.method == 'ALL') {
                return 'fa-circle default'
            } else {
                return 'fa-circle-thin deactivated'
            }
        }

    });
})();