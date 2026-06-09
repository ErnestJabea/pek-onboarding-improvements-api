@component('mail::message')
    # Onboarding PEK

    @if ($type === 'client')
        Bonjour {{ $user->first_name }},

        Félicitations, vous avez complété avec succès votre parcours d'onboarding pour le **PLAN D'EPARGNE KORI**.

        Côté serveur, notre algorithme a analysé vos réponses et a déterminé votre profil investisseur :
        **Profil : {{ $payload['risk_profile'] ?? 'En attente' }}**

        Vous trouverez en pièce jointe de cet e-mail votre **Fiche de Profil Investisseur** officielle.

        Pour finaliser définitivement l'ouverture de votre compte titres auprès de **Kori Asset Management**, veuillez
        préparer et fournir à nos agents les justificatifs physiques suivants :
        * Copie certifiée conforme de votre pièce d'identité en cours de validité
        ({{ $payload['piece'] ?? 'CNI/Passeport' }} n° {{ $payload['num_piece'] ?? '' }})
        * Justificatif de domicile de moins de 3 mois (facture d'eau, d'électricité ou plan de localisation)
        * Deux (02) photos d'identité récentes
        * Justificatif de l'origine de vos fonds (bulletin de paie récent, relevé de compte bancaire, ou registre de
        commerce)

        Nos équipes vont étudier votre dossier et vous recevrez une notification dès que votre compte sera entièrement
        activé pour les versements.
    @else
        ## Dossier d'Onboarding Reçu (Conformité)

        Un nouveau client a terminé son parcours d'onboarding sur PEK.

        ### Détails du Client :
        * **Nom Complet** : {{ strtoupper($user->last_name) }} {{ $user->first_name }}
        * **E-mail** : {{ $user->email }}
        * **Téléphone** : {{ $payload['tel'] ?? $user->phone }}
        * **Nationalité** : {{ $payload['nat'] ?? '' }}
        * **Profession** : {{ $payload['profession'] ?? '' }}
        * **Employeur** : {{ $payload['employeur'] ?? '' }}

        ### Analyse Réglementaire :
        * **Niveau de Risque Client** : **{{ $session->risk_level }}**
        * **Profil Investisseur** : {{ $payload['risk_profile'] ?? 'Non calculé' }} (Score :
        {{ $payload['risk_score'] ?? '0' }}/23)
        * **PPE (Personne Politiquement Exposée)** : {{ $payload['ppe'] ?? 'Non' }}
        * **Indicateurs de Risque Spécifiques** :
        * Pays à risque : {{ $payload['pays_risque'] ?? 'Non' }}
        * Secteur sensible : {{ $payload['secteur_sensible'] ?? 'Non' }}
        * Condamnation antérieure : {{ $payload['condamnation'] ?? 'Non' }}

        @if ($session->risk_level === 'HIGH')
            > [!WARNING]
            > **ATTENTION** : Ce dossier comporte des alertes LAB-FT. Un examen renforcé est nécessaire avant toute
            approbation.
        @endif

        Les trois documents contractuels (Fiche KYC, Profil Investisseur, Questionnaire LAB-FT) contenant la signature
        électronique du client sont joints à cet e-mail.
    @endif

    Cordialement,<br>
    L'équipe **KORI ASSET MANAGEMENT**
@endcomponent
