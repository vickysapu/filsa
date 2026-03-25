<?php

namespace App\Mail;

use App\Models\DataAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataAccessApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DataAccessRequest $request,
        public array $tokens 
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Akses Data Disetujui - Token Akses',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.data-access-approved',
            with: [
                'projectName' => $this->request->project_name,
                'tokens' => $this->tokens,
                'warning' => 'Simpan token ini di tempat aman! Token hanya ditampilkan sekali.',
            ],
        );
    }
}