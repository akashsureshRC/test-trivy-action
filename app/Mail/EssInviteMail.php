<?php

namespace App\Mail;

use App\Mail\Ess\EssQueueableMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Hrm\Employee;

class EssInviteMail extends EssQueueableMail
{
    public string $setupUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Employee $employee, string $token)
    {
        $this->employee = $employee;
        $this->setupUrl = route('ess.setup', $token);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Employee Self-Service - Set Up Your Account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ess-invite',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
