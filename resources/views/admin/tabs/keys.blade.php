<div class="tab-pane active" id="keys" ng-controller="keys-controller">

    <!-- Add Key -->

    <div id="add-key" class="well well-lg" ng-show="form == 'new'" ng-cloak>
        <form ng-submit="addNewKey()">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newKey.name" placeholder="Name">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newKey.key" placeholder="Key">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="newKey.secret" placeholder="Secret">
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
                        Save New Key
                    </span>
                </button>
            </div>
        </form>
    </div>

    <div class="right-align" ng-hide="form == 'new' || form == 'edit'">
        <button type="button" class="btn btn-link" ng-click="form = 'new'">
            Add Key
        </button>
    </div>


    <!-- Edit Key -->

    <div id="edit-key" class="well well-lg" ng-show="form == 'edit'" ng-cloak>
        <form ng-submit="editKey()">
            <input type="hidden" ng-model="updateKey.id">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateKey.name" placeholder="Name">
            </div>
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateKey.key" placeholder="Key">
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
                        Update Key
                    </span>
                </button>
            </div>
        </form>
    </div>


    <!-- Edit Secret -->

    <div id="edit-secret" class="well well-lg" ng-show="form == 'secret'" ng-cloak>
        <form ng-submit="editSecret()">
            <h3>Update secret for <span ng-bind="updateSecret.name"></span></h3>
            <input type="hidden" ng-model="updateSecret.id">
            <div class="form-group">
                <input class="form-control" id="name_input" ng-model="updateSecret.secret" placeholder="Secret">
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
                        Update Secret
                    </span>
                </button>
            </div>
        </form>
    </div>


    <!-- Delete Key -->

    <script type="text/ng-template" id="deleteKey.html">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">
                Delete Key
            </h3>
        </div>
        <div class="modal-body" id="modal-body">
            Are you sure you want to delete the key:
            <div style="margin: 1em">
                <code ng-bind="$ctrl.key.name"></code>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" type="button" ng-click="$ctrl.cancel()">Cancel</button>
            <button class="btn btn-success" type="button" ng-click="$ctrl.ok()" ng-class="{'disabled':disable_buttons}">
                <span ng-show="disable_buttons">
                    <i class="fa fa-spinner fa-pulse fa-fw"></i>
                    Deleting Key...
                </span>
                <span ng-hide="disable_buttons">
                    Delete Key <span ng-bind="$ctrl.key.name"></span>
                </span>
            </button>
        </div>
    </script>


    <!-- Table -->

    <h1 ng-hide="keys">Loading...</h1>

    <table class="table table-striped" ng-show="keys" ng-cloak>
        <thead>
        <tr>
            <th>Name</th>
            <th>Key</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr ng-repeat="key in keys track by $index">
            <td ng-bind="key.name"></td>
            <td ng-bind="key.key"></td>
            <td class="actions">
                <button type="button" class="btn btn-default btn-xs" ng-click="populateUpdateKeyForm($index)" uib-tooltip="Edit Key and Name">
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-default btn-xs" ng-click="populateUpdateKeySecretForm($index)" uib-tooltip="Edit Secret">
                    <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-danger btn-xs" ng-click="deleteKeyModal(key)" uib-tooltip="Delete Key">
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                </button>
            </td>
        </tr>
        </tbody>
    </table>

</div>