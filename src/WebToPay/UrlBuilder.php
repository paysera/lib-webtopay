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

    /**
     * @var array<string, mixed>
     */
    protected array $configuration;

    protected string $environment;

    /**
     * @var array<string, string>
     */
    protected array $environmentSettings;

    /**
     * @param array<string, mixed> $configuration
     * @param string $environment
     */
    public function __construct(array $configuration, string $environment)
    {
        $this->configuration = $configuration;
        $this->environment = $environment;
        $this->environmentSettings = $this->configuration['routes'][$this->environment];
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
    public function buildForPaymentsMethodList(int $projectId, string $amount, string $currency): string
    {
        $route = $this->environmentSettings['paymentMethodList'];

        return $route . $projectId . '/currency:' . $currency . '/amount:' . $amount;
    }

    /**
     * Builds a complete URL for Sms Answer
     */
    public function buildForSmsAnswer(): string
    {
        return $this->environmentSettings['smsAnswer'];
    }

    /**
     * Build the URL to the public key
     */
    public function buildForPublicKey(): string
    {
        return $this->environmentSettings['publicKey'];
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
        return $this->environmentSettings['payment'];
    }
}
