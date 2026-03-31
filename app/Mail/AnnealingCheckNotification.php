<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\AnnealingCheck;
use App\Models\User;

class AnnealingCheckNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $annealingCheck;
    public $user;
    public $type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(AnnealingCheck $annealingCheck, User $user, string $type)
    {
        $this->annealingCheck = $annealingCheck;
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $subject = $this->type === 'new_submission' 
            ? 'New Annealing Check Submitted' 
            : 'Annealing Check Updated';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.annealing-check-notification',
            with: [
                'annealingCheck' => $this->annealingCheck,
                'user' => $this->user,
                'type' => $this->type,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
