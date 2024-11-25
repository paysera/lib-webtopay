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
        $envReader = new WebToPay_EnvReader();
        if ($envFilePath !== null) {
            $dotenv = new Dotenv();
            $dotenv->usePutenv();
            $dotenv->load($envFilePath);
        }

        $routesConfig = new WebToPay_Routes(
            $envReader,
            $env,
            $defaults,
            $customRoutes,
        );

        foreach ($expected as $method => $expectedReturnValue) {
            $this->assertEquals($expectedReturnValue, $routesConfig->{$method}());
        }
    }

    public function routesDataProvider(): iterable
    {
        $envFile = null;
        $expected = [
            'getPublicKeyRoute' => 'https://public-key-test.paysera.net/',
            'getPaymentRoute' => 'https://payment.paysera.net/',
            'getPaymentMethodListRoute' => 'https://payment-method-list.paysera.net/',
            'getSmsAnswerRoute' => 'https://sms-answer.paysera.net/',
        ];

        $env = 'test';
        $defaults = [
            'publicKey' => $expected['getPublicKeyRoute'],
            'payment' => $expected['getPaymentRoute'],
            'paymentMethodList' => $expected['getPaymentMethodListRoute'],
            'smsAnswer' => $expected['getSmsAnswerRoute'],
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
            'getPublicKeyRoute' => 'https://custom-public-key-test.paysera.net/',
            'getPaymentRoute' => 'https://custom-payment.paysera.net/',
            'getPaymentMethodListRoute' => 'https://custom-payment-method-list.paysera.net/',
            'getSmsAnswerRoute' => 'https://custom-sms-answer.paysera.net/',
        ];

        $customRoutes = [
            'publicKey' => $expected['getPublicKeyRoute'],
            'payment' => $expected['getPaymentRoute'],
            'paymentMethodList' => $expected['getPaymentMethodListRoute'],
            'smsAnswer' => $expected['getSmsAnswerRoute'],
        ];

        yield 'only custom vars' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected = [
            'getPublicKeyRoute' => 'https://public-key-test.paysera.net/',
            'getPaymentRoute' => 'https://payment.paysera.net/',
            'getPaymentMethodListRoute' => 'https://payment-method-list.paysera.net/',
            'getSmsAnswerRoute' => 'https://sms-answer.paysera.net/',
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
            'getPublicKeyRoute' => 'https://custom-public-key-test.paysera.net/',
            'getPaymentRoute' => 'https://custom-payment.paysera.net/',
            'getPaymentMethodListRoute' => 'https://custom-payment-method-list.paysera.net/',
            'getSmsAnswerRoute' => 'https://custom-sms-answer.paysera.net/',
        ];

        $customRoutes = [
            'publicKey' => $expected['getPublicKeyRoute'],
            'payment' => $expected['getPaymentRoute'],
            'paymentMethodList' => $expected['getPaymentMethodListRoute'],
            'smsAnswer' => $expected['getSmsAnswerRoute'],
        ];

        yield 'customs on isset env vars' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected = [
            'getPublicKeyRoute' => 'https://public-key-test.paysera.net/',
            'getPaymentRoute' => 'https://payment.paysera.net/',
            'getPaymentMethodListRoute' => 'https://default-payment-method-list.paysera.net/',
            'getSmsAnswerRoute' => 'https://default-sms-answer.paysera.net/',
        ];
        $customRoutes = [];

        $defaults = [
            'paymentMethodList' => $expected['getPaymentMethodListRoute'],
            'smsAnswer' => $expected['getSmsAnswerRoute'],
        ];

        $envFile = dirname(__FILE__) . '/.non-full-routes-test.env';

        yield 'default on env var is not set' => [
            $env,
            $envFile,
            $defaults,
            $customRoutes,
            $expected,
        ];

        $expected['getSmsAnswerRoute'] = 'https://custom-sms-answer.paysera.net/';
        $customRoutes = [
            'smsAnswer' => $expected['getSmsAnswerRoute'],
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
