<?php

use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WebTest extends TestCase
{
    private $base_url = 'admin';

    public function testLoginPage()
    {
        $this->visit($this->base_url)
            ->see('Login')
            ->see('Email')
            ->see('Password');
    }

    public function testUnsuccessfulLogIntoAdmin()
    {
        $this->visit($this->base_url)
            ->type('some username', 'username')
            ->type('some password', 'password')
            ->press('Login')
            ->seePageIs('admin')
            ->see('The Username or Password is Incorrect');
    }

    public function testSuccessfulLogIntoAdmin()
    {
        $this->visit($this->base_url)
            ->type('test@test.com', 'username')
            ->type('test@test.com', 'password')
            ->press('Login')
            ->seePageIs('admin/panel');
    }

    public function testCanNotLookAtHomeWithoutLoggingIn()
    {
        $this->visit($this->base_url.'/panel')
            ->see('You need to login first')
            ->seePageIs('admin');
    }

    public function testCanSeeTabsOnHomeScreenTabs()
    {
        $this->withoutMiddleware();

        $this->visit($this->base_url.'/panel')
            ->see('Keys')
            ->see('Groups')
            ->see('Users');
    }

    //

    public function testCanSeeLogoutLinkOnHomeScreenTabs()
    {
        $this->withoutMiddleware();

        $this->withSession(['user_logged_in' => true])
            ->visit($this->base_url.'/panel')
            ->see('Logout');
    }

    public function testCanLogout()
    {
        $this->withSession(['user_logged_in' => true])
            ->visit($this->base_url.'/panel')
            ->click('Logout')
            ->seePageIs('admin');
    }

}

