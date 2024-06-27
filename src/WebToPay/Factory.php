<?php

declare(strict_types=1);

/**
 * Creates objects. Also caches to avoid creating several instances of same objects
 */
class WebToPay_Factory
{
    /**
     * @deprecated since 3.0.2
     */
    public const ENV_PRODUCTION = 'production';
    /**
     * @deprecated since 3.0.2
     */
    public const ENV_SANDBOX = 'sandbox';

    /**
     * @var array<string, mixed>
     *
     * @deprecated since 3.0.2
     */
    protected static array $defaultConfiguration = [
        'routes' => [
            self::ENV_PRODUCTION => [
                'publicKey' => 'https://www.paysera.com/download/public.key',
                'payment' => 'https://bank.paysera.com/pay/',
                'paymentMethodList' => 'https://www.paysera.com/new/api/paymentMethods/',
                'smsAnswer' => 'https://bank.paysera.com/psms/respond/',
            ],
            self::ENV_SANDBOX => [
                'publicKey' => 'https://sandbox.paysera.com/download/public.key',
                'payment' => 'https://sandbox.paysera.com/pay/',
                'paymentMethodList' => 'https://sandbox.paysera.com/new/api/paymentMethods/',
                'smsAnswer' => 'https://sandbox.paysera.com/psms/respond/',
            ],
        ],
    ];

    protected string $environment;

    protected WebToPay_Config $configuration;

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
        $this->environment = WebToPay_Config::PRODUCTION;
        $this->configuration = new WebToPay_Config($this->environment, $configuration);
    }

    /**
     * If passed true the factory will use sandbox when constructing URLs
     */
    public function useSandbox(bool $enableSandbox): self
    {
        if ($enableSandbox) {
            $this->environment = WebToPay_Config::SANDBOX;
        } else {
            $this->environment = WebToPay_Config::PRODUCTION;
        }

        $this->configuration->switchEnvironment($this->environment);

        return $this;
    }

    /**
     * Creates or gets callback validator instance
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function getCallbackValidator(): WebToPay_CallbackValidator
    {
        if ($this->callbackValidator === null) {
            if ($this->configuration->getProjectId() === null) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }

            $this->callbackValidator = new WebToPay_CallbackValidator(
                $this->configuration->getProjectId(),
                $this->getSigner(),
                $this->getUtil(),
                $this->configuration->getPassword()
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
            if ($this->configuration->getPassword() === null) {
                throw new WebToPay_Exception_Configuration('You have to provide project password to sign request');
            }
            if ($this->configuration->getProjectId() === null) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }
            $this->requestBuilder = new WebToPay_RequestBuilder(
                $this->configuration->getProjectId(),
                $this->configuration->getPassword(),
                $this->getUtil(),
                $this->getUrlBuilder()
            );
        }

        return $this->requestBuilder;
    }

    public function getUrlBuilder(): WebToPay_UrlBuilder
    {
        if ($this->urlBuilder === null || $this->urlBuilder->getEnvironment() !== $this->environment) {
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
            if ($this->configuration->getPassword() === null) {
                throw new WebToPay_Exception_Configuration('You have to provide project password');
            }
            $this->smsAnswerSender = new WebToPay_SmsAnswerSender(
                $this->configuration->getPassword(),
                $this->getWebClient(),
                $this->getUrlBuilder()
            );
        }

        return $this->smsAnswerSender;
    }

    /**
     * Creates or gets payment list provider instance
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function getPaymentMethodListProvider(): WebToPay_PaymentMethodListProvider
    {
        if ($this->paymentMethodListProvider === null) {
            if ($this->configuration->getProjectId() === null) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }
            $this->paymentMethodListProvider = new WebToPay_PaymentMethodListProvider(
                $this->configuration->getProjectId(),
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
            if (WebToPay_Functions::function_exists('openssl_pkey_get_public')) {
                $webClient = $this->getWebClient();
                $publicKey = $webClient->get($this->getUrlBuilder()->buildForPublicKey());
                if (!$publicKey) {
                    throw new WebToPayException('Cannot download public key from WebToPay website');
                }
                $this->signer = new WebToPay_Sign_SS2SignChecker($publicKey, $this->getUtil());
            } else {
                if ($this->configuration->getPassword() === null) {
                    throw new WebToPay_Exception_Configuration(
                        'You have to provide project password if OpenSSL is unavailable'
                    );
                }
                $this->signer = new WebToPay_Sign_SS1SignChecker($this->configuration->getPassword());
            }
        }

        return $this->signer;
    }

    /**
     * Creates or gets web client instance
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
    protected function getUtil(): WebToPay_Util
    {
        if ($this->util === null) {
            $this->util = new WebToPay_Util();
        }

        return $this->util;
    }
}
