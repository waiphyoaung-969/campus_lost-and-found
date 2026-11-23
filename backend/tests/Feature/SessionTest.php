<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const string FRONTEND = 'http://localhost:5173';

    public function test_guest_receives_401_from_current_member_endpoint(): void
    {
        $this->get('/api/v1/me', ['Origin' => self::FRONTEND])
            ->assertUnauthorized()
            ->assertHeader('Access-Control-Allow-Origin', self::FRONTEND);
    }

    public function test_stateful_spa_can_read_its_member_profile_with_a_session_cookie(): void
    {
        $member = User::factory()->microsoft()->create();
        $this->withMemberSession($member);

        $this->getJson('/api/v1/me', ['Origin' => self::FRONTEND])
            ->assertOk()
            ->assertExactJson(['data' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => 'member',
            ]]);
    }

    public function test_unlisted_origin_cannot_use_the_member_session(): void
    {
        $this->withMemberSession(User::factory()->create());

        $this->getJson('/api/v1/me', ['Origin' => 'http://localhost:5174'])
            ->assertUnauthorized()
            ->assertHeader('Access-Control-Allow-Origin', self::FRONTEND);
    }

    public function test_guest_with_csrf_token_cannot_log_out(): void
    {
        $this->withSession(['_token' => 'guest-csrf-token']);

        $this->postJson('/api/v1/logout', [], [
            'Origin' => self::FRONTEND,
            'X-CSRF-TOKEN' => 'guest-csrf-token',
        ])->assertUnauthorized();
    }

    public function test_logout_requires_csrf_protection_and_invalidates_the_session(): void
    {
        config(['sanctum.middleware.validate_csrf_token' => EnforceCsrfDuringTests::class]);
        $this->withMemberSession(User::factory()->create());
        $sessionId = session()->getId();
        $origin = ['Origin' => self::FRONTEND];

        $csrf = $this->get('/sanctum/csrf-cookie', $origin)
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', self::FRONTEND)
            ->assertHeader('Access-Control-Allow-Credentials', 'true');

        $this->assertTrue($csrf->getCookie(config('session.cookie'))->isHttpOnly());
        $this->postJson('/api/v1/logout', [], $origin)->assertStatus(419);

        $this->postJson('/api/v1/logout', [], $origin + [
            'X-XSRF-TOKEN' => $csrf->getCookie('XSRF-TOKEN', false)->getValue(),
        ])->assertNoContent();

        $this->assertGuest('web');
        $this->assertNotSame($sessionId, session()->getId());

        Auth::forgetGuards();
        $this->getJson('/api/v1/me', $origin)->assertUnauthorized();
    }

    public function test_logout_preflight_allows_the_frontend_and_xsrf_header(): void
    {
        $this->options('/api/v1/logout', [], [
            'Origin' => self::FRONTEND,
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'X-XSRF-TOKEN',
        ])->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', self::FRONTEND)
            ->assertHeader('Access-Control-Allow-Credentials', 'true')
            ->assertHeader('Access-Control-Allow-Headers', 'accept, content-type, x-xsrf-token');
    }

    private function withMemberSession(User $member): void
    {
        $this->withSession([Auth::guard('web')->getName() => $member->getKey()]);
        session()->save();
        $this->withCredentials()->withCookie(config('session.cookie'), session()->getId());
        session()->flush();
    }
}

class EnforceCsrfDuringTests extends ValidateCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
