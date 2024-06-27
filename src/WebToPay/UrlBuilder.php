<?php

declare(strict_types=1);

/**
 * Used to build a complete request URL.
 *
 * Class WebToPay_UrlBuilder
 */
class WebToPay_UrlBuilder
{
    public const PLACEHOLDER_KEY = '[domain]';

    protected WebToPay_Config $configuration;

    protected string $environment;

    /**
     * @var array<string, string>
     */
    protected WebToPay_Routes $environmentSettings;

    /**
     * @param WebToPay_Config $configuration
     * @param string $environment
     */
    public function __construct(WebToPay_Config $configuration, string $environment)
    {
        $this->configuration = $configuration;
        $this->environment = $environment;
        $this->environmentSettings = $this->configuration->getRoutes();
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Builds a complete request URL based on the provided parameters
     *
     * @param array<string, mixed> $request
     *
     * @return string
     */
    public function buildForRequest(array $request): string
    {
        return $this->createUrlFromRequestAndLanguage($request);
    }

    /**
     * Builds a complete URL for payment list API
     */
    public function buildForPaymentsMethodList(int $projectId, ?string $amount, ?string $currency): string
    {
        $route = $this->environmentSettings->getPaymentMethodList();

        return $route . $projectId . '/currency:' . $currency . '/amount:' . $amount;
    }

    /**
     * Builds a complete URL for Sms Answer
     *
     * @codeCoverageIgnore
     */
    public function buildForSmsAnswer(): string
    {
        return $this->environmentSettings->getSmsAnswer();
    }

    /**
     * Build the URL to the public key
     */
    public function buildForPublicKey(): string
    {
        return $this->environmentSettings->getPublicKey();
    }

    /**
     * Creates a URL from the request and data provided.
     *
     * @param array<string, mixed> $request
     *
     * @return string
     */
    protected function createUrlFromRequestAndLanguage(array $request): string
    {
        $url = $this->getPaymentUrl() . '?' . http_build_query($request, '', '&');

        return preg_replace('/[\r\n]+/is', '', $url) ?? '';
    }

    /**
     * Returns payment URL. Argument is same as lang parameter in request data
     *
     * @return string
     */
    public function getPaymentUrl(): string
    {
        return $this->environmentSettings->getPayment();
    }
}
