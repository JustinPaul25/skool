<?php

namespace App\Filament\Pages;

use App\Filament\Schemas\SchoolSettingsForm;
use App\Models\SchoolSetting;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use UnitEnum;

class SettingsPage extends Page
{
    protected static ?string $slug = 'settings';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 10;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    #[Locked]
    public ?SchoolSetting $schoolSetting = null;

    public static function getNavigationLabel(): string
    {
        return __('School settings');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('administrator');
    }

    public function mount(): void
    {
        $this->schoolSetting = SchoolSetting::instance();

        Gate::authorize('update', $this->schoolSetting);

        $this->form->fill($this->schoolSetting->attributesToArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        if (! $schema->hasCustomColumns()) {
            $schema->columns(2);
        }

        abort_unless($this->schoolSetting instanceof SchoolSetting, 500);

        return $schema
            ->model($this->schoolSetting)
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return SchoolSettingsForm::configure($schema);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment(static::$formActionsAlignment)
                            ->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        Gate::authorize('update', $this->schoolSetting);

        $data = $this->form->getState();

        app(SettingsService::class)->update($data);

        $this->schoolSetting->refresh();

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
