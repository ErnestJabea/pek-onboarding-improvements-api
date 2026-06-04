<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche KYC - Onboarding PEK</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d3748;
            font-size: 11pt;
            line-height: 1.5;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #1a2b44;
            padding-bottom: 10px;
            margin-bottom: 30px;
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
            font-size: 16pt;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 35px;
            letter-spacing: 1px;
        }
        .section-title {
            background-color: #f7fafc;
            border-left: 4px solid #1a2b44;
            padding: 6px 12px;
            font-weight: bold;
            color: #1a2b44;
            font-size: 12pt;
            margin-top: 25px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
        }
        table.data-table td.label {
            font-weight: bold;
            color: #4a5568;
            width: 35%;
            font-size: 10pt;
        }
        table.data-table td.value {
            color: #2d3748;
            font-size: 10.5pt;
        }
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-box {
            border: 1px dashed #cbd5e0;
            height: 120px;
            width: 250px;
            text-align: center;
            vertical-align: middle;
            background-color: #f8fafc;
        }
        .signature-box img {
            max-height: 110px;
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
            padding-top: 10px;
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
                    Réf : KYC-{{ $session->id }}<br>
                    Date : {{ now()->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <h1>Fiche de Connaissance Client (KYC)</h1>

    <div class="section-title">1. Informations Personnelles</div>
    <table class="data-table">
        <tr>
            <td class="label">Civilité</td>
            <td class="value">{{ $payload['civ'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Nom</td>
            <td class="value">{{ strtoupper($payload['nom'] ?? '') }}</td>
        </tr>
        <tr>
            <td class="label">Prénom(s)</td>
            <td class="value">{{ $payload['prenom'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Nationalité</td>
            <td class="value">{{ $payload['nat'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date de Naissance</td>
            <td class="value">{{ $payload['dob'] ? date('d/m/Y', strtotime($payload['dob'])) : '' }}</td>
        </tr>
        <tr>
            <td class="label">Lieu de Naissance</td>
            <td class="value">{{ $payload['lieu_naiss'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Adresse de Résidence</td>
            <td class="value">{{ $payload['adresse'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">2. Coordonnées de Contact</div>
    <table class="data-table">
        <tr>
            <td class="label">Numéro de Téléphone</td>
            <td class="value">{{ $payload['tel'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Adresse E-mail</td>
            <td class="value">{{ $payload['email'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">3. Informations Professionnelles</div>
    <table class="data-table">
        <tr>
            <td class="label">Profession</td>
            <td class="value">{{ $payload['profession'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Nom de l'Employeur</td>
            <td class="value">{{ $payload['employeur'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">4. Pièce d'Identité Officielle</div>
    <table class="data-table">
        <tr>
            <td class="label">Type de pièce</td>
            <td class="value">{{ $payload['piece'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Numéro de pièce</td>
            <td class="value">{{ $payload['num_piece'] ?? '' }}</td>
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
                        <span style="font-size: 9pt; font-weight: bold; color: #4a5568; display: block; margin-bottom: 5px;">Signature du Client :</span>
                        <div class="signature-box">
                            @if($signature)
                                <img src="{{ $signature }}" alt="Signature Client">
                            @else
                                <span style="color: #a0aec0; font-size: 9pt; line-height: 120px;">Pas de signature</span>
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
