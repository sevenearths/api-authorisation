<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

trait CacheTrait {

    public function getCacheKey($user_email, $method, $url)
    {
        return $user_email . '::' . $method . '::' . $url;
    }

    public function getCacheKeyFromRequest(Request $request)
    {
        return $this->getCacheKey($request->user, $request->get('method'), $request->url);
    }

    public function deleteCacheKey($user_email, $method, $url)
    {
        Cache::flush($this->getCacheKey($user_email, $method, $url));
        
        if($method == 'ALL') {
            Cache::flush($this->getCacheKey($user_email, 'get', $url));
            Cache::flush($this->getCacheKey($user_email, 'post', $url));
            Cache::flush($this->getCacheKey($user_email, 'patch', $url));
            Cache::flush($this->getCacheKey($user_email, 'delete', $url));
        }
    }

    public function deleteCacheTag($tag)
    {
        $this->logCacheContents();
        $this->debug('Cache::tags([' . $tag . '])->flush()');

        Cache::tags([$tag])->flush();

        $this->logCacheContents();
    }

    public function logCacheContents()
    {
        $this->debug('---------------------------------');
        $this->debug('current Cache:');
        // only print out user created keys
        foreach (Redis::command('keys', ['*']) as $key) {
            foreach (['ALL', 'get', 'post', 'patch', 'delete'] as $method) {
                if (strpos($key, '::'.$method.'::')) {
                    $key_without_prefix = substr($key, strpos($key, ':', 8) + 1);
                    $this->debug('Cache: ' . $key_without_prefix . '(' . Cache::get($key_without_prefix) . ')');
                }
            }
        }
        $this->debug('---------------------------------');
    }

    public function debug($message)
    {
        if(env('DEBUG_API') === true) { Log::debug(__CLASS__.': '.$message); }
    }

}