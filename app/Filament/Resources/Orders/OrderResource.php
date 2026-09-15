<?php

namespace App\Filament\Resources\Orders;

use App\Models\Order;
use App\Services\MembershipPayments;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('reference')->searchable()->copyable(), TextColumn::make('user.email')->searchable(), TextColumn::make('title'), TextColumn::make('amount_paisa'), TextColumn::make('provider')->badge(), TextColumn::make('environment'), TextColumn::make('status')->badge(), TextColumn::make('verified_at')->dateTime(),
        ])->recordActions([Action::make('verify')->label('Check payment provider')->visible(fn (Order $record) => filled($record->provider_reference) && app(MembershipPayments::class)->canVerify($record))->action(fn (Order $record) => app(MembershipPayments::class)->verify($record))])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageOrders::route('/')];
    }
}
