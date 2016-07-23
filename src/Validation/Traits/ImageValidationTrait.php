<?php

namespace Josegonzalez\Upload\Validation\Traits;

trait ImageValidationTrait
{
    /**
     * Check that the file is above the minimum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @return bool Success
     */
    public static function isAboveMinWidth($check, $width)
    {
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list($imgWidth) = getimagesize($check['tmp_name']);

        return $width > 0 && $imgWidth >= $width;
    }

    /**
     * Check that the file is below the maximum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @return bool Success
     */
    public static function isBelowMaxWidth($check, $width)
    {
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list($imgWidth) = getimagesize($check['tmp_name']);

        return $width > 0 && $imgWidth <= $width;
    }

    /**
     * Check that the file is above the minimum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isAboveMinHeight($check, $height)
    {
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list(, $imgHeight) = getimagesize($check['tmp_name']);

        return $height > 0 && $imgHeight >= $height;
    }

    /**
     * Check that the file is below the maximum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isBelowMaxHeight($check, $height)
    {
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list(, $imgHeight) = getimagesize($check['tmp_name']);

        return $height > 0 && $imgHeight <= $height;
    }
}
