<div class="tab-pane" id="urls" ng-controller="urls-controller">

    <!-- Add Url -->

    <div id="add-url" class="well well-lg" ng-show="form == 'new'" ng-cloak>
        <form>
            <div class="form-group">
                {{--<input class="form-control" id="name_input" ng-model="newUrl.url" placeholder="Url">--}}
                <div class="input-group">
                    <div class="input-group-btn">
                        <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                ng-class="{All:'btn-default', get:'btn-info', post:'btn-secondary', patch:'btn-warning', delete:'btn-danger'}[newUrl.method]">
                            <span ng-bind="newUrl.method"></span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a ng-click="newUrl.method = 'ALL'">ALL</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a ng-click="newUrl.method = 'get'">get</a></li>
                            <li><a ng-click="newUrl.method = 'post'">post</a></li>
                            <li><a ng-click="newUrl.method = 'patch'">patch</a></li>
                            <li><a ng-click="newUrl.method = 'delete'">delete</a></li>
                        </ul>
                    </div>
                    <input type="text" class="form-control" aria-label="url" ng-model="newUrl.url">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                ng-class="{false:'btn-success', true:'btn-danger'}[newUrl.deny]">
                            <span ng-bind="newUrl.deny ? 'Deny' : 'Allow'"></span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a ng-click="newUrl.deny = false">Allow</a></li>
                            <li><a ng-click="newUrl.deny = true">Deny</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="right-align">
                <button type="button" class="btn btn-danger" ng-class="{'disabled':disable_buttons}" ng-click="form = null">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" ng-class="{'disabled':disable_buttons}">
                    <span ng-show="disable_buttons">
                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                        Saving...
                    </span>
                    <span ng-hide="disable_buttons" ng-click="addNewUrl()">
                        Save New Url
                    </span>
                </button>
            </div>
        </form>
    </div>


    <!-- Edit Url -->

    <div id="edit-url" class="well well-lg" ng-show="form == 'edit'" ng-cloak>
        <form ng-submit="editUrl()">
            <input type="hidden" ng-model="updateUrl.id">
            <div class="align-btn-group">
                <div class="btn-group">
                    <label class="btn btn-success" ng-model="updateUrl.deny" uib-btn-radio="false">
                        Grant
                    </label>
                    <label class="btn btn-danger" ng-model="updateUrl.deny" uib-btn-radio="true">
                        Deny
                    </label>
                </div>
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateUrl.url" placeholder="Url">
            </div>
            <div class="form-group">
                <select class="form-control" ng-model="updateUrl.group_id">
                    <option ng-repeat="group in groups" value="@{{ group.id }}" ng-selected="updateUrl.group_id == group.id">
                        @{{ group.name }} (@{{ group.urls_count }})
                    </option>
                </select>
            </div>
            <div class="right-align">
                <button type="button" class="btn btn-danger" ng-class="{'disabled':disable_buttons}" ng-click="form = null">
                    Cancel
                </button>
                <button type="submit" class="btn btn-success" ng-class="{'disabled':disable_buttons}">
                    <span ng-show="disable_buttons">
                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                        Updating...
                    </span>
                    <span ng-hide="disable_buttons">
                        Update Url
                    </span>
                </button>
            </div>
        </form>
    </div>


    <!-- Delete Url -->

    <script type="text/ng-template" id="deleteUrl.html">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">
                Delete Url
            </h3>
        </div>
        <div id="delete-url" class="modal-body" id="modal-body">
            Are you sure you want to delete the url:<br>
            {{--styling won't work in urls.css for some reason--}}
            <div class="url" style="margin: 1em">
                <code ng-bind="$ctrl.url.url"></code>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" type="button" ng-click="$ctrl.cancel()">Cancel</button>
            <button class="btn btn-success" type="button" ng-click="$ctrl.ok()">
                Delete Url
            </button>
        </div>
    </script>


    <!-- Table -->

    <div class="form-group">
        <select class="form-control"
                ng-options="group.name for group in groups"
                ng-model="urlGroup"
                ng-change="getUrls()"></select>
    </div>

    <div class="right-align" ng-hide="form == 'new' || form == 'edit' || !urlGroup">
        <button type="button" class="btn btn-link" ng-click="form = 'new'">
            Add Url
        </button>
    </div>

    <h1 ng-show="!urls && urlGroup">Loading...</h1>

    <div class='list-group'>
        <div class="list-group-item" ng-class="url.deny ? 'deny' : 'allow'"
             ng-repeat="url in urls | orderBy:'order'"
             gm-draggable='url'
             gm-on-invalid-drop='reset'
             gm-on-drop='reorder'
             gm-on-hover='onHover(url)'>
            <span class='glyphicon glyphicon-menu-hamburger gm-drag-handle'></span>
            <i class="fa" ng-class="methodTableDots(url, 'get')" aria-hidden="true"></i>
            <i class="fa" ng-class="methodTableDots(url, 'post')" aria-hidden="true"></i>
            <i class="fa" ng-class="methodTableDots(url, 'patch')" aria-hidden="true"></i>
            <i class="fa" ng-class="methodTableDots(url, 'delete')" aria-hidden="true"></i>
            <span class="url-text">@{{ url.url | characters:100 }}</span>
            <div class="pull-right">
                <button type="button" class="btn btn-link btn-xs" ng-click="populateUpdateUrlForm(url)" uib-tooltip="Edit Url">
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-link btn-xs" ng-click="deleteUrlModal(url)" uib-tooltip="Delete Url">
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                </button>
            </div>
        </div>
        <div class="list-group-item deny" ng-show="urls">
            <!-- The below in-line style is a big FUCK YOU to code reviews -->
            <span class='glyphicon glyphicon-menu-hamburger gm-drag-handle' style="opacity: 0"></span>
            (deny all)
        </div>
    </div>

    <div id="legend">
        <span><i class="fa fa-circle info"></i> = get</span>
        <span><i class="fa fa-circle secondary"></i> = post</span>
        <span><i class="fa fa-circle warning"></i> = patch</span>
        <span><i class="fa fa-circle danger"></i> = delete</span>
    </div>

</div>