@if (session('success'))
    <dialog class="gx-enquiry-success-dialog" data-gx-enquiry-success aria-labelledby="enquiry-success-title">
        <div class="gx-enquiry-success-orbit" aria-hidden="true"><span></span><i>✓</i></div>
        <p class="gx-enquiry-success-kicker" data-gx-enquiry-success-kicker>MESSAGE RECEIVED</p>
        <h2 id="enquiry-success-title" data-gx-enquiry-success-title>Your enquiry is on its way.</h2>
        <p data-gx-enquiry-success-copy>Thank you for getting in touch. Our team will review your details and contact you shortly.</p>
        <div class="gx-enquiry-success-meta"><span aria-hidden="true">●</span><span data-gx-enquiry-success-status>Saved securely in our system</span></div>
        <button class="gx-enquiry-success-close" type="button" data-gx-enquiry-success-close>
            <span data-gx-enquiry-success-button>Continue</span><i aria-hidden="true">↗</i>
        </button>
    </dialog>
@endif
