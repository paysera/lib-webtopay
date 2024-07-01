<?php

declare(strict_types=1);

/**
 * PHP Library for WebToPay provided services.
 * Copyright (C) 2012 http://www.webtopay.com/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    WebToPay
 * @author     EVP International
 * @license    http://www.gnu.org/licenses/lgpl.html
 * @version    3.0.1
 * @link       http://www.webtopay.com/
 */

/**
 * Contains static methods for most used scenarios.
 */
class WebToPay
{
    /**
     * WebToPay Library version.
     */
    public const VERSION = '3.1.0';

    /**
     * Server URL where all requests should go.
     *
     * @deprecated since 3.0.2
     * @see WebToPay_Config::getPayUrl
     */
    public const PAY_URL = 'https://bank.paysera.com/pay/';

    /**
     * Server URL where all non-lithuanian language requests should go.
     *
     * @deprecated since 3.0.2
     * @see WebToPay_Config::getPayseraPayUrl
     */
    public const PAYSERA_PAY_URL = 'https://bank.paysera.com/pay/';

    /**
     * Server URL where we can get XML with payment method data.
     *
     * @deprecated since 3.0.2
     * @see WebToPay_Config::getXmlUrl
     */
    public const XML_URL = 'https://www.paysera.com/new/api/paymentMethods/';

    /**
     * SMS answer url.
     *
     * @deprecated
     */
    public const SMS_ANSWER_URL = 'https://bank.paysera.com/psms/respond/';

    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * Possible keys:
     * https://developers.paysera.com/en/checkout/integrations/integration-specification
     *
     * @param array<string, mixed> $data Information about current payment request
     *
     * @return array<string, mixed>
     *
     * @throws WebToPayException on data validation error
     */
    public static function buildRequest(array $data): array
    {
        self::checkRequiredParameters($data);

        $password = $data['sign_password'];
        $projectId = $data['projectid'];
        unset($data['sign_password']);
        unset($data['projectid']);

        $factory = new WebToPay_Factory(
            [
                WebToPay_Config::PARAM_PROJECT_ID => (int)$projectId,
                WebToPay_Config::PARAM_PASSWORD => $password,
            ]
        );
        $requestBuilder = $factory->getRequestBuilder();

        return $requestBuilder->buildRequest($data);
    }

