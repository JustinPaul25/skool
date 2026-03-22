<?php

namespace App\Filament\Resources\EnrollmentApplications;

use App\Filament\Resources\EnrollmentApplications\Pages\CreateEnrollmentApplication;
use App\Filament\Resources\EnrollmentApplications\Pages\EditEnrollmentApplication;
use App\Filament\Resources\EnrollmentApplications\Pages\ListEnrollmentApplications;
use App\Filament\Resources\EnrollmentApplications\Pages\ViewEnrollmentApplication;
use App\Filament\Resources\EnrollmentApplications\Schemas\EnrollmentApplicationForm;
use App\Filament\Resources\EnrollmentApplications\Schemas\EnrollmentApplicationInfolist;
use App\Filament\Resources\EnrollmentApplications\Tables\EnrollmentApplicationsTable;
use App\Models\EnrollmentApplication;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentApplicationResource extends Resource
{
    protected static ?string $model = EnrollmentApplication::class;

    /**
     * Applicant display is an accessor, not a DB column — disable until a custom search query is added.
     */
    protected static bool $isGloballySearchable = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'applicant_name';

    protected static string|UnitEnum|null $navigationGroup = 'Enrollment';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Applications';
    }

    public static function getModelLabel(): string
    {
        return 'Enrollment application';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Enrollment applications';
    }

    public static function form(Schema $schema): Schema
    {
        return EnrollmentApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EnrollmentApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnrollmentApplicationsTable::configure($table);
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
            'index' => ListEnrollmentApplications::route('/'),
            'create' => CreateEnrollmentApplication::route('/create'),
            'view' => ViewEnrollmentApplication::route('/{record}'),
            'edit' => EditEnrollmentApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'student',
                'branch',
                'gradeLevel',
                'schoolYear',
                'reviewer',
            ]);

        $user = auth()->user();

        if ($user && $user->hasRole('branch_manager') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }
}
