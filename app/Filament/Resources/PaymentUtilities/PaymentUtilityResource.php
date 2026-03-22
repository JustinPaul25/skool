<?php

namespace App\Filament\Resources\PaymentUtilities;

use App\Filament\Resources\PaymentUtilities\Pages\CreatePaymentUtility;
use App\Filament\Resources\PaymentUtilities\Pages\EditPaymentUtility;
use App\Filament\Resources\PaymentUtilities\Pages\ListPaymentUtilities;
use App\Filament\Resources\PaymentUtilities\Pages\ViewPaymentUtility;
use App\Filament\Resources\PaymentUtilities\Schemas\PaymentUtilityForm;
use App\Filament\Resources\PaymentUtilities\Schemas\PaymentUtilityInfolist;
use App\Filament\Resources\PaymentUtilities\Tables\PaymentUtilitiesTable;
use App\Models\PaymentUtility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PaymentUtilityResource extends Resource
{
    protected static ?string $model = PaymentUtility::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Payment utilities';
    }

    public static function getModelLabel(): string
    {
        return 'Payment utility';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Payment utilities';
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentUtilityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentUtilityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentUtilitiesTable::configure($table);
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
            'index' => ListPaymentUtilities::route('/'),
            'create' => CreatePaymentUtility::route('/create'),
            'view' => ViewPaymentUtility::route('/{record}'),
            'edit' => EditPaymentUtility::route('/{record}/edit'),
        ];
    }
}
