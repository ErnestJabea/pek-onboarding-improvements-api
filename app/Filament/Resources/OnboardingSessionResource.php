<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OnboardingSessionResource\Pages;
use App\Models\OnboardingSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Barryvdh\DomPDF\Facade\Pdf;

class OnboardingSessionResource extends Resource
{
    protected static ?string $model = OnboardingSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Onboarding Clients';

    protected static ?string $modelLabel = 'Session d\'Onboarding';

    protected static ?string $pluralModelLabel = 'Onboarding Clients';

    public static function canCreate(): bool
    {
        return false; // Lecture seule
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Lecture seule
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Statut de la session')
                    ->columns(3)
                    ->schema([
                        Placeholder::make('user_name')
                            ->label('Client')
                            ->content(fn ($record) => $record->user ? trim($record->user->first_name . ' ' . $record->user->last_name) : '-'),
                        Placeholder::make('user_email')
                            ->label('Email client')
                            ->content(fn ($record) => $record->user ? $record->user->email : '-'),
                        Placeholder::make('status')
                            ->label('Statut Onboarding')
                            ->content(fn ($record) => match ($record->status) {
                                'completed' => new HtmlString('<span style="color: green; font-weight: bold;">✓ Terminé</span>'),
                                'pending' => new HtmlString('<span style="color: orange; font-weight: bold;">⏳ En cours</span>'),
                                default => $record->status,
                            }),
                    ]),

                Tabs::make('Details')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations KYC & Identité')
                            ->schema([
                                Grid::make(3)->schema([
                                    Placeholder::make('payload.civ')->label('Civilité')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.nom')->label('Nom')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.prenom')->label('Prénom')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.nat')->label('Nationalité')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.dob')->label('Date de naissance')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ? date('d/m/Y', strtotime($state)) : '-') . '</div>')),
                                    Placeholder::make('payload.lieu_naiss')->label('Lieu de naissance')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.tel')->label('Téléphone')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.email')->label('E-mail déclaré')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.adresse')->label('Adresse de résidence')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.piece')->label('Type de pièce')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.num_piece')->label('N° de pièce')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.expiration_piece')->label('Date d\'expiration')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ? date('d/m/Y', strtotime($state)) : '-') . '</div>')),
                                    Placeholder::make('payload.profession')->label('Profession')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.employeur')->label('Employeur')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.situation_mat')->label('Situation matrimoniale')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.pays_residence')->label('Pays de résidence')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                ]),
                            ]),

                        Tab::make('Profil Investisseur')
                            ->schema([
                                Section::make('Synthèse du profil de risque')
                                    ->columns(3)
                                    ->schema([
                                        Placeholder::make('risk_level')
                                            ->label('Niveau de Risque Global')
                                            ->content(fn ($record) => match ($record->risk_level) {
                                                'LOW' => new HtmlString('<span style="background-color: #dcfce7; color: #16a34a; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">LOW (Faible)</span>'),
                                                'HIGH' => new HtmlString('<span style="background-color: #fee2e2; color: #dc2626; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">⚠️ HIGH (Élevé)</span>'),
                                                default => new HtmlString('<span style="background-color: #f1f5f9; color: #475569; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">' . ($record->risk_level ?? '-') . '</span>'),
                                            }),
                                        Placeholder::make('payload.risk_score')
                                            ->label('Score de Risque calculé')
                                            ->content(fn ($state) => new HtmlString('<div style="font-size: 1.1rem; font-weight: bold; color: #009a4d; padding-left: 10px;">' . ($state ?? '-') . ' pts</div>')),
                                        Placeholder::make('payload.risk_profile')
                                            ->label('Profil d\'Investisseur')
                                            ->content(fn ($state) => match ($state) {
                                                'Prudent' => new HtmlString('<span style="background-color: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">Prudent (Conservateur)</span>'),
                                                'Modéré', 'Moyen', 'Modéré' => new HtmlString('<span style="background-color: #fef9c3; color: #a16207; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">Modéré (Équilibré)</span>'),
                                                'Dynamique' => new HtmlString('<span style="background-color: #fae8ff; color: #a21caf; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">Dynamique (Performances)</span>'),
                                                default => new HtmlString('<span style="background-color: #f1f5f9; color: #475569; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 0.95rem;">' . ($state ?? '-') . '</span>'),
                                            }),
                                    ]),
                                Section::make('Détail des réponses au questionnaire')
                                    ->collapsible()
                                    ->schema([
                                        Grid::make(1)->schema([
                                            Placeholder::make('payload.tranche_revenus')
                                                ->label('1. Quelle est votre tranche de revenus mensuels ?')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #009a4d; padding-left: 10px; margin-bottom: 8px;">' . match ($state) {
                                                    'moins_500k' => 'Moins de 500 000 FCFA',
                                                    '500k_1_5m' => '500 000 FCFA - 1 500 000 FCFA',
                                                    'plus_1_5m' => 'Plus de 1 500 000 FCFA',
                                                    default => $state ?? '-',
                                                } . '</div>')),

                                            Placeholder::make('payload.epargne_possible')
                                                ->label('2. Disposez-vous d\'une capacité d\'épargne régulière ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    ($state === 'Oui' || $state === true)
                                                        ? '<div style="margin-bottom: 8px; border-left: 3px solid #009a4d; padding-left: 10px;"><span style="background-color: #dcfce7; color: #16a34a; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">OUI</span></div>'
                                                        : '<div style="margin-bottom: 8px; border-left: 3px solid #009a4d; padding-left: 10px;"><span style="background-color: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">NON</span></div>'
                                                )),

                                            Placeholder::make('payload.niveau_risque')
                                                ->label('3. Quel niveau de risque acceptez-vous pour vos placements ?')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #009a4d; padding-left: 10px; margin-bottom: 8px;">' . match ($state) {
                                                    'faible' => 'Faible risque (Privilégie la sécurité absolue du capital)',
                                                    'moyen' => 'Risque modéré (Accepte des fluctuations légères pour optimiser les rendements)',
                                                    'max' => 'Risque élevé (Recherche la performance maximale, accepte des pertes temporaires)',
                                                    default => $state ?? '-',
                                                } . '</div>')),

                                            Placeholder::make('payload.conscience_risque')
                                                ->label('4. Avez-vous conscience des risques inhérents à un placement financier (perte en capital) ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    ($state === 'Oui' || $state === true)
                                                        ? '<div style="margin-bottom: 8px; border-left: 3px solid #009a4d; padding-left: 10px;"><span style="background-color: #dcfce7; color: #16a34a; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">OUI</span></div>'
                                                        : '<div style="margin-bottom: 8px; border-left: 3px solid #009a4d; padding-left: 10px;"><span style="background-color: #fee2e2; color: #dc2626; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">NON (Alerte Risque)</span></div>'
                                                )),

                                            Placeholder::make('payload.objectif_invest')
                                                ->label('5. Quel est votre objectif principal d\'investissement ?')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #009a4d; padding-left: 10px; margin-bottom: 8px;">' . match ($state) {
                                                    'securite' => 'Préservation du capital (Recherche de sécurité)',
                                                    'equilibre' => 'Recherche d\'équilibre entre valorisation et sécurité',
                                                    'croissance' => 'Croissance et valorisation à long terme',
                                                    default => $state ?? '-',
                                                } . '</div>')),

                                            Placeholder::make('payload.horizon_terme')
                                                ->label('6. Quel est l\'horizon de placement visé pour vos fonds ?')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #009a4d; padding-left: 10px; margin-bottom: 8px;">' . match ($state) {
                                                    'court_terme' => 'Court terme (Moins de 1 an)',
                                                    'moyen_terme' => 'Moyen terme (1 à 3 ans)',
                                                    'long_terme' => 'Long terme (Plus de 3 ans)',
                                                    default => $state ?? '-',
                                                } . '</div>')),

                                            Placeholder::make('payload.niveau_perf')
                                                ->label('7. Quel objectif de performance ciblez-vous ?')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #009a4d; padding-left: 10px; margin-bottom: 8px;">' . match ($state) {
                                                    '1' => 'Rendement faible mais sécurité maximale',
                                                    'moderee' => 'Rendement modéré avec fluctuation modérée',
                                                    'elevee' => 'Rendement élevé avec fluctuations importantes acceptées',
                                                    default => $state ?? '-',
                                                } . '</div>')),

                                            Placeholder::make('payload.connaissance_marche')
                                                ->label('8. Quelle est votre connaissance des marchés financiers et OPCVM ?')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #009a4d; padding-left: 10px; margin-bottom: 8px;">' . match ($state) {
                                                    'nulle' => 'Aucune connaissance',
                                                    'moyenne' => 'Connaissance générale / intermédiaire',
                                                    'excellente' => 'Bonne / Excellente connaissance',
                                                    default => $state ?? '-',
                                                } . '</div>')),

                                            Placeholder::make('payload.invest_anterieurs')
                                                ->label('9. Avez-vous déjà réalisé des investissements similaires dans le passé ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    ($state === 'Oui' || $state === true)
                                                        ? '<div style="border-left: 3px solid #009a4d; padding-left: 10px;"><span style="background-color: #dcfce7; color: #16a34a; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">OUI</span></div>'
                                                        : '<div style="border-left: 3px solid #009a4d; padding-left: 10px;"><span style="background-color: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">NON</span></div>'
                                                )),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Questionnaire LAB-FT & Conformité')
                            ->schema([
                                Grid::make(3)->schema([
                                    Placeholder::make('payload.agent_kam')->label('Agent KAM de référence')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.secteur')->label('Secteur d\'activité professionnelle')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.revenus_annuels')->label('Revenus annuels estimés')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('payload.origine_fonds')->label('Origine des fonds investis')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    Placeholder::make('sources_revenus')
                                        ->label('Sources de revenus déclarées')
                                        ->content(function ($record) {
                                            $sources = [];
                                            $payload = $record->payload ?? [];
                                            if ($payload['src_salaire'] ?? false) $sources[] = 'Salaire';
                                            if ($payload['src_pro_liberal'] ?? false) $sources[] = 'Profession Libérale';
                                            if ($payload['src_foncier'] ?? false) $sources[] = 'Revenus Fonciers';
                                            if ($payload['src_dividendes'] ?? false) $sources[] = 'Dividendes';
                                            if ($payload['src_heritage'] ?? false) $sources[] = 'Héritage';
                                            if (!empty($payload['src_autre'])) $sources[] = 'Autre: ' . $payload['src_autre'];
                                            return new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . (count($sources) > 0 ? implode(', ', $sources) : 'Aucune source') . '</div>');
                                        }),
                                ]),
                                Section::make('Informations bancaires déclarées')
                                    ->collapsible()
                                    ->columns(3)
                                    ->schema([
                                        Placeholder::make('payload.banque')->label('Nom de la banque')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                        Placeholder::make('payload.num_compte')->label('Numéro de compte / RIB')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                        Placeholder::make('payload.pays_compte')->label('Pays du compte bancaire')->content(fn ($state) => new HtmlString('<div style="font-weight: 600; color: #1e293b;">' . ($state ?? '-') . '</div>')),
                                    ]),
                                Section::make('Déclarations de conformité (LAB-FT / LCB-FT)')
                                    ->collapsible()
                                    ->schema([
                                        Grid::make(1)->schema([
                                            Placeholder::make('payload.pays_risque')
                                                ->label('1. Avez-vous ou entretenez-vous des relations d\'affaires étroites avec un pays à haut risque (ex: pays sous sanctions / liste grise GAFI) ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    $state === 'Oui'
                                                        ? '<div style="border-left: 3px solid #dc2626; padding-left: 10px; margin-bottom: 8px;"><span style="background-color: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">⚠️ OUI (Alerte Risque Pays)</span></div>'
                                                        : '<div style="border-left: 3px solid #10b981; padding-left: 10px; margin-bottom: 8px;"><span style="background-color: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 4px; font-weight: 500; font-size: 0.85rem;">Non</span></div>'
                                                )),
                                            Placeholder::make('payload.secteur_sensible')
                                                ->label('2. Exercez-vous dans un secteur d\'activité jugé sensible (ex: armement, minier, jeux de hasard, trading de matières premières) ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    $state === 'Oui'
                                                        ? '<div style="border-left: 3px solid #dc2626; padding-left: 10px; margin-bottom: 8px;"><span style="background-color: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">⚠️ OUI (Alerte Secteur Sensible)</span></div>'
                                                        : '<div style="border-left: 3px solid #10b981; padding-left: 10px; margin-bottom: 8px;"><span style="background-color: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 4px; font-weight: 500; font-size: 0.85rem;">Non</span></div>'
                                                )),
                                            Placeholder::make('payload.ppe')
                                                ->label('3. Êtes-vous (ou un membre de votre famille proche) une Personne Politiquement Exposée (PPE) exerçant une fonction publique/politique de premier plan ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    $state === 'Oui'
                                                        ? '<div style="border-left: 3px solid #dc2626; padding-left: 10px; margin-bottom: 8px;"><span style="background-color: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">⚠️ OUI (Alerte Statut PPE)</span></div>'
                                                        : '<div style="border-left: 3px solid #10b981; padding-left: 10px; margin-bottom: 8px;"><span style="background-color: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 4px; font-weight: 500; font-size: 0.85rem;">Non</span></div>'
                                                )),
                                            Placeholder::make('payload.ppe_detail')
                                                ->label('Détail des fonctions publiques exercées (si PPE) :')
                                                ->content(fn ($state) => new HtmlString('<div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; border-left: 3px solid #94a3b8; padding-left: 10px; margin-bottom: 8px;">' . ($state ?? 'Aucun détail fourni.') . '</div>')),
                                            Placeholder::make('payload.condamnation')
                                                ->label('4. Avez-vous fait l\'objet d\'une condamnation pénale / administrative par le passé relative à des délits financiers ?')
                                                ->content(fn ($state) => new HtmlString(
                                                    $state === 'Oui'
                                                        ? '<div style="border-left: 3px solid #dc2626; padding-left: 10px;"><span style="background-color: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">⚠️ OUI (Alerte Condamnation)</span></div>'
                                                        : '<div style="border-left: 3px solid #10b981; padding-left: 10px;"><span style="background-color: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 4px; font-weight: 500; font-size: 0.85rem;">Non</span></div>'
                                                )),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Signatures & Téléchargements')
                            ->schema([
                                Grid::make(2)->schema([
                                    Section::make('Signature du client')
                                        ->columnSpan(1)
                                        ->schema([
                                            Placeholder::make('signature_preview')
                                                ->label('Aperçu de la signature')
                                                ->content(function ($record) {
                                                    if ($record->signature_path && Storage::exists($record->signature_path)) {
                                                        $imgData = Storage::get($record->signature_path);
                                                        $mime = 'image/png';
                                                        if (str_ends_with($record->signature_path, '.jpg') || str_ends_with($record->signature_path, '.jpeg')) {
                                                            $mime = 'image/jpeg';
                                                        }
                                                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($imgData);
                                                        return new HtmlString("<img src='{$base64}' style='max-height: 100px; border: 1px solid #ccc; padding: 5px; background: white;' />");
                                                    }
                                                    return 'Aucune signature enregistrée.';
                                                }),
                                        ]),

                                    Section::make('Téléchargement des documents réglementaires (PDF)')
                                        ->columnSpan(1)
                                        ->schema([
                                            Actions::make([
                                                FormAction::make('download_kyc')
                                                    ->label('Télécharger la Fiche KYC')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('success')
                                                    ->action(fn (OnboardingSession $record) => self::downloadPdf($record, 'kyc')),
                                                FormAction::make('download_risk')
                                                    ->label('Télécharger le Profil Investisseur')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('success')
                                                    ->action(fn (OnboardingSession $record) => self::downloadPdf($record, 'risk')),
                                                FormAction::make('download_labft')
                                                    ->label('Télécharger le Questionnaire LAB-FT')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('success')
                                                    ->action(fn (OnboardingSession $record) => self::downloadPdf($record, 'labft')),
                                            ]),
                                        ]),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('risk_level')
                    ->label('Niveau de Risque')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOW' => 'success',
                        'HIGH' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payload.risk_profile')
                    ->label('Profil Investisseur')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Dernière activité')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'completed' => 'Terminé',
                        'in_progress' => 'En cours',
                    ]),
                Tables\Filters\SelectFilter::make('risk_level')
                    ->label('Niveau de Risque')
                    ->options([
                        'LOW' => 'LOW (Faible)',
                        'HIGH' => 'HIGH (Élevé)',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Voir les détails'),
                    TableAction::make('download_kyc')
                        ->label('Télécharger Fiche KYC')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (OnboardingSession $record) => self::downloadPdf($record, 'kyc')),
                    TableAction::make('download_risk')
                        ->label('Télécharger Profil Investisseur')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (OnboardingSession $record) => self::downloadPdf($record, 'risk')),
                    TableAction::make('download_labft')
                        ->label('Télécharger Questionnaire LAB-FT')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (OnboardingSession $record) => self::downloadPdf($record, 'labft')),
                ]),
            ])
            ->bulkActions([
                // Pas d'actions de masse en lecture seule en dehors de l'export éventuel
            ])
            ->emptyStateActions([
                // Pas de création manuelle
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnboardingSessions::route('/'),
            'view' => Pages\ViewOnboardingSession::route('/{record}'),
        ];
    }

    /**
     * Helper to download or compile PDF on the fly.
     */
    public static function downloadPdf(OnboardingSession $record, string $type)
    {
        $prefix = match ($type) {
            'kyc' => 'kyc_',
            'risk' => 'risk_',
            'labft' => 'labft_',
        };

        $viewName = match ($type) {
            'kyc' => 'pdfs.onboarding.fiche_kyc',
            'risk' => 'pdfs.onboarding.profil_investisseur',
            'labft' => 'pdfs.onboarding.questionnaire_labft',
        };

        $filename = match ($type) {
            'kyc' => 'fiche_kyc_',
            'risk' => 'profil_investisseur_',
            'labft' => 'questionnaire_labft_',
        } . ($record->user->last_name ?? 'client') . '_' . ($record->user->first_name ?? '') . '.pdf';

        $path = 'secure_onboardings/' . $prefix . $record->id . '.pdf';

        if (Storage::exists($path)) {
            return Storage::download($path, $filename);
        }

        // Generate on the fly
        $signatureBase64 = '';
        if ($record->signature_path && Storage::exists($record->signature_path)) {
            $imgData = Storage::get($record->signature_path);
            $mime = 'image/png';
            if (str_ends_with($record->signature_path, '.jpg') || str_ends_with($record->signature_path, '.jpeg')) {
                $mime = 'image/jpeg';
            }
            $signatureBase64 = 'data:' . $mime . ';base64,' . base64_encode($imgData);
        }

        $pdfData = [
            'session' => $record,
            'payload' => $record->payload ?? [],
            'user' => $record->user,
            'signature' => $signatureBase64,
        ];

        $pdf = Pdf::loadView($viewName, $pdfData);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
