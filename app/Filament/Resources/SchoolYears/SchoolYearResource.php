<?php

namespace App\Filament\Resources\SchoolYears;

use App\Filament\Resources\SchoolYears\Pages\CreateSchoolYear;
use App\Filament\Resources\SchoolYears\Pages\EditSchoolYear;
use App\Filament\Resources\SchoolYears\Pages\ListSchoolYears;
use App\Filament\Resources\SchoolYears\Pages\ViewSchoolYear;
use App\Filament\Resources\SchoolYears\Schemas\SchoolYearForm;
use App\Filament\Resources\SchoolYears\Schemas\SchoolYearInfolist;
use App\Filament\Resources\SchoolYears\Tables\SchoolYearsTable;
use App\Models\SchoolYear;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SchoolYearResource extends Resource
{
    protected static ?string $model = SchoolYear::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'Academics';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return SchoolYearForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SchoolYearInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchoolYearsTable::configure($table);
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
            'index' => ListSchoolYears::route('/'),
            'create' => CreateSchoolYear::route('/create'),
            'view' => ViewSchoolYear::route('/{record}'),
            'edit' => EditSchoolYear::route('/{record}/edit'),
        ];
    }
}
