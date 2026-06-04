<?php

namespace App\Http\Controllers;

use App\Models\OnboardingSession;
use App\Http\Requests\StoreKYCRequest;
use App\Http\Requests\StoreLABFTRequest;
use App\Services\ProfilRiskService;
use App\Jobs\GenerateOnboardingDocumentsJob;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    /**
     * Get or create the current user's onboarding session.
     */
    public function status()
    {
        $session = Auth::user()->onboardingSession()->firstOrCreate([], [
            'current_step' => 'kyc',
            'status' => 'in_progress',
            'payload' => []
        ]);

        return response()->json([
            'session' => $session
        ]);
    }

    /**
     * Save progress of the current step in the onboarding session.
     * Keeps it extremely lenient to allow saving partial drafts during poor network.
     */
    public function saveProgress(Request $request)
    {
        $session = Auth::user()->onboardingSession()->firstOrCreate([], [
            'current_step' => 'kyc',
            'status' => 'in_progress',
            'payload' => []
        ]);

        $step = $request->input('step', $session->current_step);
        $payload = $request->input('payload', []);

        // Merge incoming payload into the existing payload
        $currentPayload = $session->payload ?? [];
        $mergedPayload = array_merge($currentPayload, $payload);

        $session->current_step = $step;
        $session->payload = $mergedPayload;
        $session->save();

        return response()->json([
            'message' => 'Progression enregistrée avec succès.',
            'session' => $session
        ]);
    }

    /**
     * Finalize the onboarding session, validate all answers, calculate risk, and start PDF job.
     */
    public function finalize(Request $request)
    {
        $session = Auth::user()->onboardingSession;

        if (!$session) {
            return response()->json(['message' => 'Aucune session d\'onboarding trouvée.'], 404);
        }

        if ($session->status === 'completed') {
            return response()->json(['message' => 'L\'onboarding est déjà finalisé.'], 400);
        }

        $payload = $session->payload ?? [];

        // 1. Validate KYC data
        $kycValidator = Validator::make($payload, (new StoreKYCRequest())->rules());
        if ($kycValidator->fails()) {
            return response()->json([
                'message' => 'Veuillez compléter correctement les informations d\'identité.',
                'errors' => $kycValidator->errors()
            ], 422);
        }

        // 2. Validate LAB-FT data
        $labftValidator = Validator::make($payload, (new StoreLABFTRequest())->rules());
        if ($labftValidator->fails()) {
            return response()->json([
                'message' => 'Veuillez compléter correctement le questionnaire LAB-FT.',
                'errors' => $labftValidator->errors()
            ], 422);
        }

        // 3. Validate Risk Profiler data
        $riskValidator = Validator::make($payload, [
            'tranche_revenus' => 'required|string',
            'epargne_possible' => 'required',
            'niveau_risque' => 'required|string',
            'conscience_risque' => 'required',
            'objectif_invest' => 'required|string',
            'horizon_terme' => 'required|string',
            'niveau_perf' => 'required',
            'connaissance_marche' => 'required|string',
            'invest_anterieurs' => 'required',
        ]);
        if ($riskValidator->fails()) {
            return response()->json([
                'message' => 'Veuillez compléter correctement le profil investisseur.',
                'errors' => $riskValidator->errors()
            ], 422);
        }

        // 4. Validate Signature
        $request->validate([
            'signature' => 'required|string', // Base64 string representing the signature image
        ]);

        // 5. Server-side Risk Score Calculation
        $riskService = new ProfilRiskService();
        $score = $riskService->calculateScore($payload);
        $profileName = $riskService->getProfileName($score);

        $payload['risk_score'] = $score;
        $payload['risk_profile'] = $profileName;

        // 6. Server-side LAB/FT High-Risk Flag Evaluation
        $riskLevel = 'LOW';
        if (
            ($payload['ppe'] ?? 'Non') === 'Oui' ||
            ($payload['pays_risque'] ?? 'Non') === 'Oui' ||
            ($payload['secteur_sensible'] ?? 'Non') === 'Oui' ||
            ($payload['condamnation'] ?? 'Non') === 'Oui'
        ) {
            $riskLevel = 'HIGH';
        }

        // Update session
        $session->payload = $payload;
        $session->risk_level = $riskLevel;
        $session->status = 'completed';
        $session->current_step = 'completed';
        $session->save();

        // 7. Dispatch Background Job
        GenerateOnboardingDocumentsJob::dispatch($session, $request->signature);

        // 8. Create In-App Notification
        Notification::create([
            'user_id' => $session->user_id,
            'title' => 'Onboarding validé ! 🎉',
            'body' => "Votre dossier d'onboarding a été soumis avec succès pour validation.",
            'type' => 'success'
        ]);

        return response()->json([
            'message' => 'Onboarding finalisé avec succès.',
            'session' => $session
        ]);
    }
}
