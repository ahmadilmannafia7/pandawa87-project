<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Umum')
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->schema([
                        TextInput::make('code'),
                        Select::make('flight_id')
                            ->relationship('flight', 'flight_number'),
                        Select::make('flight_class_id')
                            ->relationship('class', 'class_type'),
                    ]),
                Section::make('Informasi Penumpang')
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->schema([
                        TextInput::make('number_of_passengers'),
                        TextInput::make('name'),
                        TextInput::make('email'),
                        TextInput::make('phone'),
                        Section::make('Daftar Penumpang')
                            ->schema([
                                Repeater::make('passenger')
                                    ->relationship('passengers')
                                    ->schema([
                                        Select::make('flight_seat_id')
                                            ->label('Seat Name')
                                            ->relationship('seat', 'name')
                                            ->required(),
                                        TextInput::make('name'),
                                        TextInput::make('date_of_birth'),
                                        TextInput::make('nationality'),
                                    ])
                            ])

                    ]),
                Section::make('pembayaran')
                ->schema([
                    Select::make('promo_code_id')
                        ->relationship('promo', 'code'),
                    TextInput::make('payment_status'),
                    TextInput::make('subtotal'),
                    TextInput::make('grandtotal'),
                ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code'),
                TextColumn::make('flight.flight_number'),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('number_of_passengers'),
                TextColumn::make('promo.code'),
                TextColumn::make('subtotal'),
                TextColumn::make('grandtotal'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
