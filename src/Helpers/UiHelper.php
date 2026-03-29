<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * UiHelper — UI-related utility functions.
 */
final class UiHelper
{
    /**
     * Directory inside public/ where login background images are stored.
     * Drop any .jpg, .jpeg, .png, or .webp files here — the sign-in page
     * picks one at random on each load.
     */
    private const BG_DIR = '/assets/images/login-backgrounds';

    /**
     * Get a random login background image path.
     *
     * Scans the login-backgrounds directory for image files and returns
     * a web-accessible path to a random one. Returns empty string if
     * the directory is empty (caller should handle gracefully).
     */
    public static function getRandomLoginBackground(): string
    {
        $absoluteDir = $_SERVER['DOCUMENT_ROOT'] . self::BG_DIR;

        if (!is_dir($absoluteDir)) {
            return '';
        }

        $files = glob($absoluteDir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        if (empty($files)) {
            return '';
        }

        $chosen = $files[random_int(0, count($files) - 1)];

        return self::BG_DIR . '/' . basename($chosen);
    }
}
