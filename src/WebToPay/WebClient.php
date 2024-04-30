<?php

declare(strict_types=1);

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
     * @throws WebToPayException
     */
    public function get(string $uri, array $queryData = []): string
    {
        if (count($queryData) > 0) {
            $uri .= strpos($uri, '?') === false ? '?' : '&';
            $uri .= http_build_query($queryData, '', '&');
        }
        $url = parse_url($uri);
        if ('https' === ($url['scheme'] ?? '')) {
            $host = 'ssl://' . ($url['host'] ?? '');
            $port = 443;
        } else {
            $host = $url['host'] ?? '';
            $port = 80;
        }

        $fp = $this->openSocket($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            throw new WebToPayException(sprintf('Cannot connect to %s', $uri), WebToPayException::E_INVALID);
        }

        if(isset($url['query'])) {
            $data = ($url['path'] ?? '') . '?' . $url['query'];
        } else {
            $data = ($url['path'] ?? '');
        }

        $out = "GET " . $data . " HTTP/1.0\r\n";
        $out .= "Host: " . ($url['host'] ?? '') . "\r\n";
        $out .= "Connection: Close\r\n\r\n";

        $content = $this->getContentFromSocket($fp, $out);

        // Separate header and content
        [$header, $content] = explode("\r\n\r\n", $content, 2);

        return trim($content);
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $errno
     * @param string $errstr
     * @param float $timeout
     * @return false|resource
     */
    protected function openSocket(string $host, int $port, &$errno, &$errstr, float $timeout = 30)
    {
        return fsockopen($host, $port, $errno, $errstr, $timeout);
    }

    /**
     * @param resource $fp
     * @param string $out
     *
     * @return string
     */
    protected function getContentFromSocket($fp, string $out): string
    {
        fwrite($fp, $out);
        $content = (string) stream_get_contents($fp);
        fclose($fp);

        return $content;
    }
}
