@extends('base')

@section('css')
    <link href="{{ url('/') }}/css/admin/keys.css" rel="stylesheet">
    <link href="{{ url('/') }}/css/admin/groups.css" rel="stylesheet">
    <link href="{{ url('/') }}/css/admin/users.css" rel="stylesheet">
    <link href="{{ url('/') }}/css/admin/urls.css" rel="stylesheet">
@endsection

@section('content')

    <div ng-app="admin-app">

        <div class="messages" ng-controller="messages-controller">
            <div uib-alert ng-repeat="message in messages" ng-class="'alert-' + (message.type || 'warning')" close="closeMessage($index)">
                <span ng-bind-html="message.text"></span>
            </div>
        </div>

        <h1>Admin</h1>

        <hr>

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#keys" data-toggle="tab">
                    Keys
                </a>
            </li>
            <li>
                <a href="#users" data-toggle="tab">
                    Users
                </a>
            </li>
            <li>
                <a href="#groups" data-toggle="tab">
                    Groups
                </a>
            </li>
            <li>
                <a href="#urls" data-toggle="tab">
                    Urls
                </a>
            </li>
        </ul>

        <div class="panel with-nav-tabs panel-default">

            <div class="panel-body">

                <div class="tab-content">

                    @include('admin/tabs/keys')

                    @include('admin/tabs/users')

                    @include('admin/tabs/groups')

                    @include('admin/tabs/urls')

                </div>  <!-- END: tab-content -->

            </div>  <!-- END: panel-body -->

        </div>  <!-- END: panel -->

    </div>  <!-- END: ng-app -->

@endsection

@section('script_angular')

    <script type="text/javascript">

        var app = angular.module('admin-app', ['ngAnimate', 'ui.bootstrap', 'ngSanitize', 'truncate', 'gm.dragDrop']);


            /////  CONFIG  /////

        app.config(function ($httpProvider) {
            $httpProvider.interceptors.push('RequestInterceptorFactory');
            $httpProvider.interceptors.push('httpInterceptor');
        });


            /////  RUN  /////

        app.run(function($rootScope) {
            $rootScope.apiUrl = 'admin/panel/ajax/';
        });

        //angular.bootstrap(document, ['admin-app']);


            /////  COMPONENTS  /////

        app.component('deleteKeyModalComponent', {
            templateUrl: 'deleteKey.html',
            bindings: {
                resolve: '<',
                close: '&',
                dismiss: '&'
            },
            controller: function ($http, $rootScope) {
                var $ctrl = this;

                $ctrl.$onInit = function () {
                    $ctrl.key = $ctrl.resolve.key;
                };

                $ctrl.ok = function () {
                    $http.delete($rootScope.apiUrl + 'key/'+$ctrl.key.id)
                    .then(
                        function(response) {
                            if(response.data.code == 204) {
                                //messagesFactory.addSuccess('Key Deleted');
                                $ctrl.close({$value: $ctrl.key});
                            }
                        },
                        function(reject) {
                            // handled by RequestInterceptorFactory
                            $ctrl.dismiss();
                        }
                    );
                };

                $ctrl.cancel = function () {
                    $ctrl.dismiss();
                };
            }
        });

        app.component('deleteUserModalComponent', {
            templateUrl: 'deleteUser.html',
            bindings: {
                resolve: '<',
                close: '&',
                dismiss: '&'
            },
            controller: function ($http, $rootScope) {
                var $ctrl = this;

                $ctrl.$onInit = function () {
                    $ctrl.user = $ctrl.resolve.user;
                };

                $ctrl.ok = function () {
                    $http.delete($rootScope.apiUrl + 'user/'+$ctrl.user.id)
                            .then(
                                    function(response) {
                                        if(response.data.code == 204) {
                                            // message set by RequestInterceptorFactory
                                            //messagesFactory.addSuccess('Key Deleted');
                                            $ctrl.close({$value: $ctrl.user});
                                        }
                                    },
                                    function(reject) {
                                        // handled by RequestInterceptorFactory
                                        $ctrl.dismiss();
                                    }
                            );
                };

                $ctrl.cancel = function () {
                    $ctrl.dismiss();
                };
            }
        });

        app.component('deleteGroupModalComponent', {
            templateUrl: 'deleteGroup.html',
            bindings: {
                resolve: '<',
                close: '&',
                dismiss: '&'
            },
            controller: function ($http, $rootScope) {
                var $ctrl = this;

                $ctrl.$onInit = function () {
                    $ctrl.group = $ctrl.resolve.group;
                };

                $ctrl.ok = function () {
                    $http.delete($rootScope.apiUrl + 'group/'+$ctrl.group.id)
                    .then(
                        function(response) {
                            if(response.data.code == 204) {
                                // message set by RequestInterceptorFactory
                                //messagesFactory.addSuccess('Key Deleted');
                                $ctrl.close({$value: $ctrl.group});
                            }
                        },
                        function(reject) {
                            // handled by RequestInterceptorFactory
                            $ctrl.dismiss();
                        }
                    );
                };

                $ctrl.cancel = function () {
                    $ctrl.dismiss();
                };
            }
        });

        app.component('deleteUrlModalComponent', {
            templateUrl: 'deleteUrl.html',
            bindings: {
                resolve: '<',
                close: '&',
                dismiss: '&'
            },
            controller: function ($http, $rootScope) {
                var $ctrl = this;

                $ctrl.$onInit = function () {
                    $ctrl.url = $ctrl.resolve.url;
                };

                $ctrl.ok = function () {
                    $http.delete($rootScope.apiUrl + 'url/'+$ctrl.url.id)
                    .then(
                        function(response) {
                            if(response.data.code == 204) {
                                // message set by RequestInterceptorFactory
                                //messagesFactory.addSuccess('Key Deleted');
                                $ctrl.close({$value: $ctrl.url});
                            }
                        },
                        function(reject) {
                            // handled by RequestInterceptorFactory
                            $ctrl.dismiss();
                        }
                    );
                };

                $ctrl.cancel = function () {
                    $ctrl.dismiss();
                };
            }
        });

    </script>
@endsection

@section('script_jquery')
    <script type="text/javascript">
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endsection
