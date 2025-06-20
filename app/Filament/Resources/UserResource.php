<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\TextEntry;
// use App\Filament\Resources\UserResource\Pages;
// use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(
                    'User Information'
                )->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('email')
                                ->required(),
                            TextInput::make('password')
                                ->required(),
                        ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('avatar_url')
                        ->searchable()
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url
                            ? $record->avatar_url
                            : "https://ui-avatars.com/api/?name=" . urlencode($record->name)),
                    TextColumn::make('name')
                        ->searchable()
                        ->weight(FontWeight::Bold),
                    Stack::make([
                        TextColumn::make('roles.name')
                            ->searchable()
                            ->icon('heroicon-o-shield-check')
                            ->grow(false),
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->searchable()
                            ->grow(false),
                    ])->alignStart()->visibleFrom('lg')->space(1)
                ]),
            ])
            ->filters([
                //
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('Set Role')
                    ->icon('heroicon-m-adjustments-vertical')
                    ->schema([
                        Select::make('role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(10)
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                    ]),
                // Impersonate::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserExporter::class),
                ImportAction::make()
                    ->importer(UserImporter::class)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email'),
                ]),
            ]);
    }
}