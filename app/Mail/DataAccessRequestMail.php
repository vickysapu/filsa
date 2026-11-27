<?php

namespace App\Mail;

use App\Models\DataAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataAccessRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DataAccessRequest $dataRequest,
        public array $tableNames,
        public string $oneTimeKey,
        public string $audience = 'user',
        public ?string $confirmUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $project = html_entity_decode($this->dataRequest->project_name, ENT_QUOTES);
        $isAdmin = $this->audience === 'admin';

        return new Envelope(
            subject: $isAdmin
                ? "Permintaan akses data baru: {$project}"
                : "Konfirmasi permintaan akses data: {$project}",
            replyTo: $isAdmin
                ? [new Address(
                    $this->dataRequest->requester_email,
                    html_entity_decode($this->dataRequest->requester_name, ENT_QUOTES)
                )]
                : [],
        );
    }

    public function content(): Content
    {
        $r = $this->dataRequest;

        return new Content(
            view: $this->audience === 'admin'
                ? 'emails.data-access.admin'
                : 'emails.data-access.verify',
            with: [
                'requester_name' => html_entity_decode($r->requester_name, ENT_QUOTES),
                'requester_email' => $r->requester_email,
                'projectName' => html_entity_decode($r->project_name, ENT_QUOTES),
                'projectUrl' => $r->project_url,
                'purpose' => $r->purpose,
                'tables' => $this->tableNames,
                'branchName' => $r->branch_name,
                'requestId' => $r->id,
                'ipAddress' => $r->ip_address,
                'oneTimeKey' => $this->oneTimeKey,
                'verificationUrl' => $this->confirmUrl,
                'expiresIn' => '48 jam',
                'adminUrl' => route('admin.data-access.requests'),
            ],
        );
    }
}