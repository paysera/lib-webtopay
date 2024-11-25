<?php

declare(strict_types=1);

/**
 * Representation of routes configurations for WebToPay_Factory
 *
 * @since 3.1.0
 */
class WebToPay_Routes
{
    public const ROUTE_PUBLIC_KEY = 'publicKey';

    public const ROUTE_PAYMENT = 'payment';

    public const ROUTE_PAYMENT_METHOD_LIST = 'paymentMethodList';

    public const ROUTE_SMS_ANSWER = 'smsAnswer';

    protected const ENV_VAR_PUBLIC_KEY = 'PUBLIC_KEY';

    protected const ENV_VAR_PAYMENT = 'PAYMENT';

    protected const ENV_VAR_PAYMENT_METHOD_LIST = 'PAYMENT_METHOD_LIST';

    protected const ENV_VAR_SMS_ANSWER = 'SMS_ANSWER';

    protected const ROUTES_TO_ENV_VARS_MAP = [
        self::ROUTE_PUBLIC_KEY => self::ENV_VAR_PUBLIC_KEY,
        self::ROUTE_PAYMENT => self::ENV_VAR_PAYMENT,
        self::ROUTE_PAYMENT_METHOD_LIST => self::ENV_VAR_PAYMENT_METHOD_LIST,
        self::ROUTE_SMS_ANSWER => self::ENV_VAR_SMS_ANSWER,
    ];

    protected const ENV_VARS_DEFAULTS = [
        self::ROUTE_PUBLIC_KEY => '',
        self::ROUTE_PAYMENT => '',
        self::ROUTE_PAYMENT_METHOD_LIST => '',
        self::ROUTE_SMS_ANSWER => '',
    ];

    protected string $envPrefix = WebToPay_Config::PRODUCTION;

    protected array $defaults = [];

    protected array $customRoutes = [];

    protected string $publicKey;


    protected string $payment;

    protected string $paymentMethodList;

    protected string $smsAnswer;

    private WebToPay_EnvReader $envReader;

    /**
     * @throws Exception
     */
    public function __construct(
        WebToPay_EnvReader $envReader,
        string $envPrefix,
        array $defaults = [],
        array $customRoutes = []
    ) {
        $this->envReader = $envReader;
        $this->envPrefix = $envPrefix;
        $this->defaults = $defaults;
        $this->customRoutes = $customRoutes;

        $this->initConfig();
    }

    public function getPublicKeyRoute(): string
    {
        return $this->publicKey;
    }

    public function getPaymentRoute(): string
    {
        return $this->payment;
    }

    public function getPaymentMethodListRoute(): string
    {
        return $this->paymentMethodList;
    }

    public function getSmsAnswerRoute(): string
    {
        return $this->smsAnswer;
    }

    protected function initConfig(): void
    {
        $envKeyTemplate = strtoupper($this->envPrefix) . '_%s';

        foreach (static::ROUTES_TO_ENV_VARS_MAP as $targetProperty => $varName) {
            $this->initProperty($targetProperty, $varName, $envKeyTemplate);
        }
    }

    protected function initProperty(string $targetProperty, ?string $envName, string $envKeyTemplate): void
    {
        if (!property_exists($this, $targetProperty)) {
            return;
        }

        if ($this->initCustomValue($targetProperty)) {
            return;
        }

        if ($envName === null) {
            return;
        }

        $this->initEnvVar($envName, $targetProperty, $envKeyTemplate);
    }

    protected function initCustomValue(string $targetProperty): bool
    {
        if (isset($this->customRoutes[$targetProperty])) {
            $this->{$targetProperty} = $this->customRoutes[$targetProperty];

            return true;
        }

        return false;
    }

    protected function initEnvVar(string $varName, string $targetProperty, string $envKeyTemplate): void
    {
        $envVar = sprintf($envKeyTemplate, $varName);

        $this->{$targetProperty} = $this->envReader->getAsString(
            $envVar,
            $this->defaults[$targetProperty] ?? static::ENV_VARS_DEFAULTS[$targetProperty]
        );
    }
}
