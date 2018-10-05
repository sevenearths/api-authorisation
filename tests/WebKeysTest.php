<?php

use Illuminate\Support\Facades\Artisan;

class WebKeysTest extends TestCase
{
    private $base_url = 'admin/panel/ajax';

    private $key = [
        'name' => 'Stephan',
        'key' => '12341234',
        'secret' => '12341234'
    ];

    private $keys = [
        ['name' => 'Stephan', 'key' => '12341234', 'secret' => '12341234'],
        ['name' => 'Richard', 'key' => '23452345', 'secret' => '23452345'],
        ['name' => 'Bernard', 'key' => '34563456', 'secret' => '34563456'],
    ];

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function testGetAllKeys()
    {
        $keys1 = $this->keys[0];
        unset($keys1['secret']);
        $keys1_return_data = array_merge(['code' => 200, 'id' => 2], $keys1);

        $keys2 = $this->keys[1];
        unset($keys2['secret']);
        $keys2_return_data = array_merge(['code' => 200, 'id' => 2], $keys2);

        $keys3 = $this->keys[2];
        unset($keys3['secret']);
        $keys3_return_data = array_merge(['code' => 200, 'id' => 3], $keys3);

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->keys[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/key', $this->keys[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/key', $this->keys[2])
            ->seeJson(['code' => 201])
            ->visit($this->base_url.'/key')
            ->seeJson(['code' => 200])
            ->seeJson($keys1_return_data)
            ->seeJson($keys2_return_data)
            ->seeJson($keys3_return_data);
    }

    public function testAddAKeyWithNoName()
    {
        unset($this->key['name']);

        $this->baseTestAddKeyWithAttributeRemovedFromKeyArray();
    }

    public function testAddAKeyWithNoKey()
    {
        unset($this->key['key']);

        $this->baseTestAddKeyWithAttributeRemovedFromKeyArray();
    }

    public function testAddAKeyWithNoSecret()
    {
        unset($this->key['secret']);

        $this->baseTestAddKeyWithAttributeRemovedFromKeyArray();
    }

    private function baseTestAddKeyWithAttributeRemovedFromKeyArray()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testAddAKey()
    {
        // because the returned version is hashed
        $key = $this->key;
        unset($key['secret']);

        $return_data = array_merge(
            ['code' => 201, 'id' => 1],
            $key
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson($return_data);
    }

    public function testTryingToAddTheSameKey()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testGetKey()
    {
        // because the returned version is hashed
        $key = $this->key;
        unset($key['secret']);

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $key
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/key/1')
            ->seeJson($return_data);
    }

    public function testGetKeyThatDoesNotExist()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/key/100')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateKeyWithoutID()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/key/')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateKey()
    {
        // because the returned version is hashed
        $key = $this->key;
        unset($key['secret']);
        $key['name'] = 'Stephen';

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $key
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/key/1', $key)
            ->seeJson($return_data);
    }

    public function testUpdateSecretKeyWithoutID()
    {
        // because the returned version is hashed
        $key = $this->key;
        unset($key['name']);
        unset($key['key']);
        $key['secret'] = 'something else';

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/key/secret/', $key)
            ->seeJson(['code' => 422]);
    }

    public function testUpdateSecretKeyWithWrongID()
    {
        // because the returned version is hashed
        $key = $this->key;
        unset($key['name']);
        unset($key['key']);
        $key['secret'] = 'something else';

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/key/secret/100', $key)
            ->seeJson(['code' => 422]);
    }

    public function testUpdateSecretKey()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->key)
            ->seeJson(['code' => 201]);

        $create_response_array = (array)json_decode($this->response->content());


        // because the returned version is hashed
        $key = $this->key;
        unset($key['name']);
        unset($key['key']);
        $key['secret'] = 'something else';

        $key_without_secret = $this->key;
        unset($key_without_secret['secret']);

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $key_without_secret
        );

        $this->withSession(['user_logged_in' => true])
            ->patch($this->base_url.'/key/secret/1', $key)
            ->seeJson($return_data);

        $patch_response_array = (array)json_decode($this->response->content());

        $this->assertNotEquals($create_response_array['data']->secret, $patch_response_array['data']->secret);
    }

    public function testDeleteKey()
    {
        $keys2 = $this->keys[1];
        unset($keys2['secret']);
        $keys2_return_data = array_merge(['code' => 200, 'id' => 2], $keys2);

        $keys3 = $this->keys[2];
        unset($keys3['secret']);
        $keys3_return_data = array_merge(['code' => 200, 'id' => 3], $keys3);

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->keys[0])
            ->post($this->base_url.'/key', $this->keys[1])
            ->post($this->base_url.'/key', $this->keys[2])
            ->delete($this->base_url.'/key/1')
            ->seeJson(['code' => 204])
            ->visit($this->base_url.'/key')
            ->seeJson(['code' => 200])
            ->seeJson($keys2_return_data)
            ->seeJson($keys3_return_data);
    }

    public function testDeleteKeyWithWrongId()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/key', $this->keys[0])
            ->post($this->base_url.'/key', $this->keys[1])
            ->post($this->base_url.'/key', $this->keys[2])
            ->delete($this->base_url.'/key/100')
            ->seeJson(['code' => 422]);
    }
}