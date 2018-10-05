<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;

use App\Traits\CacheTrait;

class CacheMethodAllTest extends TestCase
{
    use CacheTrait;

    private $base_url = 'api/v1';
    private $admin_url = 'admin/panel/ajax';

    private $data = [
        'key' => [
            'name' => 'The Key',
            'key' => '12341234',
            'secret' => '12341234'
        ],
        'user' => [
            'name' => 'Aron Something',
            'email' => 'as@as.com'
        ],
        'group' => [
            'name' => 'My Group',
            'description' => 'Something description about something'
        ],
        'urls' => [
            [
                'method' => 'ALL',
                'url' => 'http://www.something.com/dave/ian',
                'group_id' => 1,
                'deny' => false
            ],
            [
                'method' => 'ALL',
                'url' => 'http://www.something.com/dave',
                'group_id' => 1,
                'deny' => false
            ],
            [
                'method' => 'ALL',
                'url' => 'http://www.something.com/',
                'group_id' => 1,
                'deny' => false
            ],
        ]
    ];

    private $test_url = 'http://www.something.com/dave/ian';

    private $test_group = [
        'name' => 'My Group 02',
        'description' => 'Something description about something 02'
    ];

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');

        if(Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) { }
        else {
            print('\r\nERROR: Your cache driver does not support tagging. Please change it to reddis');
        }
    }

    public function tearDown()
    {
        Artisan::call('cache:clear');
    }

    private function postData()
    {
        return [
            'key' => $this->data['key']['key'],
            'secret' => $this->data['key']['secret'],
            'method' => 'ALL',
            'url' => $this->test_url,
            'user' => $this->data['user']['email']
        ];
    }

    private function alterTestUrl()
    {
        return substr($this->test_url, 0, strlen($this->test_url)-1);
    }

    private function alterDomainNameOnTestUrl($url = null)
    {
        if($url == null) { $url = $this->test_url; }
        return substr($url, 0, 14) . substr($url, 15, strlen($url));
    }

    private function loadData()
    {
        return $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2]);
    }

    private function loadDataNoUrls()
    {
        return $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1]);
    }


        /////  TESTS  /////


    public function testClearOutCacheFromPreviousTests()
    {
        $this->logMessage(__FUNCTION__);

        Artisan::call('cache:clear');

        $this->assertTrue(true);
    }

    // empty test (deny)
    public function testAllowDoesNotPersistInTest()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection
    public function testNoPostData()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $this->loadData()
            ->post($this->base_url,[]);

        $this->assertFalse($this->cacheExists());
    }


    /////  ONLY ONE POST VALUE  /////


    // base rejection (only key)
    public function testPostDataKeyOnly()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['secret']);
        unset($post_data['url']);
        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (only secret)
    public function testPostDataSecretOnly()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['key']);
        unset($post_data['url']);
        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (only url)
    public function testPostDataUrlOnly()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['key']);
        unset($post_data['secret']);
        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (only user)
    public function testPostDataUserOnly()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['key']);
        unset($post_data['secret']);
        unset($post_data['url']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }


    /////  REMOVE ONE POST VALUE  /////


    // base rejection (missing key)
    public function testPostDataWithNoKey()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['key']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (missing secret)
    public function testPostDataWithNoSecret()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['secret']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (missing url)
    public function testPostDataWithNoUrl()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['url']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (missing user)
    public function testPostDataWithNoUser()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }


    /////  CHANGE ONE POST VALUE  /////


    // base rejection (change key)
    public function testPostDataKeyValueChanged()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['key'] = '1234123';

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (change secret)
    public function testPostDataSecretValueChanged()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['secret'] = '2341234';

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    // base rejection (change url)
    // no point having this it's just a normal request
    /*public function testPostDataUrlValueChanged()
    {
        $post_data = $this->postData();

        $post_data['url'] = 'http://www.something.com/dave/ia';

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }*/

    // base rejection (change user)
    public function testPostDataUserValueChanged()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['user'] = 'as1@as.com';

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }


    //////  ONLY ONE URL IN DB  //////


    // first match (single)
    public function testOnlyOneUrlInTheDBThatMatchesTestUrl()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // first match (single) [deny]
    public function testOnlyOneUrlInTheDBThatMatchesTestUrlButIsSetToDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // first rejection (single)
    public function testOnlyOneUrlInTheDBThatDoesNotMatchTestUrl()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['url'] = $this->alterTestUrl();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data);

        /*$this->assertTrue($this->cacheExists($this->data['user']['email'], $post_data['method'], $post_data['url']));
        $this->assertEquals($this->getCachedResult($this->data['user']['email'], $post_data['method'], $post_data['url']), 'deny');*/

        $this->assertTrue($this->cacheExists($post_data['user'], $post_data['method'], $post_data['url']));
        $this->assertEquals($this->getCachedResult($post_data['user'], $post_data['method'], $post_data['url']), 'deny');
    }

    // first rejection (single) [deny]
    public function testOnlyOneUrlInTheDBThatDoesNotMatchTestUrlAndTheDBUrlIsSetToDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['url'] = $this->alterTestUrl();

        $this->data['urls'][0]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists($post_data['user'], $post_data['method'], $post_data['url']));
        $this->assertEquals($this->getCachedResult($post_data['user'], $post_data['method'], $post_data['url']), 'deny');
    }

    // first alt-match (single)
    public function testOnlyOneAlternativeUrlInTheDBThatMatchesTestUrl()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // first alt-rejection (single)
    public function testOnlyOneAlternativeUrlInTheDBThatDoesNotMatchTestUrl()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['url'] = $this->alterDomainNameOnTestUrl();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists($post_data['user'], $post_data['method'], $post_data['url']));
        $this->assertEquals($this->getCachedResult($post_data['user'], $post_data['method'], $post_data['url']), 'deny');
    }

    // first alt-rejection (single) [deny]
    public function testOnlyOneAlternativeUrlInTheDBThatDoesNotMatchTestUrlAndTheDBUrlIsSetToDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['url'] = $this->alterTestUrl();

        $this->data['urls'][1]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists($post_data['user'], $post_data['method'], $post_data['url']));
        $this->assertEquals($this->getCachedResult($post_data['user'], $post_data['method'], $post_data['url']), 'deny');
    }

    // first match
    public function testFirstMatch()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // first rejection (deny)
    public function testFirstMatchDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // first rejection
    public function testFirstRejection()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][1]['url']);
        $this->data['urls'][2]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][2]['url']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // first alt-match
    public function testFirstAltMatch()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        unset($this->data['urls'][0]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // first alt-rejection
    public function testFirstAltMatchDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][1]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][1]['url']);
        $this->data['urls'][2]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][2]['url']);
        unset($this->data['urls'][0]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }


    /////  MATCHES FOR URLS IN SECOND PLACE IN THE DB  /////


    // second match
    public function testSecondMatch()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->test_url;

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // second rejection (deny)
    public function testSecondMatchDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->test_url;
        $this->data['urls'][1]['deny'] = true;

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // second rejection
    public function testSecondRejection()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][1]['url']);
        unset($this->data['urls'][2]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // second alt-match
    public function testSecondAltMatch()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // second alt-rejection (deny)
    public function testSecondAltRejectionDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['deny'] = true;

        $this->loadData()
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // second alt-rejection
    public function testSecondAltRejection()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['deny'] = true;
        unset($this->data['urls'][2]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    /////  OTHER GROUP  /////

    // matching links in another group
    public function testMatchUrlsAreInAnotherGroup()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group', $this->test_group)
            ->post($this->admin_url.'/group/2/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // matching links in another group (all deny)
    public function testMatchUrlsAreInAnotherGroupAndAllSetToDeny()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;
        $this->data['urls'][1]['deny'] = true;
        $this->data['urls'][2]['deny'] = true;

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group', $this->test_group)
            ->post($this->admin_url.'/group/2/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    /////  RE-ORDER  /////
    // (test to make sure the caching is working) //

    // deny moved from the bottom to the middle
    public function testAddUrlsThenMoveBottomUrlToTheMiddle()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][2]['deny'] = true;

        $this->loadData()
            ->patch($this->admin_url.'/url/order/1', ['order' => [1,3,2]])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    // deny moved from the bottom to the top
    public function testAddUrlsThenMoveBottomUrlToTheTop()
    {
        $this->logMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][2]['deny'] = true;

        $this->loadData()
            ->patch($this->admin_url.'/url/order/1', ['order' => [3,1,2]])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }


    ///// UPDATE  /////

    public function testUpdateUser()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $new_user = [
            'name' => 'New Dave',
            'email' => 'dave@as.com'
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/user/1', $new_user);

        $this->assertFalse($this->cacheExists());
        
        $post_data['user'] = $new_user['email'];

        $this->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'allow');
    }

    public function testUpdateOnlyUrlToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'],
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => true
        ];

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    public function testUpdateFirstUrlToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'],
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => true
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    public function testUpdateOnlyUrlToDifferentUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    // first url is set to deny then has a different url and the 
    // second url catches the next url with allow
    public function testUpdateFirstUrlToDifferentUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    public function testUpdateUrlWithSecondUrlSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][1]['deny'] = true;

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }


        ///// UPDATE (Method: get)  /////
