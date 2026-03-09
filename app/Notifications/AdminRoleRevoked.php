<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRoleRevoked extends Notification
{
    /**
     * @param User   $revokedUser  The user losing the admin role (also the notifiable)
     * @param string $scopeType    'Faculty' or 'Department'
     * @param string $scopeName    The faculty or department name
     */
    public function __construct(
        public readonly User   $revokedUser,
        public readonly string $scopeType,
        public readonly string $scopeName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your {$this->scopeType} Administrator role has been revoked — UniOne")
            ->view('emails.admin-revoked', [
                'user'      => $this->revokedUser,
                'scopeType' => $this->scopeType,
                'scopeName' => $this->scopeName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'admin_role_revoked',
            'title'      => "{$this->scopeType} Administrator Role Revoked",
            'body'       => "Your {$this->scopeType} Administrator role for {$this->scopeName} has been revoked.",
            'scope_type' => $this->scopeType,
            'scope_name' => $this->scopeName,
        ];
    }
}
