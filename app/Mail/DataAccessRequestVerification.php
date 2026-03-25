<?php

namespace App\Mail;

use App\Models\DataAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataAccessRequestVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DataAccessRequest $request,
        public string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Verifikasi Permintaan Akses Data - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.data-access-verification',
            with: [
                'requester_name' => $this->request->requester_name,
                'projectName' => $this->request->project_name,
                'purpose' => $this->request->purpose,
                'tables' => implode(', ', $this->request->requested_tables),
                'verificationUrl' => $this->verificationUrl,
                'expiresIn' => '24 jam',
            ],
        );
    }
}