<?php

use Illuminate\Support\Facades\Artisan;

class WebGroupsTest extends TestCase
{
    private $base_url = 'admin/panel/ajax';

    private $group = [
        'name' => 'My Group',
        'description' => 'Something description about something'
    ];

    private $user = [
        'name' => 'Stephen',
        'email' => 'as@as.com'
    ];

    private $groups = [
        ['name' => 'My Group', 'description' => 'Something description about something'],
        ['name' => 'My Group 02', 'description' => 'Something description about something 2'],
        ['name' => 'My Group 03', 'description' => 'Something description about something 3'],
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

    public function testGetAllGroups()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->groups[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[2])
            ->seeJson(['code' => 201])
            ->visit($this->base_url.'/group')
            ->seeJson(['code' => 200])
            ->seeJson($this->groups[0])
            ->seeJson($this->groups[1])
            ->seeJson($this->groups[2]);
    }

    public function testAddAGroupWithNoName()
    {
        unset($this->group['name']);

        $this->baseTestAddGroupWithAttributeRemovedFromGroupArray();
    }

    public function testAddAKeyWithNoDescription()
    {
        unset($this->group['description']);

        $this->baseTestAddGroupWithAttributeRemovedFromGroupArray();
    }

    private function baseTestAddGroupWithAttributeRemovedFromGroupArray()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testAddAGroup()
    {
        // because the returned version is hashed
        $group = $this->group;

        $return_data = array_merge(
            ['code' => 201, 'id' => 1],
            $group
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson($return_data);
    }

    public function testTryingToAddTheSameGroup()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testAddUserToGroup()
    {
        $return_data = ['code' => 201];

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson($return_data);
    }

    public function testAddUserThatDoesNotExistToGroup()
    {
        $return_data = ['code' => 422];

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 100])
            ->seeJson($return_data);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testAddUserToGroupThatDoesNotExist()
    {
        $return_data = ['code' => 422];

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/100/user', ['user_id' => 1])
            ->seeJson($return_data);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testRemoveUserFromGroup()
    {
        $return_data = ['code' => 204];

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/group/1/user/1')
            ->seeJson($return_data);
    }

    public function testRemoveUserThatDoesNotExistFromGroup()
    {
        $return_data = ['code' => 422];

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/group/1/user/100')
            ->seeJson($return_data);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testRemoveUserFromGroupThatDoesNotExist()
    {
        $return_data = ['code' => 422];

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/group/100/user/1')
            ->seeJson($return_data);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testGetGroup()
    {
        // because the returned version is hashed
        $key = $this->group;

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $key
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/group/1')
            ->seeJson($return_data);
    }

    public function testGetGroupUsers()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->user)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/group/1/user')
            ->seeJson(['code' => 200])
            ->seeJson($this->user);
    }

    public function testGetNotGroupUsers()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[2])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/group/1/user/available')
            ->seeJson(['code' => 200])
            ->seeJson($this->users[1])
            ->seeJson($this->users[2]);
    }

    public function testGetGroupUsersWhereAUserHasBeenDelete()
    {
        $return_data = ['code' => 200, 'data' => []];

        $this->withSession(['user_logged_in' => true])
            // add group
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            // add users
            ->post($this->base_url.'/user', $this->users[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/user', $this->users[2])
            ->seeJson(['code' => 201])
            // add user to group
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            // delete user 1
            ->delete($this->base_url.'/user/1')
            ->seeJson(['code' => 204])
            // get users attached to group
            ->get($this->base_url.'/group/1/user')
            ->seeJsonEquals($return_data);
    }

    public function testGetNotGroupUsersWhereAUserHasBeenDelete()
    {
        $return_data = ['code' => 200, 'data' => []];

        $this->withSession(['user_logged_in' => true])
            // add group
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            // add users
            ->post($this->base_url.'/user', $this->users[0])
            ->seeJson(['code' => 201])
            // add user to group
            ->post($this->base_url.'/group/1/user', ['user_id' => 1])
            ->seeJson(['code' => 201])
            // delete user 1
            ->delete($this->base_url.'/user/1')
            ->seeJson(['code' => 204])
            // get users available to attach to the group
            ->get($this->base_url.'/group/1/user/available')
            ->seeJson($return_data);
    }

    public function testGetGroupThatDoesNotExist()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/group/100')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateGroupWithoutID()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/group/')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateGroup()
    {
        $group = $this->group;
        $group['name'] = 'Stephen';

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $group
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/group/1', $group)
            ->seeJson($return_data);
    }

    public function testDeleteGroup()
    {
        $group2 = $this->groups[1];
        $group2_return_data = array_merge(['code' => 200, 'id' => 2], $group2);

        $group3 = $this->groups[2];
        $group3_return_data = array_merge(['code' => 200, 'id' => 3], $group3);

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->groups[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[2])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/group/1')
            ->seeJson(['code' => 204])
            ->visit($this->base_url.'/group')
            ->seeJson(['code' => 200])
            ->seeJson($group2_return_data)
            ->seeJson($group3_return_data);
    }

    public function testDeleteGroupWithWrongId()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->groups[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[2])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/group/100')
            ->seeJson(['code' => 422]);
    }
}