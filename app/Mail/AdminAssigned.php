<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAssigned extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param User   $assignedUser  The user being granted the admin role
     * @param string $scopeType     'Faculty' or 'Department'
     * @param string $scopeName     The faculty or department name
     */
    public function __construct(
        public readonly User   $assignedUser,
        public readonly string $scopeType,
        public readonly string $scopeName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->assignedUser->email,
            subject: "You've been assigned as {$this->scopeType} Administrator — UniOne",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-assigned',
            with: [
                'user'      => $this->assignedUser,
                'scopeType' => $this->scopeType,
                'scopeName' => $this->scopeName,
                'loginUrl'  => route('dashboard.login'),
            ],
        );
    }
}
