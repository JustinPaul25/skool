<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Account;
use App\Models\Payment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PaymentForm
{
    /**
     * @return array<int, string>
     */
    public static function typeOptions(): array
    {
        return [
            'tuition' => 'Tuition',
            'miscellaneous' => 'Miscellaneous',
            'discount' => 'Discount',
            'penalty' => 'Penalty',
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment details')
                    ->schema([
                        Select::make('account_id')
                            ->label('Student account')
                            ->options(function (): array {
                                $query = Account::query()->with('student');

                                $user = Auth::user();
                                if ($user && $user->hasRole('branch_manager') && $user->branch_id) {
                                    $query->whereHas('student', fn ($q) => $q->where('branch_id', $user->branch_id));
                                }

                                return $query->orderBy('id')
                                    ->get()
                                    ->filter(fn (Account $account): bool => $account->student !== null)
                                    ->mapWithKeys(function (Account $account): array {
                                        $student = $account->student;

                                        $label = $student->full_name.' — '.($student->student_id ?? 'ID #'.$account->id);

                                        return [$account->id => $label];
                                    })
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn (?Payment $record): bool => $record !== null),

                        Select::make('enrollment_id')
                            ->label('Enrollment (optional)')
                            ->options(function (Get $get): array {
                                $accountId = $get('account_id');
                                if (! $accountId) {
                                    return [];
                                }

                                $account = Account::query()
                                    ->with(['student.enrollments.schoolYear', 'student.enrollments.gradeLevel'])
                                    ->find($accountId);

                                if (! $account?->student) {
                                    return [];
                                }

                                return $account->student->enrollments
                                    ->mapWithKeys(fn ($e) => [
                                        $e->id => ($e->schoolYear?->name ?? 'Year').' — '.($e->gradeLevel?->name ?? 'Grade'),
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->nullable()
                            ->disabled(fn (?Payment $record): bool => $record !== null),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('₱')
                            ->disabled(fn (?Payment $record): bool => $record !== null),

                        Select::make('type')
                            ->label('Type')
                            ->options(self::typeOptions())
                            ->required()
                            ->native(false)
                            ->disabled(fn (?Payment $record): bool => $record !== null),

                        DateTimePicker::make('paid_at')
                            ->label('Paid at')
                            ->seconds(false)
                            ->default(fn () => now())
                            ->required()
                            ->disabled(fn (?Payment $record): bool => $record !== null),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
