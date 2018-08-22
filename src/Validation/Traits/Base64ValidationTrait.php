<?php

namespace Josegonzalez\Upload\Validation\Traits;

trait Base64ValidationTrait
{

    /**
     * Allows only the specified mime type to be upload when using base64 uploader
     *
     * @param mixed $check Value to check
     * @param string $allowedMimeType Allowed mime type
     * @return bool Success
     */
    public static function isMimeType($check, $allowedMimeType)
    {
        $f = finfo_open();
        $mimeType = finfo_buffer($f, base64_decode($check), FILEINFO_MIME_TYPE);

        return $mimeType === $allowedMimeType;
    }
}
