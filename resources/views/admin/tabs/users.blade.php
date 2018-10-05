<div class="tab-pane" id="users" ng-controller="users-controller">

    <!-- Add User -->

    <div id="add-user" class="well well-lg" ng-show="form == 'new'" ng-cloak>
        <form ng-submit="addNewUser()">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newUser.name" placeholder="Name">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newUser.email" placeholder="Email">
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
                        Save New User
                    </span>
                </button>
            </div>
        </form>
    </div>

    <div class="right-align" ng-hide="form == 'new' || form == 'edit'">
        <button type="button" class="btn btn-link" ng-click="form = 'new'">
            Add User
        </button>
    </div>


    <!-- Edit User -->

    <div id="edit-user" class="well well-lg" ng-show="form == 'edit'" ng-cloak>
        <form ng-submit="editUser()">
            <input type="hidden" ng-model="updateUser.id">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateUser.name" placeholder="Name">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateUser.email" placeholder="Email">
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
                        Update User
                    </span>
                </button>
            </div>
        </form>
    </div>


    <!-- Delete User -->

    <script type="text/ng-template" id="deleteUser.html">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">
                Delete User
            </h3>
        </div>
        <div class="modal-body" id="modal-body">
            Are you sure you want to delete the user:
            <div style="margin: 1em;">
                <code ng-bind="$ctrl.user.name"></code>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" type="button" ng-click="$ctrl.cancel()">Cancel</button>
            <button class="btn btn-success" type="button" ng-click="$ctrl.ok()" ng-class="{'disabled':disable_buttons}">
                <span ng-show="disable_buttons">
                    <i class="fa fa-spinner fa-pulse fa-fw"></i>
                    Deleting User...
                </span>
                <span ng-hide="disable_buttons">
                    Delete User <span ng-bind="$ctrl.user.name"></span>
                </span>
            </button>
        </div>
    </script>


    <!-- Table -->

    <h1 ng-hide="users">Loading...</h1>

    <table class="table table-striped" ng-show="users" ng-cloak>
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr ng-repeat="user in users track by $index">
            <td ng-bind="user.name"></td>
            <td ng-bind="user.email"></td>
            <td class="actions">
                <button type="button" class="btn btn-default btn-xs" ng-click="populateUpdateUserForm($index)" uib-tooltip="Edit User">
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-danger btn-xs" ng-click="deleteUserModal(user)" uib-tooltip="Delete User">
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                </button>
            </td>
        </tr>
        </tbody>
    </table>

</div>