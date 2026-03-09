<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRoleAssigned extends Notification
{
    /**
     * @param User   $assignedUser  The user receiving the admin role (also the notifiable)
     * @param string $scopeType     'Faculty' or 'Department'
     * @param string $scopeName     The faculty or department name
     */
    public function __construct(
        public readonly User   $assignedUser,
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
            ->subject("You've been assigned as {$this->scopeType} Administrator — UniOne")
            ->view('emails.admin-assigned', [
                'user'      => $this->assignedUser,
                'scopeType' => $this->scopeType,
                'scopeName' => $this->scopeName,
                'loginUrl'  => route('dashboard.login'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'admin_role_assigned',
            'title'      => "{$this->scopeType} Administrator Role Assigned",
            'body'       => "You have been assigned as {$this->scopeType} Administrator for {$this->scopeName}. You are required to set a new password on your next login.",
            'scope_type' => $this->scopeType,
            'scope_name' => $this->scopeName,
        ];
    }
}
