<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class WebToPay_ConfigTest extends TestCase
{
    /**
     * @param string $env
     * @param array $customConfig
     * @param array $expected
     * @return void
     *
     * @dataProvider configDataProvider
     */
    public function testConfig(
        string  $env,
        array   $customConfig,
        ?string $envFilePath,
        array   $expected
    ): void {
        if ($envFilePath !== null) {
            $dotenv = new Dotenv();
            $dotenv->usePutenv();
            $dotenv->load($envFilePath);
        }
        $config = new WebToPay_Config($env, $customConfig);

        $this->assertConfig($expected, $config);
    }

    public function configDataProvider(): iterable
    {
        $expectedDefaultRoutes = [
            'publicKey' => 'https://sandbox.paysera.com/download/public.key',
            'payment' => 'https://sandbox.paysera.com/pay/',
            'paymentMethodList' => 'https://sandbox.paysera.com/new/api/paymentMethods/',
            'smsAnswer' => 'https://sandbox.paysera.com/psms/respond/',
        ];
        $env = 'sandbox';
        $routes = new WebToPay_Routes($env, $expectedDefaultRoutes);
        $customConfig = [];
        $expectedConfig = [
            'getProjectId' => null,
            'getPassword' => null,
            'getPayUrl' => 'https://bank.paysera.com/pay/',
            'getPayseraPayUrl' => 'https://bank.paysera.com/pay/',
            'getXmlUrl' => 'https://www.paysera.com/new/api/paymentMethods/',
            'getRoutes' => $routes,
        ];

        yield 'only default' => [
            $env,
            $customConfig,
            null,
            $expectedConfig,
        ];

        $envFile = dirname(__FILE__) . '/.base.env';

        $routes = new WebToPay_Routes($env, $expectedDefaultRoutes);

        Closure::bind(
            function () use ($env, $customConfig) {
                $this->publicKey = 'https://test.paysera.net/sandbox/public_key/';
                $this->payment = 'https://test.paysera.net/sandbox/payment/';
                $this->paymentMethodList = 'https://test.paysera.net/sandbox/paument_method_list/';
                $this->smsAnswer = 'https://test.paysera.net/sandbox/sms_answer/';
            },
            $routes,
            $routes
        )();

        $expectedConfig = [
            'getProjectId' => null,
            'getPassword' => null,
            'getPayUrl' => 'https://test.paysera.net/pay/',
            'getPayseraPayUrl' => 'https://test.paysera.net/paysera_pay/',
            'getXmlUrl' => 'https://test.paysera.net/xml/',
            'getRoutes' => $routes,
        ];

        yield 'base' => [
            $env,
            $customConfig,
            $envFile,
            $expectedConfig,
        ];

        $envFile = dirname(__FILE__) . '/.non-full.env';

        $routes = new WebToPay_Routes($env, $expectedDefaultRoutes);

        Closure::bind(
            function () use ($env, $customConfig) {
                $this->publicKey = 'https://test.paysera.net/sandbox/public_key/';
                $this->payment = 'https://test.paysera.net/sandbox/payment/';
            },
            $routes,
            $routes
        )();

        $expectedConfig = [
            'getProjectId' => null,
            'getPassword' => null,
            'getPayUrl' => 'https://bank.paysera.com/pay/',
            'getPayseraPayUrl' => 'https://test.paysera.net/paysera_pay/',
            'getXmlUrl' => 'https://test.paysera.net/xml/',
            'getRoutes' => $routes,
        ];

        yield 'non-full env' => [
            $env,
            $customConfig,
            $envFile,
            $expectedConfig,
        ];

        $customConfig = [
            'projectId' => 12347,
            'password' => 'test_password',
            'payUrl' => 'https://custom.paysera.net/pay/',
            'payseraPayUrl' => 'https://custom.paysera.net/paysera_pay/',
            'xmlUrl' => 'https://custom.paysera.net/xml/',
            'routes' => [
                'publicKey' => 'https://custom.paysera.net/sandbox/public_key/',
                'payment' => 'https://custom.paysera.net/sandbox/payment/',
                'paymentMethodList' => 'https://custom.paysera.net/sandbox/paument_method_list/',
                'smsAnswer' => 'https://custom.paysera.net/sandbox/sms_answer/',
            ],
        ];

        $expectedConfig = [
            'getProjectId' => $customConfig['projectId'],
            'getPassword' => $customConfig['password'],
            'getPayUrl' => $customConfig['payUrl'],
            'getPayseraPayUrl' => $customConfig['payseraPayUrl'],
            'getXmlUrl' => $customConfig['xmlUrl'],
            'getRoutes' => new WebToPay_Routes(
                $env,
                $expectedDefaultRoutes,
                $customConfig['routes'],
            ),
        ];

        yield 'custom config' => [
            $env,
            $customConfig,
            $envFile,
            $expectedConfig,
        ];
    }

    public function testSwitchEnvironment(): void
    {
        $dotenv = new Dotenv();
        $dotenv->usePutenv();
        $dotenv->load(dirname(__FILE__) . '/.switch-envs.env');

        $env = 'production';
        $config = new WebToPay_Config($env);

        $expected = [
            'getProjectId' => null,
            'getPassword' => null,
            'getPayUrl' => 'https://test.paysera.net/pay/',
            'getPayseraPayUrl' => 'https://test.paysera.net/paysera_pay/',
            'getXmlUrl' => 'https://test.paysera.net/xml/',
            'getRoutes' => new WebToPay_Routes(
                $env,
                [
                    'publicKey' => 'https://www.paysera.com/download/public.key',
                    'payment' => 'https://bank.paysera.com/pay/',
                    'paymentMethodList' => 'https://www.paysera.com/new/api/paymentMethods/',
                    'smsAnswer' => 'https://bank.paysera.com/psms/respond/',
                ],
            ),
        ];

        $this->assertConfig($expected, $config);

        $env = 'sandbox';
        $config->switchEnvironment($env);

        $expected = [
            'getProjectId' => null,
            'getPassword' => null,
            'getPayUrl' => 'https://test.paysera.net/pay/',
            'getPayseraPayUrl' => 'https://test.paysera.net/paysera_pay/',
            'getXmlUrl' => 'https://test.paysera.net/xml/',
            'getRoutes' => new WebToPay_Routes(
                $env,
                [
                    'publicKey' => 'https://sandbox.paysera.com/download/public.key',
                    'payment' => 'https://sandbox.paysera.com/pay/',
                    'paymentMethodList' => 'https://sandbox.paysera.com/new/api/paymentMethods/',
                    'smsAnswer' => 'https://sandbox.paysera.com/psms/respond/',
                ],
            ),
        ];

        $this->assertConfig($expected, $config);
    }

    private function assertConfig(array $expectedConfig, WebToPay_Config $config): void
    {
        foreach ($expectedConfig as $method => $expectedReturnValue) {
            $this->assertEquals($expectedReturnValue, $config->{$method}());
        }
    }
}
