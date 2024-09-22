<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TailorResource\Pages;
use App\Filament\Resources\TailorResource\RelationManagers;
use App\Models\Tailor;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TailorResource extends Resource
{
    protected static ?string $model = Tailor::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('username')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('number')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tailor Name'),
                TextColumn::make('username')->label('Username'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTailors::route('/'),
            'create' => Pages\CreateTailor::route('/create'),
            'edit' => Pages\EditTailor::route('/{record}/edit'),
            'view' => Pages\ViewTailor::route('/{record}')
        ];
    }    
}
