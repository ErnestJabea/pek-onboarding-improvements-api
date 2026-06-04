<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Questionnaire LAB-FT - Onboarding PEK</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d3748;
            font-size: 9.5pt;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #1a2b44;
            padding-bottom: 10px;
            margin-bottom: 20px;
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
            font-size: 14pt;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .subtitle {
            text-align: center;
            font-size: 8.5pt;
            color: #718096;
            margin-bottom: 25px;
            font-style: italic;
        }
        .section-title {
            background-color: #f7fafc;
            border-left: 4px solid #1a2b44;
            padding: 4px 10px;
            font-weight: bold;
            color: #1a2b44;
            font-size: 10pt;
            margin-top: 15px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
            font-size: 9pt;
        }
        table.data-table td.label {
            font-weight: bold;
            color: #4a5568;
            width: 40%;
        }
        table.data-table td.value {
            color: #2d3748;
        }
        .alert-box {
            background-color: #fffaf0;
            border: 1px solid #feebc8;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 15px;
            color: #c05621;
            font-size: 9pt;
        }
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            margin-top: 15px;
        }
        .signature-box {
            border: 1px dashed #cbd5e0;
            height: 100px;
            width: 250px;
            text-align: center;
            vertical-align: middle;
            background-color: #f8fafc;
        }
        .signature-box img {
            max-height: 90px;
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
                    Réf : LABFT-{{ $session->id }}<br>
                    Nom : {{ strtoupper($user->last_name) }} {{ $user->first_name }}
                </td>
            </tr>
        </table>
    </div>

    <h1>Questionnaire de Diligence LAB/FT</h1>
    <div class="subtitle">Lutte contre le Blanchiment de Capitaux et le Financement du Terrorisme - Règlement COBAC R-2005/01</div>

    @if($session->risk_level === 'HIGH')
    <div class="alert-box">
        <strong>⚠️ EXAMEN RENFORCÉ REQUIS :</strong> Des indicateurs de vigilance réglementaire (PPE ou exposition géographique/sectorielle) ont été cochés. Ce dossier doit faire l'objet d'une validation manuelle par l'équipe conformité.
    </div>
    @endif

    <div class="section-title">1. Situation de Famille & Résidence</div>
    <table class="data-table">
        <tr>
            <td class="label">Situation Matrimoniale</td>
            <td class="value">{{ $payload['situation_mat'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Pays de Résidence Fiscale</td>
            <td class="value">{{ $payload['pays_residence'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date d'Expiration de la Pièce</td>
            <td class="value">{{ $payload['expiration_piece'] ? date('d/m/Y', strtotime($payload['expiration_piece'])) : '' }}</td>
        </tr>
        @if(!empty($payload['agent_kam']))
        <tr>
            <td class="label">Agent Kori Asset Management associé</td>
            <td class="value">{{ $payload['agent_kam'] }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">2. Profil Économique & Origine des Fonds</div>
    <table class="data-table">
        <tr>
            <td class="label">Secteur d'Activité Professionnel</td>
            <td class="value">{{ $payload['secteur'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Tranche d'estimation des Revenus Annuels</td>
            <td class="value">{{ $payload['revenus_annuels'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Sources des Revenus Déclarées</td>
            <td class="value">
                @php
                    $sources = [];
                    if ($payload['src_salaire'] ?? false) $sources[] = 'Salaire';
                    if ($payload['src_pro_liberal'] ?? false) $sources[] = 'Profession Libérale / Honoraires';
                    if ($payload['src_foncier'] ?? false) $sources[] = 'Revenus Fonciers';
                    if ($payload['src_dividendes'] ?? false) $sources[] = 'Dividendes / Plus-values';
                    if ($payload['src_heritage'] ?? false) $sources[] = 'Héritage';
                    if ($payload['src_autre_check'] ?? false) $sources[] = 'Autre : ' . ($payload['src_autre'] ?? '');
                @endphp
                {{ implode(', ', $sources) }}
            </td>
        </tr>
        <tr>
            <td class="label">Origine des fonds à investir sur PEK</td>
            <td class="value">{{ $payload['origine_fonds'] ?? '' }}</td>
        </tr>
    </table>



    <div class="section-title">3. Indicateurs de Vigilance LAB/FT</div>
    <table class="data-table">
        <tr>
            <td class="label">Êtes-vous une Personne Politiquement Exposée (PPE) ?</td>
            <td class="value">
                <strong>{{ $payload['ppe'] ?? 'Non' }}</strong>
                @if(($payload['ppe'] ?? 'Non') === 'Oui')
                    <br><span style="font-size: 8.5pt; color: #718096;">Détails de la fonction : {{ $payload['ppe_detail'] ?? '' }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Compte bancaire situé dans un pays à haut risque ou sous sanctions ?</td>
            <td class="value"><strong>{{ $payload['pays_risque'] ?? 'Non' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Origine des fonds provenant d'un secteur d'activité sensible ?</td>
            <td class="value"><strong>{{ $payload['secteur_sensible'] ?? 'Non' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Condamnation passée pour blanchiment ou financement du terrorisme ?</td>
            <td class="value"><strong>{{ $payload['condamnation'] ?? 'Non' }}</strong></td>
        </tr>
    </table>

    <div class="section-title">4. Déclarations de Consentement</div>
    <table class="data-table">
        <tr>
            <td style="width: 5%; font-weight: bold; text-align: center;">[X]</td>
            <td style="width: 95%;">Je déclare avoir pris connaissance des règlements généraux et conditions du Fonds FCP KORI SÉRÉNITÉ.</td>
        </tr>
        <tr>
            <td style="width: 5%; font-weight: bold; text-align: center;">[X]</td>
            <td style="width: 95%;">J'autorise Kori Asset Management à effectuer le traitement informatique de mes données personnelles et financières dans le strict respect de la réglementation en vigueur.</td>
        </tr>
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
                                <span style="color: #a0aec0; font-size: 9pt; line-height: 100px;">Pas de signature</span>
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
