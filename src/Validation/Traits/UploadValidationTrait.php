<?php

namespace Josegonzalez\Upload\Validation\Traits;

use Cake\Utility\Hash;

trait UploadValidationTrait
{
    /**
     * Check that the file does not exceed the max
     * file size specified by PHP
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderPhpSizeLimit($check)
    {
        return Hash::get($check, 'error') !== UPLOAD_ERR_INI_SIZE;
    }

    /**
     * Check that the file does not exceed the max
     * file size specified in the HTML Form
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderFormSizeLimit($check)
    {
        return Hash::get($check, 'error') !== UPLOAD_ERR_FORM_SIZE;
    }

    /**
     * Check that the file was completely uploaded
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isCompletedUpload($check)
    {
        return Hash::get($check, 'error') !== UPLOAD_ERR_PARTIAL;
    }

    /**
     * Check that a file was uploaded
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isFileUpload($check)
    {
        return Hash::get($check, 'error') !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Check that the file was successfully written to the server
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isSuccessfulWrite($check)
    {
        return Hash::get($check, 'error') !== UPLOAD_ERR_CANT_WRITE;
    }

    /**
     * Check that the file is above the minimum file upload size
     *
     * @param mixed $check Value to check
     * @param int $size Minimum file size
     * @return bool Success
     */
    public static function isAboveMinSize($check, $size)
    {
        // Non-file uploads also mean the size is too small
        if (!isset($check['size']) || !strlen($check['size'])) {
            return false;
        }

        return $check['size'] >= $size;
    }

    /**
     * Check that the file is below the maximum file upload size
     *
     * @param mixed $check Value to check
     * @param int $size Maximum file size
     * @return bool Success
     */
    public static function isBelowMaxSize($check, $size)
    {
        // Non-file uploads also mean the size is too small
        if (!isset($check['size']) || !strlen($check['size'])) {
            return false;
        }

        return $check['size'] <= $size;
    }
}
