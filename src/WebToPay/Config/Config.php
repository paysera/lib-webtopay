<?php

declare(strict_types=1);

/**
 * Initializes configurations for WebToPay and WebToPay_Factory
 *
 * @since 3.0.2
 */
class WebToPay_Config
{
    public const PRODUCTION = 'production';

    public const SANDBOX = 'sandbox';

    public const PARAM_PROJECT_ID = 'projectId';

    public const PARAM_PASSWORD = 'password';

    public const PARAM_PAY_URL = 'payUrl';

    public const PARAM_PAYSERA_PAY_URL = 'payseraPayUrl';

    public const PARAM_XML_URL = 'xmlUrl';

    public const PARAM_ROUTES = 'routes';

    protected const ENV_VAR_PAY_URL = 'PAY_URL';

    protected const ENV_VAR_PAYSERA_PAY_URL = 'PAYSERA_PAY_URL';

    protected const ENV_VAR_XML_URL = 'XML_URL';

    protected const PARAMS_TO_ENV_VARS_MAP = [
        self::PARAM_PROJECT_ID => null,
        self::PARAM_PASSWORD => null,
        self::PARAM_PAY_URL => self::ENV_VAR_PAY_URL,
        self::PARAM_PAYSERA_PAY_URL => self::ENV_VAR_PAYSERA_PAY_URL,
        self::PARAM_XML_URL => self::ENV_VAR_XML_URL,
    ];

    protected const DEFAULT_VALUES = [
        self::PARAM_PROJECT_ID => null,
        self::PARAM_PASSWORD => null,
        self::PARAM_PAY_URL => 'https://bank.paysera.com/pay/',
        self::PARAM_PAYSERA_PAY_URL => 'https://bank.paysera.com/pay/',
        self::PARAM_XML_URL => 'https://www.paysera.com/new/api/paymentMethods/',
    ];

    protected const DEFAULT_ROUTES = [
        self::PRODUCTION => [
            WebToPay_Routes::ROUTE_PUBLIC_KEY => 'https://www.paysera.com/download/public.key',
            WebToPay_Routes::ROUTE_PAYMENT => 'https://bank.paysera.com/pay/',
            WebToPay_Routes::ROUTE_PAYMENT_METHOD_LIST => 'https://www.paysera.com/new/api/paymentMethods/',
            WebToPay_Routes::ROUTE_SMS_ANSWER => 'https://bank.paysera.com/psms/respond/',
        ],
        self::SANDBOX => [
            WebToPay_Routes::ROUTE_PUBLIC_KEY => 'https://sandbox.paysera.com/download/public.key',
            WebToPay_Routes::ROUTE_PAYMENT => 'https://sandbox.paysera.com/pay/',
            WebToPay_Routes::ROUTE_PAYMENT_METHOD_LIST => 'https://sandbox.paysera.com/new/api/paymentMethods/',
            WebToPay_Routes::ROUTE_SMS_ANSWER => 'https://sandbox.paysera.com/psms/respond/',
        ],
    ];

    protected array $customParams = [];

    protected string $environment = self::PRODUCTION;

    protected ?int $projectId = null;

    protected ?string $password = null;

    /**
     * Server URL where all requests should go.
     */
    protected string $payUrl;

    /**
     * Server URL where all non-lithuanian language requests should go.
     */
    protected string $payseraPayUrl;

    /**
     * Server URL where we can get XML with payment method data.
     */
    protected string $xmlUrl;

    protected WebToPay_Routes $routes;

    public function __construct(
        string $environment = self::PRODUCTION,
        array  $customParams = []
    ) {
        $this->environment = $environment;
        $this->customParams = $customParams;

        $this->initConfig();
    }

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPayUrl(): string
    {
        return $this->payUrl;
    }

    public function getPayseraPayUrl(): string
    {
        return $this->payseraPayUrl;
    }

    public function getXmlUrl(): string
    {
        return $this->xmlUrl;
    }

    public function getRoutes(): WebToPay_Routes
    {
        return $this->routes;
    }

    public function switchEnvironment(string $environment): void
    {
        $this->environment = $environment;
        $this->initRoutes();
    }

    protected function initConfig(): void
    {
        foreach (self::PARAMS_TO_ENV_VARS_MAP as $targetProperty => $envName) {
            $this->initProperty($targetProperty, $envName);
        }

        $this->initRoutes();
    }

    protected function initRoutes(): void
    {
        $this->routes = new WebToPay_Routes(
            $this->environment,
            static::DEFAULT_ROUTES[$this->environment] ?? [],
            $this->customParams[static::PARAM_ROUTES] ?? []
        );
    }

    protected function initProperty(string $targetProperty, ?string $envName): void
    {
        if (!property_exists($this, $targetProperty)) {
            return;
        }

        if ($this->initCustomVar($targetProperty)) {
            return;
        }

        if ($envName === null) {
            return;
        }

        $this->initEnvVar($envName, $targetProperty);
    }

    protected function initCustomVar($targetProperty): bool
    {
        if (!empty($this->customParams[$targetProperty])) {
            $this->{$targetProperty} = $this->customParams[$targetProperty];

            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function initEnvVar(string $varName, string $targetProperty): void
    {
        $envValue = getenv($varName);

        if (empty($envValue)) {
            $envValue = static::DEFAULT_VALUES[$targetProperty];
        }

        $this->{$targetProperty} = $envValue;
    }
}
