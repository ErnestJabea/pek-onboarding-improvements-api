<?php

namespace App\Mail;

use App\Models\OnboardingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $session;
    public $user;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(OnboardingSession $session, string $reason)
    {
        $this->session = $session;
        $this->user = $session->user;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PEK - Informations Complémentaires Requises',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.onboarding_rejected',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
