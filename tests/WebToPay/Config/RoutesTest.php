<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class WebToPay_RoutesTest extends TestCase
{
    /**
     * @param string $env
     * @param string|null $envFilePath
     * @param array $defaults
     * @param array $customRoutes
     * @param array $expected
     * @return void
     * @throws Exception
     * @dataProvider routesDataProvider
     */
    public function testRoutes(
        string  $env,
        ?string $envFilePath,
        array   $defaults,
        array   $customRoutes,
        array   $expected
    ): void {
        if ($envFilePath !== null) {
            $dotenv = new Dotenv();
            $dotenv->usePutenv();
            $dotenv->load($envFilePath);
        }

        $routesConfig = new WebToPay_Routes(
            $env,
            $defaults,
            $customRoutes
        );

        foreach ($expected as $method => $expectedReturnValue) {
            $this->assertEquals($expectedReturnValue, $routesConfig->{$method}());
        }
    }

    public function routesDataProvider(): iterable
    {
        $envFile = null;
        $expected = [
            'getPublicKey' => 'https://public-key-test.paysera.net/',
            'getPayment' => 'https://payment.paysera.net/',
            'getPaymentMethodList' => 'https://payment-method-list.paysera.net/',
            'getSmsAnswer' => 'https://sms-answer.paysera.net/',
        ];

        $env = 'test';
        $defaults = [
            'publicKey' => $expected['getPublicKey'],
            'payment' => $expected['getPayment'],
            'paymentMethodList' => $expected['getPaymentMethodList'],
            'smsAnswer' => $expected['getSmsAnswer'],
        ];
        $customRoutes = [];

        yield 'only default vars' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected = [
            'getPublicKey' => 'https://custom-public-key-test.paysera.net/',
            'getPayment' => 'https://custom-payment.paysera.net/',
            'getPaymentMethodList' => 'https://custom-payment-method-list.paysera.net/',
            'getSmsAnswer' => 'https://custom-sms-answer.paysera.net/',
        ];

        $customRoutes = [
            'publicKey' => $expected['getPublicKey'],
            'payment' => $expected['getPayment'],
            'paymentMethodList' => $expected['getPaymentMethodList'],
            'smsAnswer' => $expected['getSmsAnswer'],
        ];

        yield 'only custom vars' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected = [
            'getPublicKey' => 'https://public-key-test.paysera.net/',
            'getPayment' => 'https://payment.paysera.net/',
            'getPaymentMethodList' => 'https://payment-method-list.paysera.net/',
            'getSmsAnswer' => 'https://sms-answer.paysera.net/',
        ];
        $customRoutes = [];

        $envFile = dirname(__FILE__) . '/.routes-test.env';

        yield 'only env vars' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected = [
            'getPublicKey' => 'https://custom-public-key-test.paysera.net/',
            'getPayment' => 'https://custom-payment.paysera.net/',
            'getPaymentMethodList' => 'https://custom-payment-method-list.paysera.net/',
            'getSmsAnswer' => 'https://custom-sms-answer.paysera.net/',
        ];

        $customRoutes = [
            'publicKey' => $expected['getPublicKey'],
            'payment' => $expected['getPayment'],
            'paymentMethodList' => $expected['getPaymentMethodList'],
            'smsAnswer' => $expected['getSmsAnswer'],
        ];

        yield 'customs on isset env vars' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected = [
            'getPublicKey' => 'https://public-key-test.paysera.net/',
            'getPayment' => 'https://payment.paysera.net/',
            'getPaymentMethodList' => 'https://default-payment-method-list.paysera.net/',
            'getSmsAnswer' => 'https://default-sms-answer.paysera.net/',
        ];
        $customRoutes = [];

        $defaults = [
            'paymentMethodList' => $expected['getPaymentMethodList'],
            'smsAnswer' => $expected['getSmsAnswer'],
        ];

        $envFile = dirname(__FILE__) . '/.non-full-routes-test.env';

        yield 'default on env var is not set' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected['getSmsAnswer'] = 'https://custom-sms-answer.paysera.net/';
        $customRoutes = [
            'smsAnswer' => $expected['getSmsAnswer'],
        ];

        yield 'customs, env and defaults' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];
    }
}
