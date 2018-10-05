<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

// This class needs to be created
use ApiResponse\ApiResponseClass;

use App\Models\User;
use App\Traits\CacheTrait;

class UsersController extends Controller
{
    use CacheTrait;
    
    private $response;
    private $user;
    private $validation = [
        'name' => 'required|min:5|unique:users,name',
        'email' => 'required|email',
    ];

    public function __construct(ApiResponseClass $apiResponseClass, User $user)
    {
        $this->response = $apiResponseClass;
        $this->user = $user;
    }

    public function getAllUsers(Request $request)
    {
        return $this->response->standard($this->user->all());
    }

    public function postAddNewUser(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $user = $this->user->create([
                'name' => Input::get('name'),
                'email' => Input::get('email'),
            ]);

            return $this->response->created($user);
        }
    }

    public function getUser(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        // notice we are not using the class instance
        $validation['id'] = 'required|exists:users,id';

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return $this->response->validationFailed($validator->errors()->all());
        } else {
            $user = $this->user->findOrFail($id);
            return $this->response->standard($user);
        }
    }

    public function patchUpdateUser(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:users,id';

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $user = $this->user->findOrFail($id);

            $this->deleteCacheTag($user->email);

            $user->name = Input::get('name');
            $user->email = Input::get('email');
            $user->save();

            if($user) {
                return $this->response->standard($this->user->findOrFail($id));
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }

    public function deleteUser(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:users,id';

        // because we only need to validate the id
        unset($this->validation['name']);
        unset($this->validation['email']);

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $user = $this->user->findOrFail($id);

            $this->deleteCacheTag($user->email);

            $user->delete();

            if($user) {
                return $this->response->noContent();
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }
}