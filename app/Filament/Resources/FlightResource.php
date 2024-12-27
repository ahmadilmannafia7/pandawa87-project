<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightResource\Pages;
use App\Filament\Resources\FlightResource\RelationManagers;
use App\Models\Flight;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;

class FlightResource extends Resource
{
    protected static ?string $model = Flight::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Flight Information')
                        ->schema([
                            TextInput::make('flight_number')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Select::make('airline_id')
                                ->relationship('airline', 'name')
                                ->required(),
                        ]),
                    Wizard\Step::make('Flight Segment')
                        ->schema([
                            Repeater::make('flight_segments')
                                ->relationship('segments')
                                ->schema([
                                    TextInput::make('sequence')
                                        ->numeric()
                                        ->required(),
                                    Select::make('airport_id')
                                        ->relationship('airport', 'name')
                                        ->required(),
                                    DateTimePicker::make('time')
                                        ->required(),
                                ])
                                ->collapsed(false)
                                ->minItems(1),
                        ]),
                    Wizard\Step::make('Flight Class')
                        ->schema([
                            Repeater::make('flight_classes')
                                ->relationship('classes')
                                ->schema([
                                    Select::make('class_type')
                                        ->options([
                                            'business' => 'Business',
                                            'economy' => 'Economy',
                                        ])
                                        ->required(),
                                    TextInput::make('price')
                                        ->required()
                                        ->prefix('IDR')
                                        ->numeric()
                                        ->minValue(0),
                                    TextInput::make('total_seats')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->label('Total Seats'),
                                    Select::make('faciities')
                                        ->relationship('facilities', 'name')
                                        ->multiple()
                                        ->required()
                                ])
                        ]),
                ])->columnSpan(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flight_number'),
                TextColumn::make('airline.name'),
                TextColumn::make('segments')
                    ->label('Route & Duration')
                    ->formatStateUsing(function (Flight $record): string {
                        $firstSegment = $record->segments->first();
                        $lastSegment = $record->segments->last();
                        $route = $firstSegment->airport->iata_code . ' - ' . $lastSegment->airport->iata_code;
                        $duration = (new \DateTime($firstSegment->time))->format('d F Y H:i') . ' - ' . (new \DateTime($lastSegment->time))->format('d F Y H:i');
                        return $route . ' | ' . $duration;
                    })
                ,

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
            'index' => Pages\ListFlights::route('/'),
            'create' => Pages\CreateFlight::route('/create'),
            'edit' => Pages\EditFlight::route('/{record}/edit'),
        ];
    }
}
