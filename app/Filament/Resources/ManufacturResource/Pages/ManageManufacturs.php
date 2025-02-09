<?php

namespace App\Filament\Resources\ManufacturResource\Pages;

use App\Filament\Resources\ManufacturResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageManufacturs extends ManageRecords
{
    protected static string $resource = ManufacturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
