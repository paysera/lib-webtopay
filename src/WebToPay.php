<?php
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
 * @version    1.6
 * @link       http://www.webtopay.com/
 */


/**
 * Contains static methods for most used scenarios.
 */
class WebToPay {

    /**
     * WebToPay Library version.
     */
    const VERSION = '1.6';

    /**
     * Server URL where all requests should go.
     */
    const PAY_URL = 'https://www.mokejimai.lt/pay/';

    /**
     * Server URL where we can get XML with payment method data.
     */
    const XML_URL = 'https://www.mokejimai.lt/new/api/paymentMethods/';

    /**
     * SMS answer url.
     */
    const SMS_ANSWER_URL = 'https://www.mokejimai.lt/psms/respond/';

    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * Possible keys:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * @param  array $data Information about current payment request
     *
     * @return array
     *
     * @throws WebToPayException on data validation error
     */
    public static function buildRequest($data) {
        if (!isset($data['sign_password']) || !isset($data['projectid'])) {
            throw new WebToPayException('sign_password or projectid is not provided');
        }
        $password = $data['sign_password'];
        $projectId = $data['projectid'];
        unset($data['sign_password']);
        unset($data['projectid']);

        $factory = new WebToPay_Factory(array('projectId' => $projectId, 'password' => $password));
        $requestBuilder = $factory->getRequestBuilder();
        return $requestBuilder->buildRequest($data);
    }


    /**
     * Builds request and redirects user to payment window with generated request data
     *
     * Possible array keys are described here:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * @param  array   $data Information about current payment request.
     * @param  boolean $exit if true, exits after sending Location header; default false
     *
     * @throws WebToPayException on data validation error
     */
    public static function redirectToPayment($data, $exit = false) {
        $request = self::buildRequest($data);
        $url = self::PAY_URL . '?' . http_build_query($request);
        $url = preg_replace('/[\r\n]+/is', '', $url);
        if (headers_sent()) {
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
            exit();
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
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * @param  array $data Information about current payment request
     *
     * @return array
     *
     * @throws WebToPayException on data validation error
     */
    public static function buildRepeatRequest($data) {
        if (!isset($data['sign_password']) || !isset($data['projectid']) || !isset($data['orderid'])) {
            throw new WebToPayException('sign_password, projectid or orderid is not provided');
        }
        $password = $data['sign_password'];
        $projectId = $data['projectid'];
        $orderId = $data['orderid'];

        $factory = new WebToPay_Factory(array('projectId' => $projectId, 'password' => $password));
        $requestBuilder = $factory->getRequestBuilder();
        return $requestBuilder->buildRepeatRequest($orderId);
    }

    /**
     * Returns payment url. Argument is same as lang parameter in request data
     *
     * @param  string $language
     * @return string $url
     */
    public static function getPaymentUrl($language = 'LIT') {
        $url = self::PAY_URL;
        if ($language != 'LIT') {
            $url = str_replace('mokejimai.lt', 'webtopay.com', $url);
        }
        return $url;
    }

    /**
     * Parses response from WebToPay server and validates signs.
     *
     * This function accepts both micro and macro responses.
     *
     * First parameter usualy should be $_GET array.
     *
     * Description about response can be found here:
     * makro: https://www.mokejimai.lt/makro_specifikacija.html
     * mikro: https://www.mokejimai.lt/mikro_mokejimu_specifikacija_SMS.html
     *
     * If response is not correct, WebToPayException will be raised.
     *
     * @param array $query    Response array
     * @param array $userData
     *
     * @return array
     *
     * @throws WebToPayException
     * @deprecated use validateAndParseData() and check status code yourself
     */
    public static function checkResponse($query, $userData = array()) {
        $projectId = isset($userData['projectid']) ? $userData['projectid'] : null;
        $password = isset($userData['sign_password']) ? $userData['sign_password'] : null;
        $logFile = isset($userData['log']) ? $userData['log'] : null;

        try {
            $data = self::validateAndParseData($query, $projectId, $password);
            if ($data['type'] == 'macro' && $data['status'] != 1) {
                throw new WebToPayException('Expected status code 1', WebToPayException::E_DEPRECATED_USAGE);
            }

            if ($logFile) {
                self::log('OK', http_build_query($data), $logFile);
            }
            return $data;

        } catch (WebToPayException $exception) {
        	if ($logFile && $exception->getCode() != WebToPayException::E_DEPRECATED_USAGE) {
                self::log('ERR', $exception . "\nQuery: " . http_build_query($query), $logFile);
            }
            throw $exception;
        }
    }

    /**
     * Parses request (query) data and validates its signature.
     *
     * @param array   $query        usually $_GET
     * @param integer $projectId
     * @param string  $password
     *
     * @return array
     *
     * @throws WebToPayException
     */
    public static function validateAndParseData(array $query, $projectId, $password) {
        $factory = new WebToPay_Factory(array('projectId' => $projectId, 'password' => $password));
        $validator = $factory->getCallbackValidator();
        $data = $validator->validateAndParseData($query);
        return $data;
    }

    /**
     * Sends SMS answer
     *
     * @param array $userData
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Validation
     */
    public static function smsAnswer($userData) {
        if (!isset($userData['id']) || !isset($userData['msg']) || !isset($userData['sign_password'])) {
            throw new WebToPay_Exception_Validation('id, msg and sign_password are required');
        }

        $smsId = $userData['id'];
        $text = $userData['msg'];
        $password = $userData['sign_password'];
        $logFile = isset($userData['log']) ? $userData['log'] : null;

        try {

            $factory = new WebToPay_Factory(array('password' => $password));
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
     * @param integer $projectId
     * @param string  $currency
     *
     * @return WebToPay_PaymentMethodList
     *
     * @throws WebToPayException
     */
    public static function getPaymentMethodList($projectId, $currency = 'LTL') {
        $factory = new WebToPay_Factory(array('projectId' => $projectId));
        return $factory->getPaymentMethodListProvider()->getPaymentMethodList($currency);
    }

    /**
     * Logs to file. Just skips logging if file is not writeable
     *
     * @param string $type
     * @param string $msg
     * @param string $logfile
     */
    protected static function log($type, $msg, $logfile) {
        $fp = @fopen($logfile, 'a');
        if (!$fp) {
            return;
        }

        $logline = array(
            $type,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-',
            date('[Y-m-d H:i:s O]'),
            'v' . self::VERSION . ':',
            $msg
        );

        $logline = implode(' ', $logline)."\n";
        fwrite($fp, $logline);
        fclose($fp);

        // clear big log file
        if (filesize($logfile) > 1024 * 1024 * pi()) {
            copy($logfile, $logfile.'.old');
            unlink($logfile);
        }
    }
}


