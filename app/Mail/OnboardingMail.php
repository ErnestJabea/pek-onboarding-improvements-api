<?php

namespace App\Mail;

use App\Models\OnboardingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $session;
    public $type;
    public $user;
    public $payload;

    /**
     * Create a new message instance.
     */
    public function __construct(OnboardingSession $session, string $type)
    {
        $this->session = $session;
        $this->type = $type;
        $this->user = $session->user;
        $this->payload = $session->payload ?? [];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->type === 'client') {
            return new Envelope(
                subject: 'FCP KORI SÉRÉNITÉ - Votre Profil Investisseur',
            );
        }

        // Compliance team subject, highlighting high risk if applicable
        $riskString = $this->session->risk_level === 'HIGH' ? '[RISQUE ÉLEVÉ]' : '[RISQUE NORMAL]';
        $clientName = strtoupper($this->user->last_name) . ' ' . $this->user->first_name;

        return new Envelope(
            subject: "Onboarding PEK - {$riskString} - Dossier de {$clientName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.onboarding_completed',
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
