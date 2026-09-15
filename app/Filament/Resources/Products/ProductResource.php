<?php

namespace App\Filament\Resources\Products;

use App\Models\Product;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(150),
            Textarea::make('description')->required()->maxLength(2000),
            TextInput::make('amount_paisa')->label('Price in paisa (100 paisa = NPR 1)')->integer()->minValue(1000)->maxValue(10000000)->required(),
            TextInput::make('duration_days')->integer()->minValue(1)->maxValue(366)->required(),
            Toggle::make('is_active')->default(false)->helperText('Only activate an approved offer with available premium content. Checkout also requires server configuration.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title'), TextColumn::make('amount_paisa'), TextColumn::make('duration_days'), IconColumn::make('is_active')->boolean()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProducts::route('/')];
    }
}
