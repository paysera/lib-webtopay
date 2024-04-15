<?php

/**
 * Simple web client
 */
class WebToPay_WebClient
{
    /**
     * Gets page contents by specified URI. Adds query data if provided to the URI
     * Ignores status code of the response and header fields
     *
     * @param string $uri
     * @param array<string, mixed> $queryData
     *
     * @return string
     *
     * @throws WebToPayException
     */
    public function get(string $uri, array $queryData = []): string
    {
        // Append query data to the URI if provided
        if (!empty($queryData)) {
            $uri .= (strpos($uri, '?') === false ? '?' : '&')
                . http_build_query($queryData, '', '&');
        }

        // Parse URL
        $url = parse_url($uri);
        $scheme = isset($url['scheme']) ? $url['scheme'] : 'http';
        $host = $url['host'] ?? '';
        $port = $scheme === 'https' ? 443 : 80;
        $path = $url['path'] ?? '/';
        $query = isset($url['query']) ? '?' . $url['query'] : '';

        // Open socket connection
        $fp = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            throw new WebToPayException(sprintf('Cannot connect to %s', $uri), WebToPayException::E_INVALID);
        }

        // Construct HTTP request
        $out = "GET {$path}{$query} HTTP/1.1\r\n";
        $out .= "Host: {$host}\r\n";
        $out .= "Connection: Close\r\n\r\n";

        // Send request and read response
        fwrite($fp, $out);
        $content = (string) stream_get_contents($fp);
        fclose($fp);

        // Separate header and content
        list($header, $content) = explode("\r\n\r\n", $content, 2);

        return trim($content);
    }
}
