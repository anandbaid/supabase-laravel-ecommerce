<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImageResizer
{
    /**
     * Named variant => [maxWidth, maxHeight, quality].
     */
    public const VARIANTS = [
        'small' => [200, 200, 78],
        'medium' => [600, 600, 82],
        'large' => [1600, 1600, 85],
    ];

    /**
     * Resize an uploaded image into small/medium/large variants and store
     * all three on the given disk. Returns ['small' => path, 'medium' => path, 'large' => path].
     *
     * If GD is unavailable or resizing fails, all three keys fall back to
     * the single original (unresized) file so an upload never breaks
     * outright just because resizing had a problem.
     */
    public static function resizeAndStoreVariants(
        UploadedFile $file,
        string $directory,
        string $disk = 's3'
    ): array {
        try {
            if (! extension_loaded('gd')) {
                throw new RuntimeException('GD extension is not available.');
            }

            $sourcePath = $file->getRealPath();
            if ($sourcePath === false) {
                throw new RuntimeException('Uploaded file could not be read.');
            }

            [$width, $height, $type] = getimagesize($sourcePath) ?: [0, 0, null];
            if (! $width || ! $height || ! $type) {
                throw new RuntimeException('Could not determine image dimensions.');
            }

            $groupId = (string) Str::uuid();
            $paths = [];

            foreach (self::VARIANTS as $name => [$maxWidth, $maxHeight, $quality]) {
                $paths[$name] = self::renderVariant(
                    $sourcePath,
                    $type,
                    $width,
                    $height,
                    $maxWidth,
                    $maxHeight,
                    $quality,
                    $directory,
                    $disk,
                    $groupId,
                    $name
                );
            }

            return $paths;
        } catch (Throwable $e) {
            Log::error('Image variant resize failed, storing original for all sizes: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $original = $file->store($directory, $disk);

            return [
                'small' => $original,
                'medium' => $original,
                'large' => $original,
            ];
        }
    }

    /**
     * @throws RuntimeException
     */
    private static function renderVariant(
        string $sourcePath,
        int $type,
        int $width,
        int $height,
        int $maxWidth,
        int $maxHeight,
        int $quality,
        string $directory,
        string $disk,
        string $groupId,
        string $variantName
    ): string {
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $source) {
            throw new RuntimeException('Unsupported or unreadable image type.');
        }

        // Never upscale — only shrink images larger than the variant's max bounds.
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1.0);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        $extension = self::extensionFor($type);
        $tmpPath = tempnam(sys_get_temp_dir(), 'img_') . $extension;

        $written = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($resized, $tmpPath, $quality),
            IMAGETYPE_PNG => imagepng($resized, $tmpPath, (int) round((100 - $quality) / 100 * 9)),
            IMAGETYPE_GIF => imagegif($resized, $tmpPath),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($resized, $tmpPath, $quality) : false,
            default => false,
        };

        imagedestroy($resized);

        if (! $written || ! file_exists($tmpPath)) {
            throw new RuntimeException("Failed to encode {$variantName} image variant.");
        }

        $filename = "{$groupId}_{$variantName}" . $extension;
        $storagePath = trim($directory, '/') . '/' . $filename;

        $stream = fopen($tmpPath, 'r');
        try {
            Storage::disk($disk)->put($storagePath, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($tmpPath);
        }

        return $storagePath;
    }

    private static function extensionFor(int $type): string
    {
        return match ($type) {
            IMAGETYPE_JPEG => '.jpg',
            IMAGETYPE_PNG => '.png',
            IMAGETYPE_GIF => '.gif',
            IMAGETYPE_WEBP => '.webp',
            default => '.jpg',
        };
    }

    /**
     * Resize a single uploaded image to one size and store it. Used for
     * gallery images, where three variants per photo isn't needed.
     * Falls back to the original file if resizing fails.
     */
    public static function resizeAndStoreSingle(
        UploadedFile $file,
        string $directory,
        string $disk = 's3',
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $quality = 85
    ): string {
        try {
            if (! extension_loaded('gd')) {
                throw new RuntimeException('GD extension is not available.');
            }

            $sourcePath = $file->getRealPath();
            if ($sourcePath === false) {
                throw new RuntimeException('Uploaded file could not be read.');
            }

            [$width, $height, $type] = getimagesize($sourcePath) ?: [0, 0, null];
            if (! $width || ! $height || ! $type) {
                throw new RuntimeException('Could not determine image dimensions.');
            }

            return self::renderVariant(
                $sourcePath,
                $type,
                $width,
                $height,
                $maxWidth,
                $maxHeight,
                $quality,
                $directory,
                $disk,
                (string) Str::uuid(),
                'gallery'
            );
        } catch (Throwable $e) {
            Log::error('Gallery image resize failed, storing original: ' . $e->getMessage(), ['exception' => $e]);
            return $file->store($directory, $disk);
        }
    }

    /**
     * Delete all three variants for a product/category, ignoring individual
     * failures so one bad path doesn't block the others.
     */
    public static function deleteVariants(?string $small, ?string $medium, ?string $large, string $disk = 's3'): void
    {
        foreach (array_unique(array_filter([$small, $medium, $large])) as $path) {
            try {
                Storage::disk($disk)->delete($path);
            } catch (Throwable $e) {
                Log::error('Failed to delete image variant: ' . $e->getMessage(), ['exception' => $e, 'path' => $path]);
            }
        }
    }

    /**
     * Delete a list of arbitrary stored paths (e.g. gallery images),
     * ignoring individual failures.
     */
    public static function deletePaths(array $paths, string $disk = 's3'): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            try {
                Storage::disk($disk)->delete($path);
            } catch (Throwable $e) {
                Log::error('Failed to delete image: ' . $e->getMessage(), ['exception' => $e, 'path' => $path]);
            }
        }
    }
}