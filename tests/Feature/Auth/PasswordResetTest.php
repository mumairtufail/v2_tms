<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendMailJob;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Reset emails go out as a queued SendMailJob; pull the token from the link inside it.
     */
    private function requestResetToken(User $user): string
    {
        Queue::fake();

        $this->post('/forgot-password', ['email' => $user->email]);

        $token = null;
        Queue::assertPushed(SendMailJob::class, function (SendMailJob $job) use ($user, &$token) {
            if ($job->to !== $user->email || ! $job->mailable instanceof ResetPasswordMail) {
                return false;
            }

            $token = basename(parse_url($job->mailable->url, PHP_URL_PATH));

            return true;
        });

        return $token;
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($this->requestResetToken($user));
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $token = $this->requestResetToken($user);

        $this->get('/reset-password/'.$token)->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        $token = $this->requestResetToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }
}
