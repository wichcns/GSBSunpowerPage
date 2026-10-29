@php($siteKey = (string) config('services.turnstile.site_key'))

<div class="gx-form-protection">
    <div class="gx-honeypot" aria-hidden="true">
        <label>
            Website
            <input type="text" name="company_website" tabindex="-1" autocomplete="off">
        </label>
    </div>
    <input type="hidden" name="contact_form_started_at" value="{{ now()->timestamp }}">

    @if (filled($siteKey))
        @once
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endonce

        <div class="cf-turnstile gx-turnstile" data-sitekey="{{ $siteKey }}" data-action="{{ $action }}" data-theme="light" data-size="flexible"></div>
    @endif
</div>
