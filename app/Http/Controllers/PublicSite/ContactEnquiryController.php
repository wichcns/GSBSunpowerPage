<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Mail\NewContactEnquiryNotification;
use App\Models\ContactEnquiry;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactEnquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:80'],
            'service' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'in:contact,home,consultation'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'contact_form_started_at' => ['required', 'integer'],
            'cf-turnstile-response' => ['nullable', 'string', 'max:2048'],
        ]);

        $this->ensureHumanSubmission($request, $data);

        $contactEnquiry = ContactEnquiry::query()->create([
            'name' => $data['name'], 'company' => $data['company'] ?? null,
            'email' => $data['email'], 'phone' => $data['phone'],
            'services' => $data['services'] ?? array_filter([$data['service'] ?? null]),
            'budget' => $data['budget'] ?? null, 'project_location' => $data['location'] ?? null,
            'website' => $data['website'] ?? null, 'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? 'contact',
        ]);

        try {
            Mail::to((string) config('mail.contact_enquiry_notification_address'))
                ->send(new NewContactEnquiryNotification($contactEnquiry));
        } catch (Throwable $exception) {
            Log::error('Unable to send contact enquiry notification.', [
                'contact_enquiry_id' => $contactEnquiry->id,
                'exception' => $exception,
            ]);
        }

        return back()->with('success', 'Thank you. Our team has received your enquiry.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureHumanSubmission(Request $request, array $data): void
    {
        if (filled($data['company_website'] ?? null)) {
            Log::notice('Blocked suspected spam contact enquiry.', [
                'reason' => 'honeypot',
                'source' => $data['source'] ?? 'contact',
            ]);

            throw ValidationException::withMessages([
                'form' => 'We could not process your submission. Please try again.',
            ]);
        }

        $submittedAfter = now()->timestamp - (int) $data['contact_form_started_at'];

        if ($submittedAfter < 3 || $submittedAfter > 7200) {
            throw ValidationException::withMessages([
                'form' => 'Please review the form and submit it again.',
            ]);
        }

        $siteKey = (string) config('services.turnstile.site_key');
        $secretKey = (string) config('services.turnstile.secret_key');

        if (blank($siteKey) && blank($secretKey)) {
            return;
        }

        if (blank($siteKey) || blank($secretKey)) {
            Log::error('Turnstile configuration is incomplete.');

            throw ValidationException::withMessages([
                'form' => 'Form verification is temporarily unavailable. Please try again later.',
            ]);
        }

        $token = (string) ($data['cf-turnstile-response'] ?? '');

        if ($token === '') {
            throw ValidationException::withMessages([
                'form' => 'Please complete the security check and try again.',
            ]);
        }

        try {
            $verification = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Turnstile verification request failed.', ['exception' => $exception]);

            throw ValidationException::withMessages([
                'form' => 'Form verification is temporarily unavailable. Please try again later.',
            ]);
        }

        $source = (string) ($data['source'] ?? 'contact');

        if (! $verification->successful()
            || $verification->json('success') !== true
            || $verification->json('action') !== $source) {
            Log::notice('Blocked invalid Turnstile contact enquiry.', [
                'source' => $source,
                'error_codes' => $verification->json('error-codes', []),
            ]);

            throw ValidationException::withMessages([
                'form' => 'Security verification failed. Please try again.',
            ]);
        }
    }
}
