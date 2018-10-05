<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

use App\Models\Key;
use App\Models\Url;
use App\Models\User;
use App\Models\Group;
use App\Traits\CacheTrait;

// This class needs to be created
use ApiResponse\ApiResponseClass;

class AuthorisationController extends Controller
{
    use CacheTrait;

    private $response;
    private $url;
    private $group;

    private $validation = [
        'key' => 'required',
        'secret' => 'required',
        // 'method' is a protected variable on Request
        'method' => 'required',
        'url' => 'required|url',
        'user' => 'required',
    ];
    /*private $validation = [
        'key' => 'required|min:5|exists:keys,key',
        'secret' => 'required|min:8',
        'method' => 'required|in:ALL,get,post,patch,delete',
        'url' => 'required|url',
        'user' => 'required|exists:users,email',
    ];*/

    public function __construct(ApiResponseClass $apiResponseClass, Url $url, Group $group)
    {
        $this->response = $apiResponseClass;
        $this->url = $url;
        $this->group = $group;
    }

    public function postAuthorisation(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation);

        if ($validator->fails()) {

            Log::debug(json_encode($validator->errors()->all()));

            return $this->response->unauthorised();

        } else {

            // check the data
            $key = Key::where('key', $request->key)->first();

            if ($key == null) {
                return $this->response->unauthorised();
            }

            if (Hash::check($request->secret, $key['secret']) == false ||
                User::where('email', $request->user)->count() == 0
            ) {
                $this->debug("Hash::check(request->secret, key['secret']) == " . Hash::check($request->secret, $key['secret']));
                $this->debug("User::where('email', request->user)->count() == " . User::where('email', $request->user)->count());

                return $this->response->unauthorised();
            }

            // have to check the key and secret first
            if (Cache::has($this->getCacheKeyFromRequest($request))) {
                $this->debug('LOADED FROM CACHE!!!');
                
                return $this->response->standard(Cache::get($this->getCacheKeyFromRequest($request)));
            }

            // take the top rule that matches and apply it

            $this->debug('---------------------------------');

            // TODO: need to order the group by something
            $groups = User::where('email', $request->user)->first()->groups()->with('urls')->get();

            foreach ($groups as $group) {

                foreach ($group->urls as $url) {

                    $this->debug('--> '.preg_quote($request->url).' = '.$url['url'].' ('.!$url['deny'].')');

                    if(preg_match('/'.str_replace('/', '\\/', $url['url']).'/', $request->url)) {

                        $this->debug('----  MATCH: url  ----');

                        if($url['method'] == $request->get('method') || $url['method'] == 'ALL') {

                            $this->debug('-----  MATCH: method  -----');

                            if($url['deny'] == true) {
                                $this->debug('------> deny');
                                return $this->storeAndRespond($request, 'deny');
                            } else {
                                $this->debug('------> ALLOW');
                                return $this->storeAndRespond($request, 'allow');
                            }

                        } else {
                            $this->debug('-----  MATCH: method (failed!)  -----');
                            $this->debug('-----> deny');
                        }

                    } else {
                        $this->debug('----  MATCH: url (failed!)  ----');
                        $this->debug('----> deny');
                    }

                }

            }

            return $this->storeAndRespond($request, 'deny');

        }
    }

    private function storeAndRespond($request, $result)
    {
        if(Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) { $this->debug('Driver = redis'); }
        else { $this->debug('Driver = NOT redis'); }
        $this->debug('Store = ' . Config::get('cache.stores.redis.connection'));
        $this->debug('Key   = ' . $this->getCacheKeyFromRequest($request) . ' (' . $result . ')');

        Cache::tags([$request->url, $request->user])->forever($this->getCacheKeyFromRequest($request), $result);
        if($request->get('method') == 'ALL') {
            Cache::tags([$request->url, $request->user])->forever($this->getCacheKey($request->user, 'get', $request->url), $result);
            Cache::tags([$request->url, $request->user])->forever($this->getCacheKey($request->user, 'post', $request->url), $result);
            Cache::tags([$request->url, $request->user])->forever($this->getCacheKey($request->user, 'patch', $request->url), $result);
            Cache::tags([$request->url, $request->user])->forever($this->getCacheKey($request->user, 'delete', $request->url), $result);
        }

        return $this->response->standard($result);
    }
}