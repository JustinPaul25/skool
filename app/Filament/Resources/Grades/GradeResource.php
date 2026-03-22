<?php

namespace App\Filament\Resources\Grades;

use App\Filament\Resources\Grades\Pages\CreateGrade;
use App\Filament\Resources\Grades\Pages\EditGrade;
use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Filament\Resources\Grades\Pages\ViewGrade;
use App\Filament\Resources\Grades\Schemas\GradeForm;
use App\Filament\Resources\Grades\Schemas\GradeInfolist;
use App\Filament\Resources\Grades\Tables\GradesTable;
use App\Models\Grade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $recordTitleAttribute = null;

    protected static string|UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Grades';
    }

    public static function getModelLabel(): string
    {
        return 'Grade';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Grades';
    }

    public static function getRecordTitle(?Model $record): string | Htmlable | null
    {
        if (! $record instanceof Grade) {
            return static::getModelLabel();
        }

        $record->loadMissing(['subject', 'enrollment.student']);

        $subject = $record->subject?->name ?? 'Grade';
        $student = $record->enrollment?->student?->full_name ?? 'Student';

        return "{$subject} — {$student}";
    }

    public static function form(Schema $schema): Schema
    {
        return GradeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GradeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradesTable::configure($table);
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
            'index' => ListGrades::route('/'),
            'create' => CreateGrade::route('/create'),
            'view' => ViewGrade::route('/{record}'),
            'edit' => EditGrade::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'enrollment.student',
                'enrollment.schoolYear',
                'enrollment.gradeLevel',
                'subject',
                'grader',
            ]);
    }
}
