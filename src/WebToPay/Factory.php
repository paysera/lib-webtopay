<?php

/**
 * Creates objects. Also caches to avoid creating several instances of same objects
 */
class WebToPay_Factory {

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var WebToPay_WebClient
     */
    protected $webClient = null;

    /**
     * @var WebToPay_CallbackValidator
     */
    protected $callbackValidator = null;

    /**
     * @var WebToPay_RequestBuilder
     */
    protected $requestBuilder = null;

    /**
     * @var WebToPay_Sign_SignCheckerInterface
     */
    protected $signer = null;

    /**
     * @var WebToPay_SmsAnswerSender
     */
    protected $smsAnswerSender = null;

    /**
     * @var WebToPay_PaymentMethodListProvider
     */
    protected $paymentMethodListProvider = null;

    /**
     * @var WebToPay_Util
     */
    protected $util = null;

    /**
     * Constructs object.
     * Configuration keys: projectId, password
     * They are required only when some object being created needs them,
     *     if they are not found at that moment - exception is thrown
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = array()) {
        $this->configuration = $configuration;
    }

    /**
     * Creates or gets callback validator instance
     *
     * @return WebToPay_CallbackValidator
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function getCallbackValidator() {
        if ($this->callbackValidator === null) {
            if (!isset($this->configuration['projectId'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }
            $this->callbackValidator = new WebToPay_CallbackValidator(
                $this->configuration['projectId'],
                $this->getSigner(),
                $this->getUtil()
            );
        }
        return $this->callbackValidator;
    }

    /**
     * Creates or gets request builder instance
     *
     * @throws WebToPay_Exception_Configuration
     *
     * @return WebToPay_RequestBuilder
     */
    public function getRequestBuilder() {
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
                $this->getUtil()
            );
        }
        return $this->requestBuilder;
    }

    /**
     * Creates or gets SMS answer sender instance
     *
     * @throws WebToPay_Exception_Configuration
     *
     * @return WebToPay_SmsAnswerSender
     */
    public function getSmsAnswerSender() {
        if ($this->smsAnswerSender === null) {
            if (!isset($this->configuration['password'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project password');
            }
            $this->smsAnswerSender = new WebToPay_SmsAnswerSender(
                $this->configuration['password'],
                $this->getWebClient()
            );
        }
        return $this->smsAnswerSender;
    }

    /**
     * Creates or gets payment list provider instance
     *
     * @throws WebToPay_Exception_Configuration
     *
     * @return WebToPay_PaymentMethodListProvider
     */
    public function getPaymentMethodListProvider() {
        if ($this->paymentMethodListProvider === null) {
            if (!isset($this->configuration['projectId'])) {
                throw new WebToPay_Exception_Configuration('You have to provide project ID');
            }
            $this->paymentMethodListProvider = new WebToPay_PaymentMethodListProvider(
                $this->configuration['projectId'],
                $this->getWebClient()
            );
        }
        return $this->paymentMethodListProvider;
    }

    /**
     * Creates or gets signer instance. Chooses SS2 signer if openssl functions are available, SS1 in other case
     *
     * @throws WebToPay_Exception_Configuration
     *
     * @return WebToPay_Sign_SignCheckerInterface
     *
     * @throws WebToPayException
     */
    protected function getSigner() {
        if ($this->signer === null) {
            if (function_exists('openssl_pkey_get_public')) {
                $webClient = $this->getWebClient();
                $publicKey = $webClient->get('http://downloads.webtopay.com/download/public.key');
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
     *
     * @return WebToPay_WebClient
     */
    protected function getWebClient() {
        if ($this->webClient === null) {
            $this->webClient = new WebToPay_WebClient();
        }
        return $this->webClient;
    }

    /**
     * Creates or gets util instance
     *
     * @throws WebToPay_Exception_Configuration
     *
     * @return WebToPay_Util
     */
    protected function getUtil() {
        if ($this->util === null) {
            $this->util = new WebToPay_Util();
        }
        return $this->util;
    }
}