    /**
     * Builds request and redirects user to payment window with generated request data
     *
     * Possible array keys are described here:
     * https://developers.paysera.com/en/checkout/integrations/integration-specification
     *
     * @param array<string, mixed> $data Information about current payment request.
     * @param boolean $exit if true, exits after sending Location header; default false
     *
     * @throws WebToPayException on data validation error
     */
    public static function redirectToPayment(array $data, bool $exit = false): void
    {
        self::checkRequiredParameters($data);

        $password = $data['sign_password'];
        $projectId = $data['projectid'];
        unset($data['sign_password']);
        unset($data['projectid']);

        $factory = new WebToPay_Factory(
            [
                WebToPay_Config::PARAM_PROJECT_ID => (int)$projectId,
                WebToPay_Config::PARAM_PASSWORD => $password,
            ]
        );
        $url = $factory->getRequestBuilder()
            ->buildRequestUrlFromData($data);

        if (WebToPay_Functions::headers_sent()) {
            echo '<script type="text/javascript">window.location = "' . addslashes($url) . '";</script>';
        } else {
            header("Location: $url", true);
        }
        printf(
            'Redirecting to <a href="%s">%s</a>. Please wait.',
            htmlentities($url, ENT_QUOTES, 'UTF-8'),
            htmlentities($url, ENT_QUOTES, 'UTF-8')
        );
        if ($exit) {
            // @codeCoverageIgnoreStart
            exit();
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Builds repeat request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * Method accepts single parameter $data of array type. All possible array
     * keys are described here:
     * https://developers.paysera.com/en/checkout/integrations/integration-specification
     *
     * @param array<string, mixed> $data Information about current payment request
     *
     * @return array<string, mixed>
     *
     * @throws WebToPayException on data validation error
     */
    public static function buildRepeatRequest(array $data): array
    {
        if (!isset($data['sign_password']) || !isset($data['projectid']) || !isset($data['orderid'])) {
            throw new WebToPayException('sign_password, projectid or orderid is not provided');
        }
        $password = $data['sign_password'];
        $projectId = $data['projectid'];
        $orderId = $data['orderid'];

        $factory = new WebToPay_Factory(
            [
                WebToPay_Config::PARAM_PROJECT_ID => (int)$projectId,
                WebToPay_Config::PARAM_PASSWORD => $password,
            ]
        );
        $requestBuilder = $factory->getRequestBuilder();

        return $requestBuilder->buildRepeatRequest($orderId);
    }

    /**
     * Returns payment url. Argument is same as lang parameter in request data
     *
     * @param string $language
     * @return string $url
     */
    public static function getPaymentUrl(string $language = 'LIT'): string
    {
        $config = new WebToPay_Config();

        return (in_array($language, ['lt', 'lit', 'LIT'], true))
            ? $config->getPayUrl()
            : $config->getPayseraPayUrl();
    }

    /**
     * Parses request (query) data and validates its signature.
     *
     * @param array<string, string> $query usually $_GET
     * @param int|null $projectId
     * @param string|null $password
     *
     * @return array<string, string>
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     * @throws WebToPay_Exception_Configuration
     */
    public static function validateAndParseData(array $query, ?int $projectId, ?string $password): array
    {
        $factory = new WebToPay_Factory(
            [
                WebToPay_Config::PARAM_PROJECT_ID => $projectId,
                WebToPay_Config::PARAM_PASSWORD => $password,
            ]
        );
        $validator = $factory->getCallbackValidator();

        return $validator->validateAndParseData($query);
    }

    /**
     * Sends SMS answer
     *
     * @param array<string, mixed> $userData
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Validation
     *
     * @deprecated
     * @codeCoverageIgnore
     */
    public static function smsAnswer(array $userData): void
    {
        if (!isset($userData['id']) || !isset($userData['msg']) || !isset($userData['sign_password'])) {
            throw new WebToPay_Exception_Validation('id, msg and sign_password are required');
        }

        $smsId = $userData['id'];
        $text = $userData['msg'];
        $password = $userData['sign_password'];
        $logFile = $userData['log'] ?? null;

        try {
            $factory = new WebToPay_Factory([WebToPay_Config::PARAM_PASSWORD => $password]);
            $factory->getSmsAnswerSender()->sendAnswer($smsId, $text);

            if ($logFile) {
                self::log('OK', 'SMS ANSWER ' . $smsId . ' ' . $text, $logFile);
            }

        } catch (WebToPayException $e) {
            if ($logFile) {
                self::log('ERR', 'SMS ANSWER ' . $e, $logFile);
            }
            throw $e;
        }
    }

    /**
     * Gets available payment methods for project. Gets methods min and max amounts in specified currency.
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public static function getPaymentMethodList(
        int $projectId,
        ?float $amount,
        ?string $currency = 'EUR'
    ): WebToPay_PaymentMethodList {
        $factory = new WebToPay_Factory([WebToPay_Config::PARAM_PROJECT_ID => $projectId]);

        return $factory->getPaymentMethodListProvider()->getPaymentMethodList($amount, $currency);
    }

    /**
     * Logs to file. Just skips logging if file is not writeable
     *
     * @deprecated
     * @codeCoverageIgnore
     */
    protected static function log(string $type, string $msg, string $logfile): void
    {
        $fp = @fopen($logfile, 'a');
        if (!$fp) {
            return;
        }

        $logline = [
            $type,
            $_SERVER['REMOTE_ADDR'] ?? '-',
            date('[Y-m-d H:i:s O]'),
            'v' . self::VERSION . ':',
            $msg,
        ];

        $logline = implode(' ', $logline) . "\n";
        fwrite($fp, $logline);
        fclose($fp);

        // clear big log file
        if (filesize($logfile) > 1024 * 1024 * pi()) {
            copy($logfile, $logfile . '.old');
            unlink($logfile);
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws WebToPayException
     */
    protected static function checkRequiredParameters(array $data): void
    {
        if (!isset($data['sign_password']) || !isset($data['projectid'])) {
            throw new WebToPayException('sign_password or projectid is not provided');
        }
    }
}
