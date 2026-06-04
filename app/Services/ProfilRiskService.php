<?php

namespace App\Services;

class ProfilRiskService
{
    /**
     * Calculate the risk score based on investor responses.
     */
    public function calculateScore(array $data): int
    {
        $score = 0;

        // 1. Revenues (0 to 2 pts)
        $revenus = $data['tranche_revenus'] ?? '';
        if ($revenus === '500k_1_5m') {
            $score += 1;
        } elseif ($revenus === 'plus_1_5m') {
            $score += 2;
        }

        // 2. Savings capability (Oui = 2 pts)
        $epargne = $data['epargne_possible'] ?? '';
        if ($epargne === 'Oui' || $epargne === true) {
            $score += 2;
        }

        // 3. Accepted risk (faible = 1, moyen = 2, max = 4 pts)
        $risque = $data['niveau_risque'] ?? '';
        if ($risque === 'faible') {
            $score += 1;
        } elseif ($risque === 'moyen') {
            $score += 2;
        } elseif ($risque === 'max') {
            $score += 4;
        }

        // 4. Risk consciousness (Oui = 1 pt)
        $conscience = $data['conscience_risque'] ?? '';
        if ($conscience === 'Oui' || $conscience === true) {
            $score += 1;
        }

        // 5. Objective (1 to 2 pts according to choice)
        $objectif = $data['objectif_invest'] ?? '';
        if ($risque === 'croissance' || $objectif === 'croissance') {
            $score += 2;
        } else {
            $score += 1; // securite, equilibre, etc.
        }

        // 6. Horizon (court_terme = 1, moyen_terme = 2, long_terme = 3 pts)
        $horizon = $data['horizon_terme'] ?? '';
        if ($horizon === 'court_terme') {
            $score += 1;
        } elseif ($horizon === 'moyen_terme') {
            $score += 2;
        } elseif ($horizon === 'long_terme') {
            $score += 3;
        }

        // 7. Target performance (performance 1 = 0, 2 = 2, 3 = 4 pts)
        $perf = $data['niveau_perf'] ?? '';
        if ($perf === 'moderee' || $perf === '2') {
            $score += 2;
        } elseif ($perf === 'elevee' || $perf === '3') {
            $score += 4;
        }

        // 8. Knowledge & past investments
        // connaissance_marche: excellente = 2 pts
        $connaissance = $data['connaissance_marche'] ?? '';
        if ($connaissance === 'excellente') {
            $score += 2;
        }
        // invest_anterieurs: Oui = 3 pts
        $anterieurs = $data['invest_anterieurs'] ?? '';
        if ($anterieurs === 'Oui' || $anterieurs === true) {
            $score += 3;
        }

        return $score;
    }

    /**
     * Determine the profile category name based on the score.
     */
    public function getProfileName(int $score): string
    {
        if ($score <= 10) {
            return 'Prudent';
        } elseif ($score <= 19) {
            return 'Modéré';
        } else {
            return 'Dynamique';
        }
    }
}
