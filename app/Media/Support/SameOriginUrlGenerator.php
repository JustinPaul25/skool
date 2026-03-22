<?php

namespace App\Media\Support;

use DateTimeInterface;
use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

/**
 * Serves Spatie Media Library files through the application origin so Filament FilePond
 * can fetch previews (and load the image editor) without cross-origin CORS failures against S3/Spaces.
 *
 * When {@see config('media-library.use_same_origin_urls')} is false, defers to direct disk URLs
 * (configure CORS on the bucket if you disable this).
 */
final class SameOriginUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if (! config('media-library.use_same_origin_urls')) {
            return parent::getUrl();
        }

        $conversion = $this->conversion?->getName();

        $parameters = ['media' => $this->media->getKey()];

        if ($conversion !== null && $conversion !== '') {
            $parameters['conversion'] = $conversion;
        }

        return $this->versionUrl(route('media.file', $parameters, absolute: false));
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        if (! config('media-library.use_same_origin_urls')) {
            return parent::getTemporaryUrl($expiration, $options);
        }

        $conversion = $this->conversion?->getName();

        $parameters = ['media' => $this->media->getKey()];

        if ($conversion !== null && $conversion !== '') {
            $parameters['conversion'] = $conversion;
        }

        return URL::temporarySignedRoute('media.file', $expiration, $parameters);
    }
}
