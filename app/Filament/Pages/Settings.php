<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected string $view = 'filament.pages.settings';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog';
    protected static ?int $navigationSort = 10;
}
