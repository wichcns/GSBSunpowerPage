<?php

namespace Tests\Feature;

use App\Mail\NewContactEnquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactEnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.turnstile.site_key', null);
        config()->set('services.turnstile.secret_key', null);
    }

    public function test_contact_enquiry_is_saved_from_the_public_form(): void
    {
        Mail::fake();

        $response = $this->post(route('contact-enquiries.store'), [
            'name' => 'Narin Test',
            'company' => 'GSB Test',
            'email' => 'narin@example.com',
            'phone' => '0988285767',
            'service' => 'Solar PPA',
            'message' => 'Please contact me about a solar project.',
            'source' => 'contact',
            'contact_form_started_at' => now()->subSeconds(5)->timestamp,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('contact_enquiries', [
            'email' => 'narin@example.com',
            'source' => 'contact',
            'status' => 'new',
        ]);

        Mail::assertSent(NewContactEnquiryNotification::class, function (NewContactEnquiryNotification $mail): bool {
            $mail->assertSeeInHtml('NEW WEBSITE ENQUIRY');
            $mail->assertSeeInHtml('GSB SunPower Group');

            return $mail->hasTo('marketing.gsbsunpower@gmail.com')
                && $mail->contactEnquiry->email === 'narin@example.com';
        });
    }

    public function test_contact_enquiry_with_honeypot_data_is_blocked(): void
    {
        Mail::fake();

        $response = $this->from(route('contact'))
            ->post(route('contact-enquiries.store'), [
                'name' => 'Spam Bot',
                'email' => 'spam@example.com',
                'phone' => '0000000000',
                'source' => 'contact',
                'company_website' => 'https://spam.example',
                'contact_form_started_at' => now()->subSeconds(5)->timestamp,
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors('form');
        $this->assertDatabaseCount('contact_enquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_contact_enquiry_requires_a_valid_turnstile_response_when_configured(): void
    {
        Mail::fake();
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'contact',
            ]),
        ]);

        $response = $this->post(route('contact-enquiries.store'), [
            'name' => 'Narin Test',
            'email' => 'narin@example.com',
            'phone' => '0988285767',
            'source' => 'contact',
            'contact_form_started_at' => now()->subSeconds(5)->timestamp,
            'cf-turnstile-response' => 'valid-test-token',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_enquiries', ['email' => 'narin@example.com']);
        Http::assertSentCount(1);
        Mail::assertSent(NewContactEnquiryNotification::class);
    }
}
