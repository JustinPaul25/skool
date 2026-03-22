<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController extends Controller
{
    /**
     * Stream a media file from the configured disk. Used when media URLs are same-origin
     * so the browser does not need CORS headers on the object storage bucket.
     */
    public function show(Request $request, Media $media, ?string $conversion = null): StreamedResponse
    {
        $authorized = $request->hasValidSignature() || auth()->check();

        abort_unless($authorized, 403);

        $relativePath = $media->getPathRelativeToRoot($conversion ?? '');

        abort_unless(Storage::disk($media->disk)->exists($relativePath), 404);

        return Storage::disk($media->disk)->response(
            $relativePath,
            $media->file_name,
            [
                'Content-Type' => $media->mime_type ?? 'application/octet-stream',
            ],
        );
    }
}
