<?php

/**
 * Simple web client
 */
class WebToPay_WebClient {

    /**
     * Gets page contents by specified URI. Adds query data if provided to the URI
     * Ignores status code of the response and header fields
     *
     * @param string $uri
     * @param array  $queryData
     *
     * @return string
     *
     * @throws WebToPayException
     */
    public function get($uri, array $queryData = array()) {
        if (count($queryData) > 0) {
            $uri .= strpos($uri, '?') === false ? '?' : '&';
            $uri .= http_build_query($queryData);
        }
        $url = parse_url($uri);
        if ('https' == $url['scheme']) {
            $host = 'ssl://'.$url['host'];
            $port = 443;
        } else {
            $host = $url['host'];
            $port = 80;
        }

        $fp = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            throw new WebToPayException(sprintf('Cannot connect to %s', $uri), WebToPayException::E_INVALID);
        }

        if(isset($url['query'])) {
            $data = $url['path'].'?'.$url['query'];
        } else {
            $data = $url['path'];
        }

        $out = "GET " . $data . " HTTP/1.0\r\n";
        $out .= "Host: ".$url['host']."\r\n";
        $out .= "Connection: Close\r\n\r\n";

        $content = '';

        fwrite($fp, $out);
        while (!feof($fp)) $content .= fgets($fp, 8192);
        fclose($fp);

        list($header, $content) = explode("\r\n\r\n", $content, 2);

        return trim($content);
    }
}