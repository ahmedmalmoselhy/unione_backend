<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRevoked extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param User   $revokedUser  The user whose admin role is being revoked
     * @param string $scopeType    'Faculty' or 'Department'
     * @param string $scopeName    The faculty or department name
     */
    public function __construct(
        public readonly User   $revokedUser,
        public readonly string $scopeType,
        public readonly string $scopeName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->revokedUser->email,
            subject: "Your {$this->scopeType} Administrator role has been revoked — UniOne",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-revoked',
            with: [
                'user'      => $this->revokedUser,
                'scopeType' => $this->scopeType,
                'scopeName' => $this->scopeName,
            ],
        );
    }
}
