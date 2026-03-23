<?php

arch('app')
    ->expect('App\\Models')
    ->toUseStrictTypes();

it('App\\Services have no public properties', function () {
    $serviceClasses = [
        \App\Services\PaymentReceiptService::class,
        \App\Services\SchoolYearService::class,
        \App\Services\SettingsService::class,
        \App\Services\PaymentService::class,
        \App\Services\EnrollmentApplicationService::class,
    ];

    foreach ($serviceClasses as $class) {
        $ref = new ReflectionClass($class);
        $publicProps = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
        expect(count($publicProps))->toBe(0);
    }
});
