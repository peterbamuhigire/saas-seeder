<?php

namespace App\Helpers;

/**
 * UiHelper - UI-related utility functions
 */
class UiHelper
{
    /**
     * Get a random login background image from the login-bg directory
     *
     * @param int $totalImages Total number of background images available (default: 15)
     * @return string Path to the random background image
     */
    public static function getRandomLoginBackground(int $totalImages = 15): string
    {
        // Generate random number between 1 and $totalImages
        $randomNumber = rand(1, $totalImages);

        // Return the path to the random background image
        return "./uploads/login-bg/bground{$randomNumber}.jpg";
    }
}
