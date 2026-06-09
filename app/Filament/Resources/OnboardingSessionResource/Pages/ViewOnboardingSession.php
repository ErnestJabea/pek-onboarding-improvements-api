<?php

namespace App\Filament\Resources\OnboardingSessionResource\Pages;

use App\Filament\Resources\OnboardingSessionResource;
use App\Mail\OnboardingValidatedMail;
use App\Mail\OnboardingRejectedMail;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewOnboardingSession extends ViewRecord
{
    protected static string $resource = OnboardingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('validate')
                ->label('Valider le dossier')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Valider cet onboarding')
                ->modalDescription('Êtes-vous sûr de vouloir valider ce dossier d\'onboarding ? Le client recevra un e-mail de confirmation d\'activation de compte.')
                ->visible(fn ($record) => $record && $record->status === 'completed')
                ->action(function ($record) {
                    $record->update(['status' => 'validated']);
                    
                    // Send validation email
                    Mail::to($record->user->email)->send(new OnboardingValidatedMail($record));

                    Notification::make()
                        ->title('Dossier d\'onboarding validé avec succès !')
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('Rejeter le dossier')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Motif du rejet')
                        ->placeholder('Indiquez ici la raison du rejet (ex: CNI expirée, justificatif de domicile non lisible, etc.)')
                        ->required()
                        ->rows(3),
                ])
                ->modalHeading('Rejeter cet onboarding')
                ->modalDescription('Veuillez indiquer le motif du rejet. Le client en sera notifié par e-mail.')
                ->visible(fn ($record) => $record && $record->status === 'completed')
                ->action(function ($record, array $data) {
                    // Save reason in payload and change status to rejected
                    $payload = $record->payload ?? [];
                    $payload['rejection_reason'] = $data['reason'];
                    
                    $record->update([
                        'status' => 'rejected',
                        'payload' => $payload
                    ]);

                    // Send rejection email
                    Mail::to($record->user->email)->send(new OnboardingRejectedMail($record, $data['reason']));

                    Notification::make()
                        ->title('Dossier d\'onboarding rejeté et notifié au client.')
                        ->danger()
                        ->send();
                }),
        ];
    }
}
