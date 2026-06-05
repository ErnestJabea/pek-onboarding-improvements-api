<?php

namespace App\Filament\Resources\OnboardingSessionResource\Pages;

use App\Filament\Resources\OnboardingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOnboardingSession extends ViewRecord
{
    protected static string $resource = OnboardingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
