<?php

use Illuminate\Support\Facades\Artisan;

class WebUserTest extends TestCase
{
    private $base_url = 'admin/panel/ajax';

    private $user = [
        'name' => 'Aron Something',
        'email' => 'as@as.com'
    ];

    private $users = [
        ['name' => 'Aron Something', 'email' => 'as@as.com'],
        ['name' => 'Arthur Stephan', 'email' => 'as2@as.com'],
        ['name' => 'Alan Smith', 'email' => 'as3@as.com'],
    ];

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function testGetAllUsers()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->users[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[2])
            ->seeJson(['code' => 201])
            ->visit($this->base_url.'/user')
            ->seeJson(['code' => 200])
            ->seeJson($this->users[0])
            ->seeJson($this->users[1])
            ->seeJson($this->users[2]);
    }

    public function testAddAUserWithNoName()
    {
        unset($this->user['name']);

        $this->baseTestAddUserWithAttributeRemovedFromUserArray();
    }

    public function testAddAKeyWithNoDescription()
    {
        unset($this->user['email']);

        $this->baseTestAddUserWithAttributeRemovedFromUserArray();
    }

    private function baseTestAddUserWithAttributeRemovedFromUserArray()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testAddAUser()
    {
        // because the returned version is hashed
        $user = $this->user;

        $return_data = array_merge(
            ['code' => 201, 'id' => 1],
            $user
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson($return_data);
    }

    public function testTryingToAddTheSameUser()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testGetUser()
    {
        // because the returned version is hashed
        $key = $this->user;

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $key
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/user/1')
            ->seeJson($return_data);
    }

    public function testGetUserThatDoesNotExist()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/user/100')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateUserWithoutID()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/user/')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateUser()
    {
        $user = $this->user;
        $user['name'] = 'Stephen';

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $user
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/user/1', $user)
            ->seeJson($return_data);
    }

    public function testDeleteUser()
    {
        $user2 = $this->users[1];
        $user2_return_data = array_merge(['code' => 200, 'id' => 2], $user2);

        $user3 = $this->users[2];
        $user3_return_data = array_merge(['code' => 200, 'id' => 3], $user3);

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->users[0])
            ->post($this->base_url.'/user', $this->users[1])
            ->post($this->base_url.'/user', $this->users[2])
            ->delete($this->base_url.'/user/1')
            ->seeJson(['code' => 204])
            ->visit($this->base_url.'/user')
            ->seeJson(['code' => 200])
            ->seeJson($user2_return_data)
            ->seeJson($user3_return_data);
    }

    public function testDeleteUserWithWrongId()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/user', $this->users[0])
            ->post($this->base_url.'/user', $this->users[1])
            ->post($this->base_url.'/user', $this->users[2])
            ->delete($this->base_url.'/user/100')
            ->seeJson(['code' => 422]);
    }
}