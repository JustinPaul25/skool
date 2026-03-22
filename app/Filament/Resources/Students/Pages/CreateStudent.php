<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Enrollment;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    use HasWizard;

    protected static string $resource = StudentResource::class;

    /**
     * @var array<string, mixed>
     */
    protected array $placementData = [];

    protected function getSteps(): array
    {
        return [
            Step::make('Personal')
                ->description('Name, birth date, gender, and photo')
                ->schema([
                    ...StudentForm::personalFields(),
                    SpatieMediaLibraryFileUpload::make('photo')
                        ->label('Photo')
                        ->collection('photo')
                        ->image()
                        ->imageEditor()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Step::make('Contact')
                ->description('Address, phone, and email')
                ->schema(StudentForm::contactFields())
                ->columns(2),

            Step::make('Guardian')
                ->description('Guardian contact details')
                ->schema(StudentForm::guardianFields())
                ->columns(2),

            Step::make('Academic placement')
                ->description('Branch and initial enrollment (optional)')
                ->schema([
                    Select::make('branch_id')
                        ->relationship('branch', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label('Branch')
                        ->default(fn () => auth()->user()?->branch_id)
                        ->disabled(fn () => auth()->user()?->hasRole('branch_manager') ?? false),

                    Select::make('placement_school_year_id')
                        ->label('School year')
                        ->options(fn () => SchoolYear::query()->orderByDesc('start_date')->pluck('name', 'id'))
                        ->searchable(),

                    Select::make('placement_grade_level_id')
                        ->label('Grade level')
                        ->options(fn () => GradeLevel::query()->orderBy('order')->pluck('name', 'id'))
                        ->searchable()
                        ->live(),

                    Select::make('placement_section_id')
                        ->label('Section')
                        ->options(function (callable $get) {
                            $gradeId = $get('placement_grade_level_id');
                            $branchId = $get('branch_id');
                            if (! $gradeId || ! $branchId) {
                                return [];
                            }

                            return Section::query()
                                ->where('grade_level_id', $gradeId)
                                ->where('branch_id', $branchId)
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->nullable(),
                ])
                ->columns(2),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['student_id'] ?? null)) {
            $data['student_id'] = 'STU-'.now()->format('Y').'-'.strtoupper(Str::random(8));
        }

        $this->placementData = Arr::only($data, [
            'placement_school_year_id',
            'placement_grade_level_id',
            'placement_section_id',
        ]);

        return Arr::except($data, [
            'placement_school_year_id',
            'placement_grade_level_id',
            'placement_section_id',
            'photo',
        ]);
    }

    protected function afterCreate(): void
    {
        $schoolYearId = $this->placementData['placement_school_year_id'] ?? null;
        $gradeLevelId = $this->placementData['placement_grade_level_id'] ?? null;

        if ($schoolYearId && $gradeLevelId) {
            Enrollment::query()->create([
                'student_id' => $this->record->id,
                'school_year_id' => $schoolYearId,
                'grade_level_id' => $gradeLevelId,
                'section_id' => $this->placementData['placement_section_id'] ?? null,
                'branch_id' => $this->record->branch_id,
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);
        }
    }
}
