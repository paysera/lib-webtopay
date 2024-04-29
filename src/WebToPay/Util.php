<?php

declare(strict_types=1);

/**
 * Utility class
 */
class WebToPay_Util
{
    public const GCM_CIPHER = 'aes-256-gcm';
    public const GCM_AUTH_KEY_LENGTH = 16;

    /**
     * Decodes url-safe-base64 encoded string
     * Url-safe-base64 is same as base64, but + is replaced to - and / to _
     */
    public function decodeSafeUrlBase64(string $encodedText): string
    {
        return (string) base64_decode(strtr($encodedText, '-_', '+/'), true);
    }

    /**
     * Encodes string to url-safe-base64
     * Url-safe-base64 is same as base64, but + is replaced to - and / to _
     */
    public function encodeSafeUrlBase64(string $text): string
    {
        return strtr(base64_encode($text), '+/', '-_');
    }

    /**
     * Decrypts string with aes-256-gcm algorithm
     */
    public function decryptGCM(string $stringToDecrypt, string $key): ?string
    {
        $ivLength = (int) openssl_cipher_iv_length(self::GCM_CIPHER);
        $iv = substr($stringToDecrypt, 0, $ivLength);
        $ciphertext = substr($stringToDecrypt, $ivLength, -self::GCM_AUTH_KEY_LENGTH);
        $tag = substr($stringToDecrypt, -self::GCM_AUTH_KEY_LENGTH);

        $decryptedText = openssl_decrypt(
            $ciphertext,
            self::GCM_CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $decryptedText === false ? null : $decryptedText;
    }

    /**
     * Parses HTTP query to array
     *
     * @param string $query
     *
     * @return array<int|string, mixed>
     */
    public function parseHttpQuery(string $query): array
    {
        $params = [];
        parse_str($query, $params);

        return $params;
    }
}
