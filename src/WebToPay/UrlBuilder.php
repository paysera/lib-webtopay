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
        return $this->createUrlFromRequestAndLanguage($request, $language);
    }

    /**
     * Builds a complete URL for payment list API
     *
     * @param int $projectId
     * @param string $currency
     * @return string
     */
    public function buildForPaymentsMethodList($projectId, $currency) {
        $routeWithNoDomain = $this->environmentSettings['paymentMethodList'];
        $route = str_replace(self::PLACEHOLDER_KEY, $this->getDefaultDomain(), $routeWithNoDomain);
        return $route . $projectId . '/currency:' . $currency;
    }

    /**
     * Builds a complete URL for Sms Answer
     *
     * @return string
     */
    public function buildForSmsAnswer() {
        $routeWithNoDomain = $this->environmentSettings['smsAnswer'];
        $route = str_replace(self::PLACEHOLDER_KEY, $this->getDefaultDomain(), $routeWithNoDomain);
        return $route;
    }

    /**
     * Build the url to the public key
     *
     * @return string
     */
    public function buildForPublicKey() {
        $routeWithNoDomain = $this->environmentSettings['publicKey'];
        $route = str_replace(self::PLACEHOLDER_KEY, $this->getDefaultDomain(), $routeWithNoDomain);
        return $route;
    }

    /**
     * Creates an URL from the request and data provided.
     *
     * @param array $request
     * @param array $language
     * @return string
     */
    protected function createUrlFromRequestAndLanguage($request, $language) {
        $url = $this->getPaymentUrl($language) . '?' . http_build_query($request);
        return preg_replace('/[\r\n]+/is', '', $url);
    }

    /**
     * Returns payment url. Argument is same as lang parameter in request data
     *
     * @param  string $language
     * @return string $url
     */
    protected function getPaymentUrl($language) {
        $routeWithNoDomain = $this->environmentSettings['payment'];
        $route = str_replace(self::PLACEHOLDER_KEY, $this->getDomainByLanguage($language), $routeWithNoDomain);
        return $route;
    }

    /**
     * Gets the domain by lang variable
     * lit -> mokejimai.lt
     * eng -> paysera
     * etc
     *
     * @param $language
     * @return string
     */
    protected function getDomainByLanguage($language) {
        if (isset($this->configuration['domains'][$language])) {
            return $this->configuration['domains'][$language];
        } else {
            $defaultLanguage = $this->configuration['defaultDomainLanguage'];
            return $this->configuration['domains'][$defaultLanguage];
        }
    }

    /**
     * Gets the domain for the default language
     *
     * @return string
     */
    protected function getDefaultDomain()
    {
        return $this->getDomainByLanguage($this->configuration['defaultDomainLanguage']);
    }
}
