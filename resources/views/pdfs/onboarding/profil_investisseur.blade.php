<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Investisseur - Onboarding PEK</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d3748;
            font-size: 10pt;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #1a2b44;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .header table {
            width: 100%;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #1a2b44;
        }
        .doc-meta {
            text-align: right;
            font-size: 9pt;
            color: #718096;
        }
        h1 {
            color: #1a2b44;
            font-size: 15pt;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .subtitle {
            text-align: center;
            font-size: 9pt;
            color: #718096;
            margin-bottom: 30px;
            font-style: italic;
        }
        .result-box {
            border: 2px solid #1a2b44;
            border-radius: 8px;
            background-color: #f7fafc;
            padding: 15px;
            margin-bottom: 25px;
        }
        .result-title {
            font-weight: bold;
            color: #1a2b44;
            font-size: 11pt;
            margin-bottom: 5px;
            text-transform: uppercase;
            text-align: center;
        }
        .result-value {
            font-size: 18pt;
            font-weight: 900;
            color: #009a4d;
            text-align: center;
        }
        .section-title {
            background-color: #f7fafc;
            border-left: 4px solid #1a2b44;
            padding: 4px 10px;
            font-weight: bold;
            color: #1a2b44;
            font-size: 11pt;
            margin-top: 20px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        table.qa-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.qa-table th {
            background-color: #edf2f7;
            color: #4a5568;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 12px;
            border-bottom: 1px solid #cbd5e0;
            text-align: left;
        }
        table.qa-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            margin-top: 15px;
        }
        .signature-box {
            border: 1px dashed #cbd5e0;
            height: 110px;
            width: 250px;
            text-align: center;
            vertical-align: middle;
            background-color: #f8fafc;
        }
        .signature-box img {
            max-height: 100px;
            max-width: 240px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <span class="logo-text">PEK</span><br>
                    <span style="font-size: 9pt; color: #718096; font-weight: bold;">PLAN D'ÉPARGNE KORI</span>
                </td>
                <td class="doc-meta">
                    Réf : PR-{{ $session->id }}<br>
                    Nom : {{ strtoupper($user->last_name) }} {{ $user->first_name }}
                </td>
            </tr>
        </table>
    </div>

    <h1>Questionnaire de Profil Investisseur</h1>
    <div class="subtitle">Évaluation réglementaire de convenance de l'investisseur - Règlement Général COSUMAF</div>

    <div class="result-box">
        <div class="result-title">Profil d'Investissement Validé</div>
        <div class="result-value">
            {{ $payload['risk_profile'] ?? 'Non déterminé' }}
        </div>
        <p style="font-size: 9pt; color: #4a5568; margin-top: 10px; text-align: center; line-height: 1.3;">
            @if(($payload['risk_profile'] ?? '') === 'Prudent')
                <strong>Profil Prudent :</strong> Recherche avant tout la préservation du capital avec un niveau de risque très faible. Convient à un placement à court/moyen terme.
            @elseif(($payload['risk_profile'] ?? '') === 'Modéré')
                <strong>Profil Modéré :</strong> Recherche un équilibre entre croissance du capital et sécurité. Accepte des fluctuations modérées du marché à moyen/long terme.
            @else
                <strong>Profil Dynamique :</strong> Recherche des rendements élevés sur le long terme. Prêt à accepter des fluctuations de valeur significatives sur les marchés financiers.
            @endif
        </p>
    </div>

    <div class="section-title">Réponses au Questionnaire</div>
    <table class="qa-table">
        <thead>
            <tr>
                <th style="width: 50%;">Question</th>
                <th style="width: 50%;">Votre Réponse</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tranche de revenus annuels</td>
                <td>
                    @if(($payload['tranche_revenus'] ?? '') === 'moins_500k') Moins de 500 000 FCFA
                    @elseif(($payload['tranche_revenus'] ?? '') === '500k_1_5m') Entre 500 000 et 1 500 000 FCFA
                    @else Plus de 1 500 000 FCFA
                    @endif
                </td>
            </tr>
            <tr>
                <td>Épargne résiduelle possible à investir</td>
                <td>{{ ($payload['epargne_possible'] ?? '') === 'Oui' ? 'Oui, j\'ai une capacité d\'épargne régulière' : 'Non' }}</td>
            </tr>
            <tr>
                <td>Niveau de risque financier accepté</td>
                <td>
                    @if(($payload['niveau_risque'] ?? '') === 'faible') Risque faible (préservation du capital privilégiée)
                    @elseif(($payload['niveau_risque'] ?? '') === 'moyen') Risque modéré (recherche d'équilibre)
                    @else Risque élevé (croissance maximale recherchée)
                    @endif
                </td>
            </tr>
            <tr>
                <td>Conscience du risque de perte en capital</td>
                <td>{{ ($payload['conscience_risque'] ?? '') === 'Oui' ? 'Oui, je comprends qu\'un rendement plus élevé implique un risque' : 'Non' }}</td>
            </tr>
            <tr>
                <td>Objectif principal de l'investissement</td>
                <td>
                    @if(($payload['objectif_invest'] ?? '') === 'securite') Sécurité et disponibilité immédiate du capital
                    @elseif(($payload['objectif_invest'] ?? '') === 'equilibre') Valorisation équilibrée avec prise de risque modérée
                    @else Valorisation dynamique sur le long terme
                    @endif
                </td>
            </tr>
            <tr>
                <td>Horizon de placement envisagé</td>
                <td>
                    @if(($payload['horizon_terme'] ?? '') === 'court_terme') Court terme (Moins de 2 ans)
                    @elseif(($payload['horizon_terme'] ?? '') === 'moyen_terme') Moyen terme (2 à 5 ans)
                    @else Long terme (Plus de 5 ans)
                    @endif
                </td>
            </tr>
            <tr>
                <td>Niveau de performance ciblé (et son risque)</td>
                <td>
                    @if(($payload['niveau_perf'] ?? '') === 'faible') Rendement faible et régulier (Risque minimal)
                    @elseif(($payload['niveau_perf'] ?? '') === 'moderee') Rendement modéré et fluctuation modérée (Risque moyen)
                    @else Rendement élevé et forte fluctuation possible (Risque maximal)
                    @endif
                </td>
            </tr>
            <tr>
                <td>Connaissance des marchés financiers</td>
                <td>
                    @if(($payload['connaissance_marche'] ?? '') === 'faible') Faible ou inexistante
                    @elseif(($payload['connaissance_marche'] ?? '') === 'moyenne') Moyenne (compréhension basique des OPCVM)
                    @else Excellente (maîtrise des actions, obligations et FCP)
                    @endif
                </td>
            </tr>
            <tr>
                <td>Investissements antérieurs réalisés</td>
                <td>{{ ($payload['invest_anterieurs'] ?? '') === 'Oui' ? 'Oui, j\'ai déjà investi dans des valeurs mobilières/OPCVM' : 'Non' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p style="font-size: 9pt; color: #4a5568; line-height: 1.4;">
                        Je soussigné(e) {{ $payload['prenom'] ?? '' }} {{ $payload['nom'] ?? '' }}, certifie sur l'honneur l'exactitude de toutes les informations fournies dans ce dossier d'onboarding.<br>
                        Je déclare accepter expressément les règlements du FCP PEK et reconnais que cette signature électronique fait foi de mon engagement contractuel.<br>
                        Fait à Yaoundé, le {{ now()->format('d/m/Y') }}
                    </p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="display: inline-block; text-align: left;">
                        <span style="font-size: 8.5pt; font-weight: bold; color: #4a5568; display: block; margin-bottom: 5px;">Signature du Client :</span>
                        <div class="signature-box">
                            @if($signature)
                                <img src="{{ $signature }}" alt="Signature Client">
                            @else
                                <span style="color: #a0aec0; font-size: 9pt; line-height: 110px;">Pas de signature</span>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Kori Asset Management S.A. - Agrément COSUMAF n° GP-04/2018 - Siège social : Douala, Cameroun<br>
        PEK FCP - Onboarding entièrement dématérialisé - Page 1/1
    </div>

</body>
</html>
