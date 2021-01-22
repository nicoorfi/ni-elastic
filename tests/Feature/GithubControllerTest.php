<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Tests\TestCase;

class GithubControllerTest extends TestCase
{
    /**
     * @test
     */
    public function redirect_forwards_to_github()
    {
        $response = $this->get(route('github.redirect'));
        $targetUrl = $response->baseResponse->getTargetUrl();

        $this->assertStringContainsString('https://github.com/login/oauth/authorize?', $targetUrl);
    }

    /**
     * @test
     */
    public function redirect_to_login_if_normal_user_exists()
    {
        DB::table('users')->insert(
            [
                'email' => 'foo@bar.com',
                'username' => 'john',
                'github' => false,
                'avatar_url' => 'https://awesome.avatar-url.com',
                'created_at' => '2020-08-19 09:45:08',
                'updated_at' => '2020-08-19 09:45:08'
            ]
        );

        $githubUser = $this->createMock(SocialiteUser::class);
        $githubUser->method('getName')->willReturn('John');
        $githubUser->method('getEmail')->willReturn('foo@bar.com');
        $githubUser->method('getAvatar')->willReturn('https://awesome.avatar-url.com');

        $githubDriver = $this->createMock(GithubProvider::class);
        $githubDriver->method('user')->willReturn($githubUser);

        Socialite::shouldReceive('driver')->with('github')->andReturn($githubDriver);

        $response = $this->get(route('github.handle'));
        $response->assertRedirect(route('sign-in'));
        $response->assertSessionHas('info', 'You already have an account.');
    }

    /**
     * @test
     */
    public function github_logs_in_if_user_exists()
    {
        DB::table('users')->insert(
            [
                'email' => 'foo@bar.com',
                'username' => 'john',
                'github' => true,
                'avatar_url' => 'https://awesome.avatar-url.com',
                'created_at' => '2020-08-19 09:45:08',
                'updated_at' => '2020-08-19 09:45:08'
            ]
        );

        $githubUser = $this->createMock(SocialiteUser::class);
        $githubUser->method('getName')->willReturn('John');
        $githubUser->method('getEmail')->willReturn('foo@bar.com');
        $githubUser->method('getAvatar')->willReturn('https://awesome.avatar-url.com');

        $githubDriver = $this->createMock(GithubProvider::class);
        $githubDriver->method('user')->willReturn($githubUser);

        Socialite::shouldReceive('driver')->with('github')->andReturn($githubDriver);

        $response = $this->get(route('github.handle'));
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * @test
     */
    public function handle_populates_session_with_github_user_info_if_user_doesnt_exists()
    {
        $githubUser = $this->createMock(SocialiteUser::class);
        $githubUser->method('getName')->willReturn('John');
        $githubUser->method('getEmail')->willReturn('foo@bar.com');
        $githubUser->method('getAvatar')->willReturn('https://awesome.avatar-url.com');

        $githubDriver = $this->createMock(GithubProvider::class);
        $githubDriver->method('user')->willReturn($githubUser);

        Socialite::shouldReceive('driver')->with('github')->andReturn($githubDriver);

        $response = $this->get(route('github.handle'));

        $response->assertRedirect(route('sign-up'));
        $response->assertSessionHas('githubUser');
        $response->assertSessionHasAll(['githubUser' => [
            'name' => 'John',
            'email' => 'foo@bar.com',
            'avatar_url' => 'https://awesome.avatar-url.com',
        ]]);
    }
}