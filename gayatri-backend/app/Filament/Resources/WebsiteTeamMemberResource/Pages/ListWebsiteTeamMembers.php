<?php

namespace App\Filament\Resources\WebsiteTeamMemberResource\Pages;

use App\Filament\Resources\WebsiteTeamMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebsiteTeamMembers extends ListRecords
{
    protected static string $resource = WebsiteTeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
