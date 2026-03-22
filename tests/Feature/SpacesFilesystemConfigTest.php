<?php

it('registers the spaces disk for DigitalOcean Spaces (S3-compatible)', function () {
    expect(config('filesystems.disks.spaces.driver'))->toBe('s3')
        ->and(config('filesystems.disks.spaces'))->toHaveKeys(['key', 'secret', 'region', 'bucket', 'endpoint', 'url']);
});
