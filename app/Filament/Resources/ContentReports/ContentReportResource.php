<?php

namespace App\Filament\Resources\ContentReports;

use App\Filament\Resources\ContentReports\Pages\ManageContentReports;
use App\Models\ContentReport;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ContentReportResource extends Resource
{
    protected static ?string $model = ContentReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')->options(['open' => 'Open', 'reviewing' => 'Reviewing', 'resolved' => 'Resolved'])->required(),
            Textarea::make('resolution')->maxLength(5000)->required(fn ($get) => $get('status') === 'resolved')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('document.title')->searchable()->limit(40), TextColumn::make('category')->badge(), TextColumn::make('message')->wrap()->limit(150), TextColumn::make('status')->badge(), TextColumn::make('created_at')->since()])->recordActions([EditAction::make()])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageContentReports::route('/')];
    }
}
