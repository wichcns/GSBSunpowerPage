<?php

namespace App\Mail;

use App\Models\ContactEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContactEnquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactEnquiry $contactEnquiry) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'แจ้งเตือน: มีข้อมูลติดต่อใหม่จากเว็บไซต์ GSB SunPower',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-enquiries.new',
            with: [
                'logoPath' => $this->logoPath(),
            ],
        );
    }

    private function logoPath(): string
    {
        $publicLogoPath = public_path('image/GSBSUNPOWER-Edit.png');

        if (is_file($publicLogoPath)) {
            return $publicLogoPath;
        }

        return base_path('image/GSBSUNPOWER-Edit.png');
    }
}
