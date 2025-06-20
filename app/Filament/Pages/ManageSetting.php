<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Settings\KaidoSetting;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;

class ManageSetting extends SettingsPage
{
    use HasPageShield;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = KaidoSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site Information')->columns(1)->schema([
                    TextInput::make('site_name')
                        ->label('Site Name')
                        ->required(),
                    Toggle::make('site_active')
                        ->label('Site Active'),
                    Toggle::make('registration_enabled')
                        ->label('Registration Enabled'),
                    Toggle::make('password_reset_enabled')
                        ->label('Password Reset Enabled'),
                    Toggle::make('sso_enabled')
                        ->label('SSO Enabled'),
                ]),
            ]);
    }
}