// TODO: change this name
    public function testAJUpdateUserWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $new_user = [
            'name' => 'New Dave',
            'email' => 'dave@as.com'
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/user/1', $new_user)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    public function testUpdateOnlyUrlToDenyWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'],
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => true
        ];

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'deny');
    }

    public function testUpdateFirstUrlToDenyWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'],
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => true
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'deny');
    }

    public function testUpdateOnlyUrlToDifferentUrlWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'deny');
    }

    // first url is set to deny then has a different url and the
    // second url catches the next url with allow
    public function testUpdateFirstUrlToDifferentUrlWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $this->data['urls'][0]['deny'] = true;

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'allow');
    }

    public function testUpdateUrlWithSecondUrlSetToDenyWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $this->data['urls'][1]['deny'] = true;

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'deny');
    }


    /////  MOVE  /////

    public function testMoveSingleUrlToGroupWithOutUserInIt()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][0]['group_id'] = 2;

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'],
            'group_id' => 1,
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group', $this->test_group)
            // add user to group 2
            ->post($this->admin_url.'/group/2/user', ['user_id' => 1])
            // add url to group 2
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            // add url to group 1
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    public function testAttachUserToGroupWithUrlInIt()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['user']['group_id'] = 2;

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group', $this->test_group)
            ->post($this->admin_url.'/group/2/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/group/2/user/1')
            ->post($this->base_url, $post_data)
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }


    /////  MOVE (Method: get)  /////

    public function testMoveSingleUrlToGroupWithOutUserInItWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $this->data['urls'][0]['group_id'] = 2;

        $new_url = [
            'method' => 'ALL',
            'url' => $this->data['urls'][0]['url'],
            'group_id' => 1,
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group', $this->test_group)
            // add user to group 2
            ->post($this->admin_url.'/group/2/user', ['user_id' => 1])
            // add url to group 2
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            // add url to group 1
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'deny');
    }

    public function testAttachUserToGroupWithUrlInItWhereRequestMethodIsGet()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $post_data['method'] = 'get';

        $this->data['user']['group_id'] = 2;

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            ->post($this->admin_url.'/user', $this->data['user'])
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group', $this->test_group)
            ->post($this->admin_url.'/group/2/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/group/2/user/1')
            ->post($this->base_url, $post_data)
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1])
            ->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
        $this->assertTrue($this->cacheExistsForModifiedData($post_data));
        $this->assertEquals($this->getCachedResultForModifiedPostData($post_data), 'allow');
    }


        /////  DELETE  /////

    public function testRemoveUser()
    {
        $this->LogMessage(__FUNCTION__);

        //$this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/user/1');

        $this->assertFalse($this->cacheExists());

        // the key is not rebuilt on the next request because the
        // request fails validation
        $this->post($this->base_url, $post_data);

        $this->assertFalse($this->cacheExists());
    }

    public function testRemoveOnlyUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/url/1');

        $this->assertFalse($this->cacheExists());
        //$this->assertTrue($this->cacheDoesNotExistsForPostData($post_data));

        $this->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResultForPostDataAndMethodGet($post_data), 'deny');
        $this->assertEquals($this->getCachedResultForPostDataAndMethodPost($post_data), 'deny');
        $this->assertEquals($this->getCachedResultForPostDataAndMethodPatch($post_data), 'deny');
        $this->assertEquals($this->getCachedResultForPostDataAndMethodDelete($post_data), 'deny');
    }

    // second url will pickup with allow
    public function testRemoveFirstUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/url/1')
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'allow');
    }

    public function testRemoveFirstUrlSecondUrlSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][1]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/url/1')
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }

    public function testRemoveGroup()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/group/1')
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());
        $this->assertEquals($this->getCachedResult(), 'deny');
    }


    /////  DELETE (Method: get, post, patch, delete)  /////

    public function testRemoveOnlyUrlAndMakeSureAllOtherMethodsAreDeleted()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->delete($this->admin_url.'/url/1');

        $this->assertFalse($this->cacheExists());
    }

    public function testAddAllUrlAndDeleteGetUrlAndMakeSureAllOtherMethodsAreDeleted()
    {
        $this->LogMessage(__FUNCTION__);

        $this->assertFalse($this->cacheExists());

        $post_data = $this->postData();

        $this->data['urls'][1]['url'] = $post_data['url'];
        $this->data['urls'][1]['method'] = 'get';

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data);

        $this->assertTrue($this->cacheExists());

        $this->delete($this->admin_url.'/url/1');

        $this->assertFalse($this->cacheExists());
    }

    // TODO: add ALL::url, delete [get, post, patch, delete]::url
    // no point as deleting the get url deletes all keys tagged with that url as well


    // Redis Functions

    private function cacheExists($user_email = null, $method = null, $url = null)
    {
        if ($user_email == null) { $user_email = $this->postData()['user']; }
        if ($method == null) { $method = $this->postData()['method']; }
        if ($url == null) { $url = $this->postData()['url']; }

        if($method == 'ALL') {
            $num_keys = count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'ALL', $url) . '*']));
            $num_keys += count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'get', $url) . '*']));
            $num_keys += count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'post', $url) . '*']));
            $num_keys += count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'patch', $url) . '*']));
            $num_keys += count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'delete', $url) . '*']));
            $keys_count = 5;

            /*if ($num_keys != $keys_count) {
                print("\r\n------------------------------------------------------------\r\n");
                print_r(Redis::command('keys', ['*']));
                print("\r\ncount(Redis::command('keys', ['*" . $this->getCacheKey($user_email, 'ALL', $url) . "*'])) = ".count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'ALL', $url) . '*'])));
                print("\r\ncount(Redis::command('keys', ['*" . $this->getCacheKey($user_email, 'get', $url) . "*'])) = ".count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'get', $url) . '*'])));
                print("\r\ncount(Redis::command('keys', ['*" . $this->getCacheKey($user_email, 'post', $url) . "*'])) = ".count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'post', $url) . '*'])));
                print("\r\ncount(Redis::command('keys', ['*" . $this->getCacheKey($user_email, 'patch', $url) . "*'])) = ".count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'patch', $url) . '*'])));
                print("\r\ncount(Redis::command('keys', ['*" . $this->getCacheKey($user_email, 'delete', $url) . "*'])) = ".count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, 'delete', $url) . '*'])));
            }*/
        } else {
            $num_keys = count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, $method, $url) . '*']));
            $keys_count = 1;
            /*if ($num_keys != $keys_count) {
                print("\r\n------------------------------------------------------------\r\n");
                print("\r\ncount(Redis::command('keys', ['*" . $this->getCacheKey($user_email, $method, $url) . "*'])) = ".count(Redis::command('keys', ['*' . $this->getCacheKey($user_email, $method, $url) . '*'])));
            }*/
        }

        return ($num_keys == $keys_count) ? true : false ;
    }

    private function cacheExistsForModifiedData($post_data)
    {
        if ($post_data['method'] == 'ALL') {
            if ($this->cacheExists($post_data['user'], 'ALL', $post_data['url']) &&
                $this->cacheExists($post_data['user'], 'get', $post_data['url']) &&
                $this->cacheExists($post_data['user'], 'post', $post_data['url']) &&
                $this->cacheExists($post_data['user'], 'patch', $post_data['url']) &&
                $this->cacheExists($post_data['user'], 'delete', $post_data['url'])
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return $this->cacheExists($post_data['user'], $post_data['method'], $post_data['url']);
        }
    }

    // don't really need this. Just check for the cache being empty
    private function cacheDoesNotExistsForPostData($post_data)
    {
        if ($post_data['method'] == 'ALL') {
            if ($this->cacheExists($post_data['user'], 'ALL', $post_data['url']) ||
                $this->cacheExists($post_data['user'], 'get', $post_data['url']) ||
                $this->cacheExists($post_data['user'], 'post', $post_data['url']) ||
                $this->cacheExists($post_data['user'], 'patch', $post_data['url']) ||
                $this->cacheExists($post_data['user'], 'delete', $post_data['url'])
            ) {
                return false;
            } else {
                return true;
            }
        } else {
            return !$this->cacheExists($post_data['user'], $post_data['method'], $post_data['url']);
        }
    }

    private function getCachedResult($user_email = null, $method = null, $url = null)
    {
        if ($user_email == null) { $user_email = $this->postData()['user']; }
        if ($method == null) { $method = $this->postData()['method']; }
        if ($url == null) { $url = $this->postData()['url']; }

        if($method == 'ALL') {

            $result = $this->getCachedResultForSingleMethod($user_email, 'ALL', $url);

            if($this->getCachedResultForSingleMethod($user_email, 'get', $url) == $result &&
                $this->getCachedResultForSingleMethod($user_email, 'post', $url) == $result &&
                $this->getCachedResultForSingleMethod($user_email, 'patch', $url) == $result &&
                $this->getCachedResultForSingleMethod($user_email, 'delete', $url) == $result) {
            } else {
                print_r(Redis::command('keys', ['*']));
                print("\r\nCache::get(".$this->getCacheKey($user_email, 'ALL', $url).") = ".$this->getCachedResultForSingleMethod($user_email, 'ALL', $url));
                print("\r\nCache::get(".$this->getCacheKey($user_email, 'get', $url).") = ".$this->getCachedResultForSingleMethod($user_email, 'get', $url));
                print("\r\nCache::get(".$this->getCacheKey($user_email, 'post', $url).") = ".$this->getCachedResultForSingleMethod($user_email, 'post', $url));
                print("\r\nCache::get(".$this->getCacheKey($user_email, 'patch', $url).") = ".$this->getCachedResultForSingleMethod($user_email, 'patch', $url));
                print("\r\nCache::get(".$this->getCacheKey($user_email, 'delete', $url).") = ".$this->getCachedResultForSingleMethod($user_email, 'delete', $url));
                $this->assertTrue(false, 'this->getCacheResult('.$user_email.', '.$method.', '.$url.') [FAILED!!!]');
            }

            return $result;

        }

        return $this->getCachedResultForSingleMethod($user_email, $method, $url);

    }

    private function getCachedResultForSingleMethod($user_email, $method, $url)
    {

        $key = Redis::command('keys', ['*' . $this->getCacheKey($user_email, $method, $url) . '*']);

        if (count($key)) {
            $data = Redis::get($key[0]);
            return substr($data, strpos($data, '"') + 1, -2);
        }
        $this->assertTrue(false, 'no value for '.$this->getCacheKey($user_email, $method, $url).' found in cache [FAILED!!!]');
        return false;
    }

    // if you change any of the variable in the post data then use this
    private function getCachedResultForModifiedPostData($post_data)
    {
        return $this->getCachedResult($post_data['user'], $post_data['method'], $post_data['url']);
    }

    private function getCachedResultForPostDataAndMethodGet($post_data)
    {
        return $this->getCachedResult($post_data['user'], 'get', $post_data['url']);
    }

    private function getCachedResultForPostDataAndMethodPost($post_data)
    {
        return $this->getCachedResult($post_data['user'], 'post', $post_data['url']);
    }

    private function getCachedResultForPostDataAndMethodPatch($post_data)
    {
        return $this->getCachedResult($post_data['user'], 'patch', $post_data['url']);
    }

    private function getCachedResultForPostDataAndMethodDelete($post_data)
    {
        return $this->getCachedResult($post_data['user'], 'delete', $post_data['url']);
    }


    // Log functions

    private function LogMessage($function)
    {
        Log::debug('*****  ' . __CLASS__ . '()->' . $function . '()  *****');
    }
}