<?php

namespace App\Filament\Resources\EnrollmentApplications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EnrollmentApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('applicant_name')
                    ->label('Applicant'),

                TextEntry::make('student.student_id')
                    ->label('Student ID')
                    ->placeholder('—'),

                TextEntry::make('schoolYear.name')
                    ->label('School year'),

                TextEntry::make('gradeLevel.name')
                    ->label('Grade level'),

                TextEntry::make('branch.name')
                    ->label('Branch'),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextEntry::make('notes')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('submitted_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('reviewer.name')
                    ->label('Reviewed by')
                    ->placeholder('—'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('—'),
            ]);
    }
}
