<?php
    /**
     * Created by PhpStorm.
     * User: outwork
     * Date: 3/18/16
     * Time: 11:07 PM
     */

namespace Josegonzalez\Upload\Validation\Traits;

use Cake\Utility\Hash;

trait CommonValidationTrait
{
    /**
     * Check that the file does not exceed the max
     * file size specified by PHP
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderPhpSizeLimit($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_INI_SIZE;
    }

    /**
     * Check that the file does not exceed the max
     * file size specified in the HTML Form
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderFormSizeLimit($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_FORM_SIZE;
    }

    /**
     * Check that the file was completely uploaded
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isCompletedUpload($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_PARTIAL;
    }

    /**
     * Check that a file was uploaded
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isFileUpload($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Check that the file was successfully written to the server
     *
     * @param mixed $check Value to check
     * @param bool $requireUpload Whether or not to require a file upload
     * @return bool Success
     */
    public static function isSuccessfulWrite($check, $requireUpload = true) {
        // Optional parameter check if passed or is $context array
        $requireUpload = is_array($requireUpload) ? true : $requireUpload;

        $error = (int)Hash::get($check, 'error');
        // Allow circumvention of this rule if uploads is not required
        if (!$requireUpload && $error === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        return $error !== UPLOAD_ERR_CANT_WRITE;
    }
} 