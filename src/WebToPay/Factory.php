<?php

/**
 * Creates objects. Also caches to avoid creating several instances of same objects
 */
class WebToPay_Factory
{
    const ENV_PRODUCTION = 'production';
    const ENV_SANDBOX = 'sandbox';

    /**
     * @var array<string, mixed>
     */
    protected static array $defaultConfiguration = [
        'routes' => [
            self::ENV_PRODUCTION => [
                'publicKey'           => 'https://www.paysera.com/download/public.key',
                'payment'             => 'https://bank.paysera.com/pay/',
                'paymentMethodList'   => 'https://www.paysera.com/new/api/paymentMethods/',
                'smsAnswer'           => 'https://bank.paysera.com/psms/respond/',
            ],
            self::ENV_SANDBOX => [
                'publicKey'         => 'https://sandbox.paysera.com/download/public.key',
                'payment'           => 'https://sandbox.paysera.com/pay/',
                'paymentMethodList' => 'https://sandbox.paysera.com/new/api/paymentMethods/',
                'smsAnswer'         => 'https://sandbox.paysera.com/psms/respond/',
            ],
        ],
    ];

    protected string $environment;

    /**
     * @var array<string, mixed>
     */
    protected array $configuration;

    protected ?WebToPay_WebClient $webClient = null;

    protected ?WebToPay_CallbackValidator $callbackValidator = null;

    protected ?WebToPay_RequestBuilder $requestBuilder = null;

    protected ?WebToPay_Sign_SignCheckerInterface $signer = null;

    protected ?WebToPay_SmsAnswerSender $smsAnswerSender = null;

    protected ?WebToPay_PaymentMethodListProvider $paymentMethodListProvider = null;

    protected ?WebToPay_Util $util = null;

    protected ?WebToPay_UrlBuilder $urlBuilder = null;

    /**
     * Constructs object.
     * Configuration keys: projectId, password
     * They are required only when some object being created needs them,
     *     if they are not found at that moment - exception is thrown
     *
     * @param array<string, mixed> $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = array_merge(self::$defaultConfiguration, $configuration);
        $this->environment = self::ENV_PRODUCTION;
    }

    /**
     * If passed true the factory will use sandbox when constructing URLs
     */
    public function useSandbox(bool $enableSandbox): self
    {
        if ($enableSandbox) {
            $this->environment = self::ENV_SANDBOX;
        } else {
            $this->environment = self::ENV_PRODUCTION;
        }

        return $this;
    }

    /**
     * Creates or gets callback validator instance
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function getCallbackValidator(): WebToPay_CallbackValidator
    {
        if ($this->callbackValidator === null) {
            if (!isset($this->configuration['projectId'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }

            $this->callbackValidator = new WebToPay_CallbackValidator(
                (int) $this->configuration['projectId'],
                $this->getSigner(),
                $this->getUtil(),
                $this->configuration['password'] ?? null
            );
        }

        return $this->callbackValidator;
    }

    /**
     * Creates or gets request builder instance
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function getRequestBuilder(): WebToPay_RequestBuilder
    {
        if ($this->requestBuilder === null) {
            if (!isset($this->configuration['password'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project password to sign request');
            }
            if (!isset($this->configuration['projectId'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }
            $this->requestBuilder = new WebToPay_RequestBuilder(
                $this->configuration['projectId'],
                $this->configuration['password'],
                $this->getUtil(),
                $this->getUrlBuilder()
            );
        }

        return $this->requestBuilder;
    }

    public function getUrlBuilder(): WebToPay_UrlBuilder
    {
        if ($this->urlBuilder === null) {
            $this->urlBuilder = new WebToPay_UrlBuilder(
                $this->configuration,
                $this->environment
            );
        }

        return $this->urlBuilder;
    }

    /**
     * Creates or gets SMS answer sender instance
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function getSmsAnswerSender(): WebToPay_SmsAnswerSender
    {
        if ($this->smsAnswerSender === null) {
            if (!isset($this->configuration['password'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project password');
            }
            $this->smsAnswerSender = new WebToPay_SmsAnswerSender(
                $this->configuration['password'],
                $this->getWebClient(),
                $this->getUrlBuilder()
            );
        }

        return $this->smsAnswerSender;
    }

    /**
     * Creates or gets payment list provider instance
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function getPaymentMethodListProvider(): WebToPay_PaymentMethodListProvider
    {
        if ($this->paymentMethodListProvider === null) {
            if (!isset($this->configuration['projectId'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }
            $this->paymentMethodListProvider = new WebToPay_PaymentMethodListProvider(
                $this->configuration['projectId'],
                $this->getWebClient(),
                $this->getUrlBuilder()
            );
        }

        return $this->paymentMethodListProvider;
    }

    /**
     * Creates or gets signer instance. Chooses SS2 signer if openssl functions are available, SS1 in other case
     *
     * @throws WebToPay_Exception_Configuration
     * @throws WebToPayException
     */
    protected function getSigner(): WebToPay_Sign_SignCheckerInterface
    {
        if ($this->signer === null) {
            if (function_exists('openssl_pkey_get_public')) {
                $webClient = $this->getWebClient();
                $publicKey = $webClient->get($this->getUrlBuilder()->buildForPublicKey());
                if (!$publicKey) {
                    throw new WebToPayException('Cannot download public key from WebToPay website');
                }
                $this->signer = new WebToPay_Sign_SS2SignChecker($publicKey, $this->getUtil());
            } else {
                if (!isset($this->configuration['password'])) {
                    throw new WebToPay_Exception_Configuration(
                        'You have to provide project password if OpenSSL is unavailable'
                    );
                }
                $this->signer = new WebToPay_Sign_SS1SignChecker($this->configuration['password']);
            }
        }

        return $this->signer;
    }

    /**
     * Creates or gets web client instance
     *
     * @throws WebToPay_Exception_Configuration
     */
    protected function getWebClient(): WebToPay_WebClient
    {
        if ($this->webClient === null) {
            $this->webClient = new WebToPay_WebClient();
        }

        return $this->webClient;
    }

    /**
     * Creates or gets util instance
     *
     * @throws WebToPay_Exception_Configuration
     */
    protected function getUtil():WebToPay_Util
    {
        if ($this->util === null) {
            $this->util = new WebToPay_Util();
        }

        return $this->util;
    }
}
