<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiTest extends TestCase
{
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

    private function receivedUnauthorized()
    {
        return [
            'code' => 401,
            'error' => 'Unauthorized'
        ];
    }

    private function receivedAllow()
    {
        return [
            'code' => 200,
            'data' => 'allow'
        ];
    }

    private function receivedDeny()
    {
        return [
            'code' => 200,
            'data' => 'deny'
        ];
    }

    // base rejection
    public function testNoPostData()
    {
        $this->LogMessage(__FUNCTION__);

        $this->loadData()
            ->post($this->base_url,[])
            ->seeJson($this->receivedUnauthorized());
    }


        /////  ONLY ONE POST VALUE  /////


    // base rejection (only key)
    public function testPostDataKeyOnly()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['secret']);
        unset($post_data['url']);
        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (only secret)
    public function testPostDataSecretOnly()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['key']);
        unset($post_data['url']);
        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (only url)
    public function testPostDataUrlOnly()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['key']);
        unset($post_data['secret']);
        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (only user)
    public function testPostDataUserOnly()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['key']);
        unset($post_data['secret']);
        unset($post_data['url']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }


    /////  REMOVE ONE POST VALUE  /////


    // base rejection (missing key)
    public function testPostDataWithNoKey()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['key']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (missing secret)
    public function testPostDataWithNoSecret()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['secret']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (missing url)
    public function testPostDataWithNoUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['url']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (missing user)
    public function testPostDataWithNoUser()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($post_data['user']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }


        /////  CHANGE ONE POST VALUE  /////


    // base rejection (change key)
    public function testPostDataKeyValueChanged()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['key'] = '23452345';

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (change secret)
    public function testPostDataSecretValueChanged()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['secret'] = '23452345';

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (change key, no user)
    public function testPostDataKeyValueChangedAndNoUser()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['key'] = '23452345';

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            /* ->post($this->admin_url.'/user', $this->data['user']) */
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    // base rejection (change secret, no user)
    public function testPostDataSecretValueChangedAndNoUser()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['secret'] = '23452345';

        $this->withSession(['user_logged_in' => true])
            ->post($this->admin_url.'/key', $this->data['key'])
            /* ->post($this->admin_url.'/user', $this->data['user']) */
            ->post($this->admin_url.'/group', $this->data['group'])
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1])
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
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
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['user'] = 'as1@as.com';

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }


        //////  ONLY ONE URL IN DB  //////


    // first match (single)
    public function testOnlyOneUrlInTheDBThatMatchesTestUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // first match (single) [deny]
    public function testOnlyOneUrlInTheDBThatMatchesTestUrlButIsSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first rejection (single)
    public function testOnlyOneUrlInTheDBThatDoesNotMatchTestUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['url'] = $this->alterTestUrl();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first rejection (single) [deny]
    public function testOnlyOneUrlInTheDBThatDoesNotMatchTestUrlAndTheDBUrlIsSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['url'] = $this->alterTestUrl();

        $this->data['urls'][0]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first alt-match (single)
    public function testOnlyOneAlternativeUrlInTheDBThatMatchesTestUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // first alt-rejection (single)
    public function testOnlyOneAlternativeUrlInTheDBThatDoesNotMatchTestUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['url'] = $this->alterDomainNameOnTestUrl();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first alt-rejection (single) [deny]
    public function testOnlyOneAlternativeUrlInTheDBThatDoesNotMatchTestUrlAndTheDBUrlIsSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $post_data['url'] = $this->alterTestUrl();

        $this->data['urls'][1]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first match
    public function testFirstMatch()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // first rejection (deny)
    public function testFirstMatchDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first rejection
    public function testFirstRejection()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][1]['url']);
        $this->data['urls'][2]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][2]['url']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // first alt-match
    public function testFirstAltMatch()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        unset($this->data['urls'][0]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // first alt-rejection
    public function testFirstAltMatchDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][1]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][1]['url']);
        $this->data['urls'][2]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][2]['url']);
        unset($this->data['urls'][0]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }


        /////  MATCHES FOR URLS IN SECOND PLACE IN THE DB  /////


    // second match
    public function testSecondMatch()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->test_url;

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // second rejection (deny)
    public function testSecondMatchDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->test_url;
        $this->data['urls'][1]['deny'] = true;

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // second rejection
    public function testSecondRejection()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][1]['url']);
        unset($this->data['urls'][2]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // second alt-match
    public function testSecondAltMatch()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // second alt-rejection (deny)
    public function testSecondAltRejectionDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['deny'] = true;

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // second alt-rejection
    public function testSecondAltRejection()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['url'] = $this->alterDomainNameOnTestUrl($this->data['urls'][0]['url']);
        $this->data['urls'][1]['deny'] = true;
        unset($this->data['urls'][2]);

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }


        /////  OTHER GROUP  /////

    // matching links in another group
    public function testMatchUrlsAreInAnotherGroup()
    {
        $this->LogMessage(__FUNCTION__);

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
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    // matching links in another group (all deny)
    public function testMatchUrlsAreInAnotherGroupAndAllSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

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
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

        /////  RE-ORDER  /////
        // (test to make sure the caching is working) //

    // deny moved from the bottom to the middle
    public function testAddUrlsThenMoveBottomUrlToTheMiddle()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][2]['deny'] = true;

        $this->loadData()
            ->patch($this->admin_url.'/url/order/1', ['order' => [1,3,2]])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    // deny moved from the bottom to the top
    public function testAddUrlsThenMoveBottomUrlToTheTop()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][2]['deny'] = true;

        $this->loadData()
            ->patch($this->admin_url.'/url/order/1', ['order' => [3,1,2]])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }


        ///// UPDATE  /////

    public function testUpdateUser()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $new_user = [
            'name' => 'New Dave',
            'email' => 'dave@as.com'
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->patch($this->admin_url.'/user/1', $new_user)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    public function testUpdateOnlyUrlToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $new_url = [
            'method' => $this->data['urls'][0]['method'],
            'url' => $this->data['urls'][0]['url'],
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => true
        ];

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    public function testUpdateFirstUrlToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $new_url = [
            'method' => $this->data['urls'][0]['method'],
            'url' => $this->data['urls'][0]['url'],
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => true
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    public function testUpdateOnlyUrlToDifferentUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $new_url = [
            'method' => $this->data['urls'][0]['method'],
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    public function testUpdateFirstUrlToDifferentUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['deny'] = true;

        $new_url = [
            'method' => $this->data['urls'][0]['method'],
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny())
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    public function testUpdateUrlWithSecondUrlSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][1]['deny'] = true;

        $new_url = [
            'method' => $this->data['urls'][0]['method'],
            'url' => $this->data['urls'][0]['url'].'/bill',
            'group_id' => $this->data['urls'][0]['group_id'],
            'deny' => $this->data['urls'][0]['deny']
        ];

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }


        /////  MOVE  /////

    public function testMoveSingleUrlToGroupWithOutUserInIt()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][0]['group_id'] = 2;

        $new_url = [
            'method' => $this->data['urls'][0]['method'],
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
            ->seeJson($this->receivedAllow())
            // add url to group 1
            ->patch($this->admin_url.'/url/1', $new_url)
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    public function testAttachUserToGroupWithUrlInIt()
    {
        $this->LogMessage(__FUNCTION__);

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
            ->seeJson($this->receivedDeny())
            ->delete($this->admin_url.'/group/2/user/1')
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny())
            ->post($this->admin_url.'/group/1/user', ['user_id' => 1])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }
    

        /////  DELETE  /////

    public function testRemoveUser()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->delete($this->admin_url.'/user/1')
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedUnauthorized());
    }

    public function testRemoveOnlyUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->delete($this->admin_url.'/url/1')
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    public function testRemoveFirstUrl()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->delete($this->admin_url.'/url/1')
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow());
    }

    public function testRemoveFirstUrlSecondUrlSetToDeny()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->data['urls'][1]['deny'] = true;

        $this->loadDataNoUrls()
            ->post($this->admin_url.'/url', $this->data['urls'][0])
            ->post($this->admin_url.'/url', $this->data['urls'][1])
            ->post($this->admin_url.'/url', $this->data['urls'][2])
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->delete($this->admin_url.'/url/1')
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }

    public function testRemoveGroup()
    {
        $this->LogMessage(__FUNCTION__);

        $post_data = $this->postData();

        $this->loadData()
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedAllow())
            ->delete($this->admin_url.'/group/1')
            ->post($this->base_url, $post_data)
            ->seeJson($this->receivedDeny());
    }


    // (alt = alternative match [i.e. not exact match])


    private function LogMessage($function)
    {
        Log::debug('*****  ' . __CLASS__ . '()->' . $function . '()  *****');
    }

}

