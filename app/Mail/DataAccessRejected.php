<?php

namespace App\Mail;

use App\Models\DataAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataAccessRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DataAccessRequest $request
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Permintaan Akses Data Ditolak',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.data-access-rejected',
            with: [
                'projectName' => $this->request->project_name,
                'reason' => $this->request->rejection_reason,
                'contact' => 'hubungi administrator jika ada pertanyaan',
            ],
        );
    }
}