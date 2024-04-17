<?php

declare(strict_types=1);

/**
 * Builds and signs requests
 */
class WebToPay_RequestBuilder
{
    private const REQUEST_SPECS = [
        ['orderid', 40, true, ''],
        ['accepturl', 255, true, ''],
        ['cancelurl', 255, true, ''],
        ['callbackurl', 255, true, ''],
        ['lang', 3, false, '/^[a-z]{3}$/i'],
        ['amount', 11, false, '/^\d+$/'],
        ['currency', 3, false, '/^[a-z]{3}$/i'],
        ['payment', 20, false, ''],
        ['country', 2, false, '/^[a-z_]{2}$/i'],
        ['paytext', 255, false, ''],
        ['p_firstname', 255, false, ''],
        ['p_lastname', 255, false, ''],
        ['p_email', 255, false, ''],
        ['p_street', 255, false, ''],
        ['p_city', 255, false, ''],
        ['p_state', 255, false, ''],
        ['p_zip', 20, false, ''],
        ['p_countrycode', 2, false, '/^[a-z]{2}$/i'],
        ['test', 1, false, '/^[01]$/'],
        ['time_limit', 19, false, '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
    ];

    protected string $projectPassword;

    protected WebToPay_Util $util;

    protected int $projectId;

    protected WebToPay_UrlBuilder $urlBuilder;

    /**
     * Constructs object
     */
    public function __construct(
        int $projectId,
        string $projectPassword,
        WebToPay_Util $util,
        WebToPay_UrlBuilder $urlBuilder
    ) {
        $this->projectId = $projectId;
        $this->projectPassword = $projectPassword;
        $this->util = $util;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * @param array<string, mixed> $data information about current payment request
     *
     * @return array<string, mixed>
     *
     * @throws WebToPayException
     */
    public function buildRequest(array $data): array
    {
        $this->validateRequest($data);
        $data['version'] = WebToPay::VERSION;
        $data['projectid'] = $this->projectId;
        unset($data['repeat_request']);

        return $this->createRequest($data);
    }

    /**
     * Builds the full request url (including the protocol and the domain)
     *
     * @param array<string, mixed> $data
     * @return string
     * @throws WebToPayException
     */
    public function buildRequestUrlFromData(array $data): string
    {
        $language = $data['lang'] ?? null;
        $request = $this->buildRequest($data);

        return $this->urlBuilder->buildForRequest($request, $language);
    }

    /**
     * Builds repeat request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * @param int $orderId order id of repeated request
     *
     * @return array<string, mixed>
     *
     * @throws WebToPayException
     */
    public function buildRepeatRequest(int $orderId): array
    {
        $data['orderid'] = $orderId;
        $data['version'] = WebToPay::VERSION;
        $data['projectid'] = $this->projectId;
        $data['repeat_request'] = '1';

        return $this->createRequest($data);
    }

    /**
     * Builds the full request url for a repeated request (including the protocol and the domain)
     *
     * @throws WebToPayException
     */
    public function buildRepeatRequestUrlFromOrderId(int $orderId): string
    {
        $request = $this->buildRepeatRequest($orderId);

        return $this->urlBuilder->buildForRequest($request);
    }

    /**
     * Checks data to be valid by passed specification
     *
     * @param array<string, mixed> $data
     *
     * @throws WebToPay_Exception_Validation
     */
    protected function validateRequest(array $data): void
    {
        foreach (self::REQUEST_SPECS as $spec) {
            [$name, $maxlen, $required, $regexp] = $spec;

            if ($required && empty($data[$name])) {
                throw new WebToPay_Exception_Validation(
                    sprintf("'%s' is required but missing.", $name),
                    WebToPayException::E_MISSING,
                    $name
                );
            }

            if (!empty($data[$name])) {
                if ($maxlen && strlen((string) $data[$name]) > $maxlen) {
                    throw new WebToPay_Exception_Validation(sprintf(
                        "'%s' value is too long (%d), %d characters allowed.",
                        $name,
                        strlen($data[$name]),
                        $maxlen
                    ), WebToPayException::E_MAXLEN, $name);
                }

                if ($regexp !== ''  && !preg_match($regexp, (string) $data[$name])) {
                    throw new WebToPay_Exception_Validation(
                        sprintf("'%s' value '%s' is invalid.", $name, $data[$name]),
                        WebToPayException::E_REGEXP,
                        $name
                    );
                }
            }
        }
    }

    /**
     * Makes request data array from parameters, also generates signature
     *
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    protected function createRequest(array $request): array
    {
        $data = $this->util->encodeSafeUrlBase64(http_build_query($request, '', '&'));

        return [
            'data' => $data,
            'sign' => md5($data . $this->projectPassword),
        ];
    }
}
