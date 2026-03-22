<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Account;
use App\Services\PaymentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($accountId = request()->query('account_id')) {
            $this->form->fill([
                'account_id' => (int) $accountId,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $account = Account::query()->findOrFail($data['account_id']);

        return app(PaymentService::class)->record($account, [
            'enrollment_id' => $data['enrollment_id'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'],
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['paid_at'] ?? now(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Payment recorded';
    }
}
