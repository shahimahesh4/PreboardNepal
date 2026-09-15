<?php

namespace App\Filament\Resources\AuditEntries;

use App\Filament\Resources\AuditEntries\Pages\ManageAuditEntries;
use App\Models\AuditEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AuditEntryResource extends Resource
{
    protected static ?string $model = AuditEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('action')->searchable(), TextColumn::make('resource')->searchable(), TextColumn::make('user_id'), TextColumn::make('created_at')->dateTime()])->recordActions([])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageAuditEntries::route('/')];
    }
}
