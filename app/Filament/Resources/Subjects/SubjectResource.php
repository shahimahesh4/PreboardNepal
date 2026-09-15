<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\ManageSubjects;
use App\Models\Subject;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Academics';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('slug')->required()->maxLength(120)->unique(ignoreRecord: true)->regex('/^[a-z0-9-]+$/'),
            Textarea::make('description')->required()->maxLength(500)->columnSpanFull(),
            TextInput::make('curriculum')->required()->default('Grade 12 · Foundation')->maxLength(150),
            Select::make('color')->options(['blue' => 'Blue', 'lilac' => 'Lilac', 'mint' => 'Mint', 'sand' => 'Sand'])->required()->default('blue'),
            Select::make('symbol')->options(['book-open' => 'Book', 'bolt' => 'Physics', 'beaker' => 'Biology', 'variable' => 'Mathematics'])->required()->default('book-open'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('curriculum'), IconColumn::make('is_active')->boolean()])->recordActions([EditAction::make()])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageSubjects::route('/')];
    }
}
