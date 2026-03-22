<?php

use App\Filesystem\WindowsSafeFilesystem;

it('replaces file contents', function () {
    $dir = sys_get_temp_dir().'/laravel-fs-test-'.uniqid('', true);
    mkdir($dir);

    $path = $dir.'/test.txt';
    file_put_contents($path, 'old');

    (new WindowsSafeFilesystem)->replace($path, 'new');

    expect(file_get_contents($path))->toBe('new');

    unlink($path);
    rmdir($dir);
});
