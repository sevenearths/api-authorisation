<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

// This class needs to be created
use ApiResponse\ApiResponseClass;

use App\Models\Url;
use App\Models\Group;
use App\Traits\CacheTrait;

class UrlsController extends Controller
{
    use CacheTrait;

    private $response;
    private $url;
    private $group;
    private $validation = [
        'url' => 'required|url',
        'group_id' => 'required|exists:groups,id',
        'deny' => 'required|boolean',
        'method' => 'required|in:ALL,get,post,patch,delete',
    ];

    public function __construct(ApiResponseClass $apiResponseClass, Url $url, Group $group)
    {
        $this->response = $apiResponseClass;
        $this->url = $url;
        $this->group = $group;
    }

    public function getAllUrls(Request $request)
    {
        return $this->response->standard($this->url->all());
    }

    public function getAllUrlsForGroup(Request $request, $group_id)
    {
        $request->merge(['group_id' => $group_id]);
        $validation_rules = [
            'group_id' => 'required|exists:groups,id'
        ];

        $validator = Validator::make($request->all(), $validation_rules);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            return $this->response->standard($this->group->findOrFail($group_id)->urls()->get());

        }
    }

    public function postAddNewUrl(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $group_id = Input::get('group_id');

            try {
                $url = $this->url->create([
                    'url' => Input::get('url'),
                    'group_id' => $group_id,
                    'deny' => Input::get('deny'),
                    // done by the model
                    //,'order' => DB::table('urls')->where('group_id', $group_id)->count()
                    'method' => Input::get('method')
                ]);
            } catch (\Exception $e) {
                // what if it's not an integrity constraint issue?
                if (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                    Log::error($e->getTraceAsString());
                }
                return $this->response->validationFailed('The url is not unique or you have two urls at the same order position');
            }

            return $this->response->created($url);
        }
    }

    public function getUrl(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        // notice we are not using the class instance
        $validation['id'] = 'required|exists:urls,id';

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return $this->response->validationFailed($validator->errors()->all());
        } else {
            $url = $this->url->findOrFail($id);
            return $this->response->standard($url);
        }
    }

    public function patchUpdateUrl(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:urls,id';

        Log::debug(json_encode($request->all()));

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $url = $this->url->findOrFail($id);

            // will also delete url for all other users in all other groups (can't do much about that)
            $this->deleteCacheTag($url->url);

            $url->url = Input::get('url');
            $url->group_id = Input::get('group_id');
            $url->deny = Input::get('deny');
            $url->method = Input::get('method');
            $url->save();

            if($url) {
                return $this->response->standard($this->url->findOrFail($id));
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }

    public function patchReorderUrls(Request $request, $group_id)
    {
        $validator = Validator::make(
            ['group_id' => $group_id],
            ['group_id' => 'required|exists:groups,id']
        );

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            // make sure all the ids are in the same group first
            $group_urls_ids = [];
            foreach ($this->group->findOrFail($group_id)->urls()->get() as $urls) {
                $group_urls_ids[] = $urls->id;
                // flush them from the cache
            }
            foreach ($request->order as $order_url_id) {
                if (in_array($order_url_id, $group_urls_ids) == false) {
                    return $this->response->validationFailed('One of the urls is not in the group');
                }
            }

            $urls = [];

            foreach ($request->order as $key => $url_id) {
                $url = $this->url->findOrFail($url_id);
                $url->order = $key;
                $url->save();

                $this->deleteCacheTag($url->url);

                $urls[] = $url;
            }

            return $this->response->standard($urls);

        }
    }

    public function deleteUrl(Request $request, $id = null)
    {
        $request->merge(['id' => $id]);
        $this->validation['id'] = 'required|exists:urls,id';

        // because we only need to validate the id
        unset($this->validation['method']);
        unset($this->validation['url']);
        unset($this->validation['group_id']);
        unset($this->validation['deny']);

        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            return $this->response->validationFailed($validator->errors()->all());

        } else {

            $url = $this->url->findOrFail($id);

            $this->deleteCacheTag($url->url);

            $url->delete();

            if($url) {
                return $this->response->noContent();
            } else {
                return $this->response->validationFailed('entry does not exist');
            }
        }
    }
}