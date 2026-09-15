<?php

namespace App\Filament\Resources\StudyDocuments\Pages;

use App\Filament\Resources\StudyDocuments\StudyDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudyDocuments extends ManageRecords
{
    protected static string $resource = StudyDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->mutateDataUsing(fn (array $data): array => $data + ['author_id' => auth()->id(), 'status' => 'draft'])];
    }
}
