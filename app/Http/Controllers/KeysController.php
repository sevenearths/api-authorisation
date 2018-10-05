<?php

namespace App\Http\Controllers;

use App\Models\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

// This class needs to be created
use ApiResponse\ApiResponseClass;

class KeysController extends Controller
{
    private $response;
    private $key;
    private $validation = [
        'name' => 'required|min:5|unique:keys,name',
        'key' => 'required|min:8',
        'secret' => 'required|min:8',
    ];

    public function __construct(ApiResponseClass $apiResponseClass, Key $key)
    {
        $this->response = $apiResponseClass;
        $this->key = $key;
    }

    public function getAllKeys(Request $request)
    {
        return $this->response->standard($this->key->all());
    }

    public function postAddNewKey(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $key = $this->key->create([
                'name' => Input::get('name'),
                'key' => Input::get('key'),
                'secret' => Input::get('secret')
            ]);

            return $this->response->created($key);
        }
    }

    public function getKey(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        // notice we are not using the class instance
        $validation['id'] = 'required|exists:keys,id';

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return $this->response->validationFailed($validator->errors()->all());
        } else {
            $key = $this->key->findOrFail($id);
            return $this->response->standard($key);
        }
    }

    public function patchUpdateKey(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:keys,id';

        // because it's not being submitted
        unset($this->validation['secret']);

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $key = $this->key->findOrFail($id);
            $key->name = Input::get('name');
            $key->key = Input::get('key');
            $key->save();

            if($key) {
                return $this->response->standard($this->key->findOrFail($id));
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }

    public function patchUpdateSecretKey(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:keys,id';

        // because we only need to validate the secret
        unset($this->validation['name']);
        unset($this->validation['key']);

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $key = $this->key->findOrFail($id)->update([
                'secret' => Input::get('secret')
            ]);

            if($key) {
                return $this->response->standard($this->key->findOrFail($id));
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }

    public function deleteKey(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:keys,id';

        // because we only need to validate the id
        unset($this->validation['name']);
        unset($this->validation['key']);
        unset($this->validation['secret']);

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $key = $this->key->findOrFail($id)->delete();

            if($key) {
                return $this->response->noContent();
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }
}