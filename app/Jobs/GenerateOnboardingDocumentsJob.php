<?php

namespace App\Jobs;

use App\Models\OnboardingSession;
use App\Mail\OnboardingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateOnboardingDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $session;
    protected $signature;

    /**
     * Create a new job instance.
     */
    public function __construct(OnboardingSession $session, string $signature)
    {
        $this->session = $session;
        $this->signature = $signature;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->session->user;
        $signatureBase64 = $this->signature;

        try {
            // 1. Decodes and saves the signature to private storage
            if (preg_match('/^data:image\/(\w+);base64,/', $signatureBase64, $type)) {
                $rawImage = substr($signatureBase64, strpos($signatureBase64, ',') + 1);
                $type = strtolower($type[1]);
                $decodedData = base64_decode($rawImage);

                if ($decodedData !== false) {
                    $sigFileName = 'sig_' . $this->session->id . '.' . $type;
                    $sigPath = 'secure_onboardings/signatures/' . $sigFileName;
                    Storage::put($sigPath, $decodedData);
                    $this->session->update(['signature_path' => $sigPath]);
                }
            }

            // Ensure directory exists
            if (!file_exists(storage_path('app/secure_onboardings'))) {
                mkdir(storage_path('app/secure_onboardings'), 0700, true);
            }

            // 2. Generate PDF files
            $pdfData = [
                'session' => $this->session,
                'payload' => $this->session->payload,
                'user' => $user,
                'signature' => $signatureBase64
            ];

            // KYC PDF
            $kycPath = 'secure_onboardings/kyc_' . $this->session->id . '.pdf';
            $kycPdf = Pdf::loadView('pdfs.onboarding.fiche_kyc', $pdfData);
            Storage::put($kycPath, $kycPdf->output());

            // Risk Profile PDF
            $riskPath = 'secure_onboardings/risk_' . $this->session->id . '.pdf';
            $riskPdf = Pdf::loadView('pdfs.onboarding.profil_investisseur', $pdfData);
            Storage::put($riskPath, $riskPdf->output());

            // LAB-FT PDF
            $labftPath = 'secure_onboardings/labft_' . $this->session->id . '.pdf';
            $labftPdf = Pdf::loadView('pdfs.onboarding.questionnaire_labft', $pdfData);
            Storage::put($labftPath, $labftPdf->output());

            // 3. Send email to user (only contains Risk Profile PDF)
            $clientMail = new OnboardingMail($this->session, 'client');
            $clientMail->attachData($riskPdf->output(), "profil_investisseur_{$user->last_name}.pdf", [
                'mime' => 'application/pdf'
            ]);
            Mail::to($user->email)->send($clientMail);

            // 4. Send email to Kori compliance (contains all 3 PDFs)
            $complianceEmail = env('MAIL_COMPLIANCE_ADDRESS', 'fcp.koriserenite@koriassetmanagement.com');
            $complianceMail = new OnboardingMail($this->session, 'compliance');
            $complianceMail->attachData($kycPdf->output(), "1_fiche_kyc_{$user->last_name}.pdf", ['mime' => 'application/pdf']);
            $complianceMail->attachData($riskPdf->output(), "2_profil_investisseur_{$user->last_name}.pdf", ['mime' => 'application/pdf']);
            $complianceMail->attachData($labftPdf->output(), "3_questionnaire_labft_{$user->last_name}.pdf", ['mime' => 'application/pdf']);
            
            Mail::to($complianceEmail)->send($complianceMail);

        } catch (\Exception $e) {
            \Log::error("Failed to generate onboarding documents/emails for session {$this->session->id}: " . $e->getMessage());
        }
    }
}
