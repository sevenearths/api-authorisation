<div class="tab-pane" id="groups" ng-controller="groups-controller">

    <!-- Add Group -->

    <div id="add-group" class="well well-lg" ng-show="form == 'new'" ng-cloak>
        <form ng-submit="addNewGroup()">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newGroup.name" placeholder="Name">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newGroup.description" placeholder="Description">
            </div>
            <div class="right-align">
                <button type="button" class="btn btn-danger" ng-class="{'disabled':disable_buttons}" ng-click="form = null">
                    Cancel
                </button>
                <button type="submit" class="btn btn-success" ng-class="{'disabled':disable_buttons}">
                    <span ng-show="disable_buttons">
                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                        Saving...
                    </span>
                    <span ng-hide="disable_buttons">
                        Save New Group
                    </span>
                </button>
            </div>
        </form>
    </div>

    <div class="right-align" ng-hide="form == 'new' || form == 'edit'">
        <button type="button" class="btn btn-link" ng-click="form = 'new'">
            Add Group
        </button>
    </div>


    <!-- Edit Group -->

    <div id="edit-group" class="well well-lg" ng-show="form == 'edit'" ng-cloak>
        <form ng-submit="editGroup()">
            <input type="hidden" ng-model="updateGroup.id">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateGroup.name" placeholder="Name">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateGroup.description" placeholder="Description">
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
                        Update Group
                    </span>
                </button>
            </div>
        </form>
    </div>


    <!-- Group Users -->

    <div id="group-users" class="well well-lg" ng-show="form == 'users'" ng-cloak>
        <div class="row">
            <div class="col-xs-12">
                <h3 ng-bind="groupSelected.name"></h3>
            </div>
            <div class="col-xs-6">
                <h4>Available Users</h4>
                <h2 ng-hide="groupUsersAvailable">Loading...</h2>
                <select size="12" ng-model="addUser" ng-show="groupUsersAvailable">
                    <option ng-repeat="user in groupUsersAvailable" value="@{{ user.id }}" ng-click="addUserToGroup(user)">
                        @{{ user.name }}
                    </option>
                </select>
            </div>
            <div class="col-xs-6">
                <h4>Group Users</h4>
                <h2 ng-hide="groupUsers">Loading...</h2>
                <select size="12" ng-model="removeUser" ng-show="groupUsers">
                    <option ng-repeat="user in groupUsers" value="@{{ user.id }}" ng-click="removeUserFromGroup(user)">
                        @{{ user.name }}
                    </option>
                </select>
            </div>
        </div>
        <div class="right-align">
            <button type="button" class="btn btn-danger" ng-click="deleteGroupUsersArrays()">
                Close
            </button>
        </div>
    </div>


    <!-- Delete Group -->

    <script type="text/ng-template" id="deleteGroup.html">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">
                Delete Group
            </h3>
        </div>
        <div class="modal-body" id="modal-body">
            Are you sure you want to delete the Group:
            <div style="margin: 1em">
                <code ng-bind="$ctrl.group.name"></code>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" type="button" ng-click="$ctrl.cancel()">Cancel</button>
            <button class="btn btn-success" type="button" ng-click="$ctrl.ok()">
                Delete Group <span ng-bind="$ctrl.group.name"></span>
            </button>
        </div>
    </script>


    <!-- Table -->

    <h1 ng-hide="groups">Loading...</h1>

    <table class="table table-striped" ng-show="groups" ng-cloak>
        <thead>
        <tr>
            <th>Name</th>
            <th>Descriptions</th>
            <th>No. Users</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr ng-repeat="group in groups track by $index">
            <td ng-bind="group.name"></td>
            <td ng-bind="group.description"></td>
            <td id="number_of_users" ng-bind="group.users_count"></td>
            <td class="actions">
                <button type="button" class="btn btn-default btn-xs" ng-click="populateUpdateGroupForm($index)" uib-tooltip="Edit Group">
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-default btn-xs" ng-click="populateUpdateGroupUsersForm($index)" uib-tooltip="Show Group Users">
                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-danger btn-xs" ng-click="deleteGroupModal(group)" uib-tooltip="Delete Group">
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                </button>
            </td>
        </tr>
        </tbody>
    </table>

</div>