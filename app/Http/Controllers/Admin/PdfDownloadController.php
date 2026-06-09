<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnboardingSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfDownloadController extends Controller
{
    /**
     * Télécharger un PDF d'onboarding depuis le backoffice.
     *
     * @param \App\Models\OnboardingSession $session  Résolu automatiquement par le model binding Laravel
     * @param string                        $type     Le type de document : kyc | risk | labft
     */
    public function download(OnboardingSession $session, string $type)
    {
        // Charger la relation user si pas encore chargée
        $session->loadMissing('user');

        $allowedTypes = ['kyc', 'risk', 'labft'];
        if (!in_array($type, $allowedTypes)) {
            abort(404);
        }

        $prefix = $type . '_';

        $viewName = match ($type) {
            'kyc'   => 'pdfs.onboarding.fiche_kyc',
            'risk'  => 'pdfs.onboarding.profil_investisseur',
            'labft' => 'pdfs.onboarding.questionnaire_labft',
        };

        $fileLabel = match ($type) {
            'kyc'   => 'fiche_kyc_',
            'risk'  => 'profil_investisseur_',
            'labft' => 'questionnaire_labft_',
        };

        $lastName  = $session->user->last_name  ?? 'client';
        $firstName = $session->user->first_name ?? '';
        $filename  = $fileLabel . $lastName . '_' . $firstName . '.pdf';

        $storagePath = 'secure_onboardings/' . $prefix . $session->id . '.pdf';

        // Si le PDF est déjà généré et stocké, le servir directement
        if (Storage::exists($storagePath)) {
            return Storage::download($storagePath, $filename);
        }

        // Générer à la volée
        $signatureBase64 = '';
        if ($session->signature_path && Storage::exists($session->signature_path)) {
            $imgData = Storage::get($session->signature_path);
            $mime    = 'image/png';
            if (str_ends_with($session->signature_path, '.jpg') || str_ends_with($session->signature_path, '.jpeg')) {
                $mime = 'image/jpeg';
            }
            $signatureBase64 = 'data:' . $mime . ';base64,' . base64_encode($imgData);
        }

        // Charger le logo en base64 pour l'affichage dans le PDF
        $logoBase64 = '';
        $logoPath   = public_path('logo-kori.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdfData = [
            'session'   => $session,
            'payload'   => $session->payload ?? [],
            'user'      => $session->user,
            'signature' => $signatureBase64,
            'logo'      => $logoBase64,
        ];

        $pdf = Pdf::loadView($viewName, $pdfData)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
