<?php

use Illuminate\Support\Facades\Artisan;

class WebUrlTest extends TestCase
{
    private $base_url = 'admin/panel/ajax';

    private $url = [
        'method' => 'ALL',
        'url' => 'http://www.something.com/',
        'group_id' => 1,
        'deny' => false
    ];

    private $group = [
        'name' => 'My Group',
        'description' => 'Something description about something'
    ];

    private $urls = [
        ['method' => 'ALL', 'url' => 'http://www.something.com/', 'group_id' => 1, 'deny' => false],
        ['method' => 'ALL', 'url' => 'http://www.something02.com/', 'group_id' => 1, 'deny' => false],
        ['method' => 'ALL', 'url' => 'http://www.something03.com/', 'group_id' => 1, 'deny' => false],
    ];

    private $groups = [
        ['name' => 'My Group', 'description' => 'Something description about something'],
        ['name' => 'My Group 02', 'description' => 'Something description about something 2'],
        ['name' => 'My Group 03', 'description' => 'Something description about something 3'],
    ];

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function testGetAllUrls()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->visit($this->base_url.'/url')
            ->seeJson(['code' => 200])
            ->seeJson($this->urls[0])
            ->seeJson($this->urls[1])
            ->seeJson($this->urls[2]);
    }

    public function testGetAllUrlsForGroup()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->visit($this->base_url.'/url/group/1')
            ->seeJson(['code' => 200])
            ->seeJson($this->urls[0])
            ->seeJson($this->urls[1])
            ->seeJson($this->urls[2]);
    }

    public function testGetAllUrlsForGroupWhereGroupIdDoesNotExist()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->visit($this->base_url.'/url/group/100')
            ->seeJson(['code' => 422]);
    }

    public function testAddAUrlWithNoUrl()
    {
        unset($this->url['url']);

        $this->baseTestAddUrlWithAttributeRemovedFromUrlArray();
    }

    public function testAddAKeyWithNoGroupId()
    {
        unset($this->url['group_id']);

        $this->baseTestAddUrlWithAttributeRemovedFromUrlArray();
    }

    public function testAddAKeyWithNoDeny()
    {
        unset($this->url['deny']);

        $this->baseTestAddUrlWithAttributeRemovedFromUrlArray();
    }

    private function baseTestAddUrlWithAttributeRemovedFromUrlArray()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testAddAUrl()
    {
        $url = $this->url;

        $return_data = array_merge(
            ['code' => 201, 'id' => 1],
            $url
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson($return_data);
    }

    public function testTryingToAddTheSameUrl()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 422]);

        $response_array = (array)json_decode($this->response->content());

        $this->assertArrayHasKey('validate', $response_array);
    }

    public function testGetUrl()
    {
        $key = $this->url;

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $key
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/url/1')
            ->seeJson($return_data);
    }

    public function testGetUrlThatDoesNotExist()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 201])
            ->get($this->base_url.'/url/100')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateUrlWithoutID()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/url/')
            ->seeJson(['code' => 422]);
    }

    public function testUpdateUrl()
    {
        $url = $this->url;
        $url['url'] = 'http://www.google.com/';

        $return_data = array_merge(
            ['code' => 200, 'id' => 1],
            $url
        );

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->url)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/url/1', $url)
            ->seeJson($return_data);
    }

    public function testReOrderUrls()
    {
        $url1 = $this->urls[0];
        $url1['order'] = 2;

        $url2 = $this->urls[1];
        $url2['order'] = 1;

        $url3 = $this->urls[2];
        $url3['order'] = 0;

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/url/order/1', ['order' => [3,2,1]])
            ->seeJson(['code' => 200])
            ->seeJson($url1)
            ->seeJson($url2)
            ->seeJson($url3)
            ->visit($this->base_url.'/url')
            ->seeJson(['code' => 200])
            ->seeJson($url1)
            ->seeJson($url2)
            ->seeJson($url3);
    }

    public function testReOrderUrlsWhereGroupIdDoesNotExist()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->groups[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/url/order/100', ['order' => [3,2,1]])
            ->seeJson(['code' => 422]);
    }

    public function testReOrderUrlsWithOneUrlNotInGroup()
    {
        $url1 = $this->urls[0];
        $url1['order'] = 2;

        $url2 = $this->urls[1];
        $url2['order'] = 1;

        $url3 = $this->urls[2];
        $url3['order'] = 0;

        $url3_in_second_group = $this->urls[2];
        $url3_in_second_group['group_id'] = 2;

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->groups[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/group', $this->groups[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $url3_in_second_group)
            ->seeJson(['code' => 201])
            ->patch($this->base_url.'/url/order/1', ['order' => [3,2,1]])
            ->seeJson(['code' => 422]);
    }

    public function testDeleteUrl()
    {
        $url2 = $this->urls[1];
        $url2_return_data = array_merge(['code' => 200, 'id' => 2], $url2);

        $url3 = $this->urls[2];
        $url3_return_data = array_merge(['code' => 200, 'id' => 3], $url3);

        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/url/1')
            ->seeJson(['code' => 204])
            ->visit($this->base_url.'/url')
            ->seeJson(['code' => 200])
            ->seeJson($url2_return_data)
            ->seeJson($url3_return_data);
    }

    public function testDeleteUrlWithWrongId()
    {
        $this->withSession(['user_logged_in' => true])
            ->post($this->base_url.'/group', $this->group)
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[0])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[1])
            ->seeJson(['code' => 201])
            ->post($this->base_url.'/url', $this->urls[2])
            ->seeJson(['code' => 201])
            ->delete($this->base_url.'/url/100')
            ->seeJson(['code' => 422]);
    }
}