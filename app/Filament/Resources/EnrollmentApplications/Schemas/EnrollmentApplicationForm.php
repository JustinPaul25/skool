<?php

namespace App\Filament\Resources\EnrollmentApplications\Schemas;

use App\Models\EnrollmentApplication;
use App\Models\Student;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnrollmentApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Application')
                    ->schema([
                        Select::make('student_id')
                            ->label('Student')
                            ->relationship(
                                'student',
                                'last_name',
                                modifyQueryUsing: fn ($query) => $query->when(
                                    auth()->user()?->hasRole('branch_manager') && auth()->user()?->branch_id,
                                    fn ($q) => $q->where('branch_id', auth()->user()->branch_id)
                                )
                            )
                            ->getOptionLabelFromRecordUsing(fn (Student $record): string => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'student_id'])
                            ->preload()
                            ->nullable()
                            ->helperText('Leave empty for new applicants until a student profile is created.'),

                        Select::make('school_year_id')
                            ->relationship('schoolYear', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('grade_level_id')
                            ->relationship('gradeLevel', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()?->branch_id)
                            ->disabled(fn () => auth()->user()?->hasRole('branch_manager') ?? false),

                        Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'under_review' => 'Under review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('submitted')
                            ->required()
                            ->disabled(fn (?EnrollmentApplication $record): bool => (bool) ($record && in_array($record->status, ['approved', 'rejected'], true)))
                            ->dehydrated(fn (?EnrollmentApplication $record): bool => ! ($record && in_array($record->status, ['approved', 'rejected'], true))),

                        Textarea::make('notes')
                            ->columnSpanFull(),

                        DateTimePicker::make('submitted_at')
                            ->seconds(false)
                            ->default(fn (?EnrollmentApplication $record) => $record?->submitted_at ?? now()),
                    ])
                    ->columns(2),
            ]);
    }
}
