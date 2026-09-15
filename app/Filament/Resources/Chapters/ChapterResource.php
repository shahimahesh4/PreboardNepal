<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Resources\Chapters\Pages\ManageChapters;
use App\Models\Chapter;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Academics';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')->relationship('subject', 'name')->required(),
            TextInput::make('title')->required()->maxLength(150),
            TextInput::make('position')->numeric()->minValue(1)->maxValue(1000)->default(1)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable(), TextColumn::make('subject.name'), TextColumn::make('position')->sortable()])->recordActions([EditAction::make()])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageChapters::route('/')];
    }
}
