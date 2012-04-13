<?php

/**
 * Utility class
 */
class WebToPay_Util {

    /**
     * Decodes url-safe-base64 encoded string
     * Url-safe-base64 is same as base64, but + is replaced to - and / to _
     *
     * @param string $encodedText
     *
     * @return string
     */
    public function decodeSafeUrlBase64($encodedText) {
        return base64_decode(strtr($encodedText, array('-' => '+', '_' => '/')));
    }

    /**
     * Encodes string to url-safe-base64
     * Url-safe-base64 is same as base64, but + is replaced to - and / to _
     *
     * @param string $text
     *
     * @return string
     */
    public function encodeSafeUrlBase64($text) {
        return strtr(base64_encode($text), array('+' => '-', '/' => '_'));
    }

    /**
     * Parses HTTP query to array
     *
     * @param string $query
     *
     * @return array
     */
    public function parseHttpQuery($query) {
        $params = array();
        parse_str($query, $params);
        if (get_magic_quotes_gpc()) {
            $params = $this->stripSlashesRecursively($params);
        }
        return $params;
    }

    /**
     * Strips slashes recursively, so this method can be used on arrays with more than one level
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function stripSlashesRecursively($data) {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[stripslashes($key)] = $this->stripSlashesRecursively($value);
            }
            return $result;
        } else {
            return stripslashes($data);
        }
    }
}