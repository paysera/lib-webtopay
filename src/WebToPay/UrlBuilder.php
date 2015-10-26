<?php


/**
 * Used to build a complete request URL.
 *
 * Class WebToPay_UrlBuilder
 */
class WebToPay_UrlBuilder {

    const PLACEHOLDER_KEY = '[domain]';

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var array
     */
    protected $environmentSettings;

    /**
     * @param array $configuration
     * @param string $environment
     */
    function __construct($configuration, $environment)
    {
        $this->configuration = $configuration;
        $this->environment = $environment;
        $this->environmentSettings = $this->configuration['routes'][$this->environment];
    }

    /**
     * Builds a complete request URL based on the provided parameters
     *
     * @param $request
     * @param null $language
     * @return string
     */
    public function buildForRequest($request, $language = null) {
        return $this->createUrlFromRequestAndLanguage($request);
    }

    /**
     * Builds a complete URL for payment list API
     *
     * @param int $projectId
     * @param string $currency
     * @return string
     */
    public function buildForPaymentsMethodList($projectId, $currency) {
        $route = $this->environmentSettings['paymentMethodList'];
        return $route . $projectId . '/currency:' . $currency;
    }

    /**
     * Builds a complete URL for Sms Answer
     *
     * @return string
     */
    public function buildForSmsAnswer() {
        $route = $this->environmentSettings['smsAnswer'];
        return $route;
    }

    /**
     * Build the url to the public key
     *
     * @return string
     */
    public function buildForPublicKey() {
        $route = $this->environmentSettings['publicKey'];
        return $route;
    }

    /**
     * Creates an URL from the request and data provided.
     *
     * @param array $request
     * @return string
     */
    protected function createUrlFromRequestAndLanguage($request) {
        $url = $this->getPaymentUrl() . '?' . http_build_query($request, null, '&');
        return preg_replace('/[\r\n]+/is', '', $url);
    }

    /**
     * Returns payment url. Argument is same as lang parameter in request data
     *
     * @return string $url
     */
    public function getPaymentUrl() {
        $route = $this->environmentSettings['payment'];
        return $route;
    }
}
