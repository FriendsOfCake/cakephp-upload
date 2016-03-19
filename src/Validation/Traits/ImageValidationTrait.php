<?php

namespace Josegonzalez\Upload\Validation\Traits;

use Cake\Utility\Hash;

trait ImageValidationTrait
{
    /**
     * Check that the file is above the minimum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @param bool $requireUpload Whether or not to require a file upload
     * @return bool Success
     */
    public static function isAboveMinWidth($check, $width, $requireUpload = true)
    {
        // Optional parameter check if passed or is $context array
        $requireUpload = is_array($requireUpload) ? true : $requireUpload;

        $error = (int)Hash::get($check, 'error');
        // Allow circumvention of this rule if uploads is not required
        if (!$requireUpload && $error === UPLOAD_ERR_NO_FILE) {
            return true;
        }
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
     * @param bool $requireUpload Whether or not to require a file upload
     * @return bool Success
     */
    public static function isBelowMaxWidth($check, $width, $requireUpload = true)
    {
        // Optional parameter check if passed or is $context array
        $requireUpload = is_array($requireUpload) ? true : $requireUpload;

        $error = (int)Hash::get($check, 'error');
        // Allow circumvention of this rule if uploads is not required
        if (!$requireUpload && $error === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list($imgWidth) = getimagesize($check['tmp_name']);
        return $width > 0 && $imgWidth <= $width;
    }

    /**
     * Check that the file is below the maximum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @param bool $requireUpload Whether or not to require a file upload
     * @return bool Success
     */
    public static function isBelowMaxHeight($check, $height, $requireUpload = true)
    {
        // Optional parameter check if passed or is $context array
        $requireUpload = is_array($requireUpload) ? true : $requireUpload;

        $error = (int)Hash::get($check, 'error');
        // Allow circumvention of this rule if uploads is not required
        if (!$requireUpload && $error === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list(, $imgHeight) = getimagesize($check['tmp_name']);
        return $height > 0 && $imgHeight <= $height;
    }

    /**
     * Check that the file is above the minimum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @param bool $requireUpload Whether or not to require a file upload
     * @return bool Success
     */
    public static function isAboveMinHeight($check, $height, $requireUpload = true)
    {
        // Optional parameter check if passed or is $context array
        $requireUpload = is_array($requireUpload) ? true : $requireUpload;

        $error = (int)Hash::get($check, 'error');
        // Allow circumvention of this rule if uploads is not required
        if (!$requireUpload && $error === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        // Non-file uploads also mean the height is too big
        if (!isset($check['tmp_name']) || !strlen($check['tmp_name'])) {
            return false;
        }
        list(, $imgHeight) = getimagesize($check['tmp_name']);
        return $height > 0 && $imgHeight >= $height;
    }

    /**
     * Check that the file is above the minimum file upload size
     *
     * @param mixed $check Value to check
     * @param int $size Minimum file size
     * @param bool $requireUpload Whether or not to require a file upload
     * @return bool Success
     */
    public static function isAboveMinSize($check, $size, $requireUpload = true)
    {
        // Optional parameter check if passed or is $context array
        $requireUpload = is_array($requireUpload) ? true : $requireUpload;

        $error = (int)Hash::get($check, 'error');
        // Allow circumvention of this rule if uploads is not required
        if (!$requireUpload && $error === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        // Non-file uploads also mean the size is too small
        if (!isset($check['size']) || !strlen($check['size'])) {
            return false;
        }
        return $check['size'] >= $size;
    }
}
