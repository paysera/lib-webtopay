<?php

declare(strict_types=1);

/**
 * Representation of routes configurations for WebToPay_Factory
 *
 * @since 3.0.2
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

    protected const ENV_VALS_DEFAULTS = [
        self::ROUTE_PUBLIC_KEY => '',
        self::ROUTE_PAYMENT => '',
        self::ROUTE_PAYMENT_METHOD_LIST => '',
        self::ROUTE_SMS_ANSWER => '',
    ];

    protected ?string $envPrefix = null;

    protected array $defaults = [];

    protected array $customRoutes = [];

    protected string $publicKey;

    protected string $payment;

    protected string $paymentMethodList;

    protected string $smsAnswer;

    /**
     * @throws Exception
     */
    public function __construct(string $envPrefix, array $defaults = [], array $customRoutes = [])
    {
        $this->envPrefix = $envPrefix;
        $this->defaults = $defaults;
        $this->customRoutes = $customRoutes;

        $this->initConfig();
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPayment(): string
    {
        return $this->payment;
    }

    public function getPaymentMethodList(): string
    {
        return $this->paymentMethodList;
    }

    public function getSmsAnswer(): string
    {
        return $this->smsAnswer;
    }

    /**
     * @throws Exception
     */
    protected function initConfig(): void
    {
        if ($this->envPrefix === null) {
            throw new WebToPay_Exception_Configuration('Environment must be set');
        }

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
        $envValue = getenv($envVar);

        if (empty($envValue)) {
            $envValue = $this->defaults[$targetProperty] ?? static::ENV_VALS_DEFAULTS[$targetProperty];
        }

        $this->{$targetProperty} = $envValue;
    }
}
