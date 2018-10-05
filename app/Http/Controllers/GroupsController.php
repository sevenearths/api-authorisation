<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Group;
use App\Traits\CacheTrait;

// This class needs to be created
use ApiResponse\ApiResponseClass;

class GroupsController extends Controller
{
    use CacheTrait;
    
    private $response;
    private $user;
    private $group;
    private $validation = [
        'name' => 'required|min:5|unique:groups,name',
        'description' => 'required|min:12',
    ];

    public function __construct(ApiResponseClass $apiResponseClass, Group $group, User $user)
    {
        $this->response = $apiResponseClass;
        $this->group = $group;
        $this->user = $user;
    }

    public function getAllGroups(Request $request)
    {
        return $this->response->standard($this->group->all());
    }

    public function postAddNewGroup(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $group = $this->group->create([
                'name' => Input::get('name'),
                'description' => Input::get('description'),
            ]);

            return $this->response->created($group);
        }
    }

    public function postAddUserToGroup(Request $request, $id)
    {
        $request->merge(['group_id' => $id]);
        $group_validation_rules = ['group_id' => 'required|exists:groups,id'];
        $group_validator = Validator::make($request->all(), $group_validation_rules);

        $request->merge(['user_id' => Input::get('user_id')]);
        $user_validation_rules = ['user_id' => 'required|exists:users,id'];
        $user_validator = Validator::make($request->all(), $user_validation_rules);

        if ($group_validator->fails()) {

            return $this->response->validationFailed($group_validator->errors()->all());

        } else if ($user_validator->fails()) {

            return $this->response->validationFailed($user_validator->errors()->all());

        } else {

            $user = $this->user->findOrFail(Input::get('user_id'));
            $group = $this->group->findOrFail($id);
                $group->users()->attach($user->id);

            // no need to do anything with the cache

            return $this->response->created();
        }
    }

    public function deleteRemoveUserFromGroup(Request $request, $id, $user_id)
    {
        $request->merge(['group_id' => $id]);
        $group_validation_rules = ['group_id' => 'required|exists:groups,id'];
        $group_validator = Validator::make($request->all(), $group_validation_rules);

        $request->merge(['user_id' => $user_id]);
        $user_validation_rules = ['user_id' => 'required|exists:users,id'];
        $user_validator = Validator::make($request->all(), $user_validation_rules);

        if ($group_validator->fails()) {

            return $this->response->validationFailed($group_validator->errors()->all());

        } else if ($user_validator->fails()) {

            return $this->response->validationFailed($user_validator->errors()->all());

        } else {

            $user = $this->user->findOrFail($user_id);
            $group = $this->group->findOrFail($id);
            $group->users()->detach($user->id);

            $this->deleteUrlsInGroupForUserFromCache($user->email, $group->id);

            return $this->response->noContent();
        }
    }

    public function getGroupUsers(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        // notice we are not using the class instance
        $validation['id'] = 'required|exists:groups,id';

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return $this->response->validationFailed($validator->errors()->all());
        } else {
            $users = $this->group->findOrFail($id)->users()->get();
            return $this->response->standard($users);
        }
    }

    public function getNotGroupUsers(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        // notice we are not using the class instance
        $validation['id'] = 'required|exists:groups,id';

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return $this->response->validationFailed($validator->errors()->all());
        } else {
            $users = $this->group->findOrFail($id)->users()->get();
            $user_ids = [];
            foreach ($users as $user) {
                $user_ids[] = $user->id;
            }
            $available_users = DB::table('users')->whereNotIn('id', $user_ids)->whereNull('deleted_at')->get();
            return $this->response->standard($available_users);
        }
    }

    public function getGroup(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        // notice we are not using the class instance
        $validation['id'] = 'required|exists:groups,id';

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return $this->response->validationFailed($validator->errors()->all());
        } else {
            $group = $this->group->findOrFail($id);
            return $this->response->standard($group);
        }
    }

    public function patchUpdateGroup(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:groups,id';

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $group = $this->group->findOrFail($id);
            $group->name = Input::get('name');
            $group->description = Input::get('description');
            $group->save();

            // no need to do anything with the cache

            if($group) {
                return $this->response->standard($this->group->findOrFail($id));
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }

    public function deleteGroup(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:groups,id';

        // because we only need to validate the id
        unset($this->validation['name']);
        unset($this->validation['description']);

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $group = $this->group->findOrFail($id);

            $this->deleteUsersAndUrlsInGroupFromCache($group->id);

            $group->delete();

            if($group) {
                return $this->response->noContent();
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }

    /**
     * Delete all user::url combinations for the group from cache
     *
     * @param $group_id
     */
    private function deleteUsersAndUrlsInGroupFromCache($group_id)
    {
        $group_details = Group::where('id', $group_id)->with('urls', 'users')->first();

        foreach($group_details->users as $user) {
            foreach($group_details->urls as $url) {
                $this->deleteCacheKey($user->email, $url->method, $url->url);
            }
        }
    }

    /**
     * Delete all group urls for a single user from cache
     *
     * @param $user_email
     * @param $group_id
     */
    private function deleteUrlsInGroupForUserFromCache($user_email, $group_id)
    {
        $group_details = Group::where('id', $group_id)->with('urls')->first();

        foreach($group_details->urls as $url) {
            $this->deleteCacheKey($user_email, $url->method, $url->url);
        }
    }
}