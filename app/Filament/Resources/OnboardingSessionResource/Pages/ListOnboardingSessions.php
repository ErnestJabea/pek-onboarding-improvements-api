<?php

namespace App\Filament\Resources\OnboardingSessionResource\Pages;

use App\Filament\Resources\OnboardingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOnboardingSessions extends ListRecords
{
    protected static string $resource = OnboardingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
