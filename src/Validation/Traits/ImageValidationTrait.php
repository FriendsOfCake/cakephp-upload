<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Validation\Traits;

use Psr\Http\Message\UploadedFileInterface;

trait ImageValidationTrait
{
    /**
     * Check that the file is above the minimum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @return bool Success
     */
    public static function isAboveMinWidth($check, int $width): bool
    {
        if ($check instanceof UploadedFileInterface) {
            $file = $check->getStream()->getMetadata('uri');
        } else {
            // Non-file uploads also mean the height is too big
            if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
                return false;
            }
            $file = $check['tmp_name'];
        }
        [$imgWidth] = getimagesize($file);

        return $width > 0 && $imgWidth >= $width;
    }

    /**
     * Check that the file is below the maximum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @return bool Success
     */
    public static function isBelowMaxWidth($check, int $width): bool
    {
        if ($check instanceof UploadedFileInterface) {
            $file = $check->getStream()->getMetadata('uri');
        } else {
            // Non-file uploads also mean the height is too big
            if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
                return false;
            }

            $file = $check['tmp_name'];
        }
        [$imgWidth] = getimagesize($file);

        return $width > 0 && $imgWidth <= $width;
    }

    /**
     * Check that the file is above the minimum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isAboveMinHeight($check, int $height): bool
    {
        if ($check instanceof UploadedFileInterface) {
            $file = $check->getStream()->getMetadata('uri');
        } else {
            // Non-file uploads also mean the height is too big
            if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
                return false;
            }
            $file = $check['tmp_name'];
        }
        [, $imgHeight] = getimagesize($file);

        return $height > 0 && $imgHeight >= $height;
    }

    /**
     * Check that the file is below the maximum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isBelowMaxHeight($check, int $height): bool
    {
        if ($check instanceof UploadedFileInterface) {
            $file = $check->getStream()->getMetadata('uri');
        } else {
            // Non-file uploads also mean the height is too big
            if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
                return false;
            }
            $file = $check['tmp_name'];
        }
        [, $imgHeight] = getimagesize($file);

        return $height > 0 && $imgHeight <= $height;
    }
}
