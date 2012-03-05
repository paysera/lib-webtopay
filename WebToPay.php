<?php
/**
 * PHP Library for WebToPay provided services.
 * Copyright (C) 2010  http://www.webtopay.com/
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
 * @version    1.5
 * @link       http://www.webtopay.com/
 */

class WebToPay {

    /**
     * WebToPay Library version.
     */
    const VERSION = '1.5';

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
     * Prefix for callback data.
     */
    const PREFIX = 'wp_';


    /**
     * Identifies what verification method was used.
     *
     * Values can be:
     *  - false     not verified
     *  - RESPONSE  only response parameters are verified
     *  - SS1v2     SS1 v2 verification
     *  - SS2       SS2 verification
     */
    public static $verified = false;


    /**
     * If true, check SS2 if false, skip to SS1
     *
     * @deprecated
     */
    private static $SS2 = true;

    /**
     * Toggle SS2 checking. Usualy you don't need to use this method, because
     * by default first SS2 support are checked and if it doesn't work,
     * fallback to SS1.
     *
     * Use this method if your server supports SS2, but you want to use SS1.
     *
     * @deprecated
     */
    public static function toggleSS2($value) {
        self::$SS2 = (bool) $value;
    }


    /**
     * Throw exception.
     *
     * @param  string $code
     * @return void
     */
    public static function throwResponseError($code) {
        $errors = array(
            '0x1'    => self::_('mokėjimo suma per maža'),
            '0x2'    => self::_('mokėjimo suma per didelė'),
            '0x3'    => self::_('nurodyta valiuta neaptarnaujama'),
            '0x4'    => self::_('nėra sumos arba valiutos'),
            '0x6'    => self::_('klaidos kodas nebenaudojamas'),
            '0x7'    => self::_('išjungtas testavimo režimas'),
            '0x8'    => self::_('jūs uždraudėte šį mokėjimo būdą'),
            '0x9'    => self::_('blogas "paytext" kintamojo kodavimas (turi būti utf-8)'),
            '0x10'   => self::_('tuščias arba neteisingai užpildytas "orderid"'),
            '0x11'   => self::_('mokėjimas negalimas, kol projektas nepatvirtintas arba jeigu jis yra blokuotas'),
            '0x12'   => self::_('negautas "projectid" parametras, nors jis yra privalomas'),
            '0x13'   => self::_('"accepturl", "cancellurl" arba "callbacurl" skiriasi nuo projekte patvirtintų adresų'),
            '0x14'   => self::_('blogai sugeneruotas paraštas ("sign" parametras)'),
            '0x15'   => self::_('klaidingi kai kurie iš perduotų parametrų'),
            '0x15x0' => self::_('neteisingas vienas iš šių parametrų: cancelurl, accepturl, callbackurl'),
            '0x15x1' => self::_('neteisingas parametras: time_limit'),
        );

        if (isset($errors[$code])) {
            $msg = $errors[$code];
        }
        else {
            $msg = self::_('Nenumatyta klaida');
        }

        throw new WebToPayException($msg);
    }


    /**
     * Returns specification array for request.
     *
     * @return array
     */
    public static function getRequestSpec() {
        // Array structure:
        //  * name      – request item name.
        //  * maxlen    – max allowed value for item.
        //  * required  – is this item is required.
        //  * user      – if true, user can set value of this item, if false
        //                item value is generated.
        //  * isrequest – if true, item will be included in request array, if
        //                false, item only be used internaly and will not be
        //                included in outgoing request array.
        //  * regexp    – regexp to test item value.
        return array(
            array('projectid',      11,     true,   true,   true,   '/^\d+$/'),
            array('orderid',        40,     true,   true,   true,   ''),
            array('lang',           3,      false,  true,   true,   '/^[a-z]{3}$/i'),
            array('amount',         11,     false,  true,   true,   '/^\d+$/'),
            array('currency',       3,      false,  true,   true,   '/^[a-z]{3}$/i'),
            array('accepturl',      255,    true,   true,   true,   ''),
            array('cancelurl',      255,    true,   true,   true,   ''),
            array('callbackurl',    255,    true,   true,   true,   ''),
            array('payment',        20,     false,  true,   true,   ''),
            array('country',        2,      false,  true,   true,   '/^[a-z_]{2}$/i'),
            array('paytext',        255,    false,  true,   true,   ''),
            array('p_firstname',    255,    false,  true,   true,   ''),
            array('p_lastname',     255,    false,  true,   true,   ''),
            array('p_email',        255,    false,  true,   true,   ''),
            array('p_street',       255,    false,  true,   true,   ''),
            array('p_city',         255,    false,  true,   true,   ''),
            array('p_state',        20,     false,  true,   true,   ''),
            array('p_zip',          20,     false,  true,   true,   ''),
            array('p_countrycode',  2,      false,  true,   true,   '/^[a-z]{2}$/i'),
            array('sign',           255,    true,   false,  true,   ''),
            array('sign_password',  255,    true,   true,   false,  ''),
            array('only_payments',  0,      false,  true,   true,   ''),
            array('disalow_payments', 0,    false,  true,   true,   ''),
            array('repeat_request', 1,      false,  false,  true,   '/^[01]$/'),
            array('test',           1,      false,  true,   true,   '/^[01]$/'),
            array('version',        9,      true,   false,  true,   '/^\d+\.\d+$/'),
            array('time_limit',     19,     false,  true,   true,   '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'),
        );
    }


    /**
     * Returns specification array for repeat request.
     *
     * @return array
     */
    public static function getRepeatRequestSpec() {
        // Array structure:
        //  * name      – request item name.
        //  * maxlen    – max allowed value for item.
        //  * required  – is this item is required.
        //  * user      – if true, user can set value of this item, if false
        //                item value is generated.
        //  * isrequest – if true, item will be included in request array, if
        //                false, item only be used internaly and will not be
        //                included in outgoing request array.
        //  * regexp    – regexp to test item value.
        return array(
            array('projectid',      11,     true,   true,   true,   '/^\d+$/'),
            array('requestid',      40,     true,   true,   true,   ''),
            array('sign',           255,    true,   false,  true,   ''),
            array('sign_password',  255,    true,   true,   false,  ''),
            array('repeat_request', 1,      true,   false,  true,   '/^1$/'),
            array('version',        9,      true,   false,  true,   '/^\d+\.\d+$/'),
        );
    }


    /**
     * Returns specification array for makro response.
     *
     * @return array
     */
    public static function getMakroResponseSpec() {
        // Array structure:
        //  * name       – request item name.
        //  * maxlen     – max allowed value for item.
        //  * required   – is this item is required in response.
        //  * mustcheck  – this item must be checked by user.
        //  * isresponse – if false, item must not be included in response array.
        //  * regexp     – regexp to test item value.
        return array(
            'projectid'     => array(11,     true,   true,   true,  '/^\d+$/'),
            'orderid'       => array(40,     false,  false,  true,  ''),
            'lang'          => array(3,      false,  false,  true,  '/^[a-z]{3}$/i'),
            'amount'        => array(11,     false,  false,  true,  '/^\d+$/'),
            'currency'      => array(3,      false,  false,  true,  '/^[a-z]{3}$/i'),
            'payment'       => array(20,     false,  false,  true,  ''),
            'country'       => array(2,      false,  false,  true,  '/^[a-z_]{2}$/i'),
            'paytext'       => array(0,      false,  false,  true,  ''),
            '_ss2'          => array(0,      true,   false,  true,  ''),
            '_ss1v2'        => array(0,      false,  false,  true,  ''),
            'name'          => array(255,    false,  false,  true,  ''),
            'surename'      => array(255,    false,  false,  true,  ''),
            'status'        => array(255,    false,  false,  true,  ''),
            'error'         => array(20,     false,  false,  true,  ''),
            'test'          => array(1,      false,  false,  true,  '/^[01]$/'),

            'p_email'       => array(0,      false,  false,  true,  ''),
            'requestid'     => array(40,     false,  false,  true,  ''),
            'payamount'     => array(0,      false,  false,  true,  ''),
            'paycurrency'   => array(0,      false,  false,  true,  ''),

            'version'       => array(9,      true,   false,  true,  '/^\d+\.\d+$/'),

            'sign_password' => array(255,    false,  true,   false, ''),
        );
    }



    /**
     * Returns specification array for mikro response.
     *
     * @return array
     */
    public static function getMikroResponseSpec() {
        // Array structure:
        //  * name       – request item name.
        //  * maxlen     – max allowed value for item.
        //  * required   – is this item is required in response.
        //  * mustcheck  – this item must be checked by user.
        //  * isresponse – if false, item must not be included in response array.
        //  * regexp     – regexp to test item value.
        return array(
            'to'            => array(0,      true,   false,  true,  ''),
            'sms'           => array(0,      true,   false,  true,  ''),
            'from'          => array(0,      true,   false,  true,  ''),
            'operator'      => array(0,      true,   false,  true,  ''),
            'amount'        => array(0,      true,   false,  true,  ''),
            'currency'      => array(0,      true,   false,  true,  ''),
            'country'       => array(0,      true,   false,  true,  ''),
            'id'            => array(0,      true,   false,  true,  ''),
            '_ss2'          => array(0,      true,   false,  true,  ''),
            '_ss1v2'        => array(0,      true,   false,  true,  ''),
            'test'          => array(0,      true,   false,  true,  ''),
            'key'           => array(0,      true,   false,  true,  ''),
            //'version'       => array(9,      true,   false,  true,  '/^\d+\.\d+$/'),
        );
    }

    /**
     * Checks user given request data array.
     *
     * If any errors occurs, WebToPayException will be raised.
     *
     * This method returns validated request array. Returned array contains
     * only those items from $data, that are needed.
     *
     * @param  array $data
     * @return array
     */
    public static function checkRequestData($data, $specs) {
        $request = array();
        foreach ($specs as $spec) {
            list($name, $maxlen, $required, $user, $isrequest, $regexp) = $spec;
            if (!$user) continue;
            if ($required && !isset($data[$name])) {
                $e = new WebToPayException(
                    self::_("'%s' is required but missing.", $name),
                    WebToPayException::E_MISSING);
                $e->setField($name);
                throw $e;
            }

            if (!empty($data[$name])) {
                if ($maxlen && strlen($data[$name]) > $maxlen) {
                    $e = new WebToPayException(
                        self::_("'%s' value '%s' is too long, %d characters allowed.",
                                $name, $data[$name], $maxlen),
                        WebToPayException::E_MAXLEN);
                    $e->setField($name);
                    throw $e;
                }

                if ('' != $regexp && !preg_match($regexp, $data[$name])) {
                    $e = new WebToPayException(
                        self::_("'%s' value '%s' is invalid.", $name, $data[$name]),
                        WebToPayException::E_REGEXP);
                    $e->setField($name);
                    throw $e;
                }
            }

            if ($isrequest && isset($data[$name])) {
                $request[$name] = $data[$name];
            }
        }

        return $request;
    }


    /**
     * Puts signature on request data array.
     *
     * @param  array   $specification
     * @param  string  $request
     * @param  string  $password
     * @return string
     */
    public static function signRequest($specification, $request, $password) {
        $fields = array();
        foreach ($specification as $field) {
            if ($field[4] && $field[0] != 'sign') {
                $fields[] = $field[0];
            }
        }

        $data = '';
        foreach ($fields as $key) {
            if (isset($request[$key]) && trim($request[$key]) != '') {
                $data .= $request[$key];
            }
        }
        $request['sign'] = md5($data . $password);

        return $request;
    }


    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * Method accepts single parameter $data of array type. All possible array
     * keys are described here:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * @param  array $data Information about current payment request.
     * @return array
     */
    public static function buildRequest($data) {
        $specs = self::getRequestSpec();
        $request = self::checkRequestData($data, $specs);
        $version = explode('.', self::VERSION);
        $request['version'] = $version[0].'.'.$version[1];
        $request = self::signRequest($specs, $request, $data['sign_password']);
        return $request;
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
     * @param  array $data Information about current payment request.
     * @return array
     */
    public static function buildRepeatRequest($data) {
        $specs = self::getRepeatRequestSpec();
        $request = self::checkRequestData($data, $specs);
        $request['repeat_request'] = '1';
        $version = explode('.', self::VERSION);
        $request['version'] = $version[0].'.'.$version[1];
        $request = self::signRequest($specs, $request, $data['sign_password']);
        return $request;
    }

    /**
     * Download certificate from webtopay.com.
     *
     * @param  string $cert
     * @return string
     */
    public static function getCert($cert) {
        return self::getUrlContent('http://downloads.webtopay.com/download/'.$cert);
    }

    /**
     * Check is response certificate is valid
     *
     * @param  string $response
     * @param  string $cert
     * @return bool
     */
    public static function checkResponseCert($response, $cert='public.key') {
        $pKeyP = self::getCert($cert);
        if (!$pKeyP) {
            throw new WebToPayException(
                self::_('Can\'t get openssl public key for %s', $cert),
                WebToPayException::E_INVALID);
        }

        $_SS2 = '';
        foreach ($response as $key => $value) {
            if (in_array($key, array('_ss1v2', '_ss2'))) {
                continue;
            }
            $_SS2 .= "{$value}|";
        }
        
        $response['_ss2'] = str_replace(' ', '+', $response['_ss2']);
        
        $ok = openssl_verify($_SS2, base64_decode($response['_ss2']), $pKeyP);

        if ($ok !== 1) {
            throw new WebToPayException(
                self::_('Can\'t verify SS2 for %s', $cert),
                WebToPayException::E_INVALID);
        }

        return true;
    }

    public static function checkResponseData($response, $mustcheck_data, $specs) {
        $resp_keys = array();
        foreach ($specs as $name => $spec) {
            list($maxlen, $required, $mustcheck, $is_response, $regexp) = $spec;
            if ($required && !isset($response[$name])) {
                $e = new WebToPayException(
                    self::_("'%s' is required but missing.", $name),
                    WebToPayException::E_MISSING);
                $e->setField($name);
                throw $e;
            }

            if ($mustcheck) {
                if (!isset($mustcheck_data[$name])) {
                    $e = new WebToPayException(
                        self::_("'%s' must exists in array of second parameter ".
                                "of checkResponse() method.", $name),
                        WebToPayException::E_USER_PARAMS);
                    $e->setField($name);
                    throw $e;
                }

                if ($is_response) {
                    if ($response[$name] != $mustcheck_data[$name]) {
                        $e = new WebToPayException(
                            self::_("'%s' yours and requested value is not ".
                                    "equal ('%s' != '%s') ",
                                    $name, $mustcheck_data[$name], $response[$name]),
                            WebToPayException::E_INVALID);
                        $e->setField($name);
                        throw $e;
                    }
                }
            }

            if (!empty($response[$name])) {
                if ($maxlen && strlen($response[$name]) > $maxlen) {
                    $e = new WebToPayException(
                        self::_("'%s' value '%s' is too long, %d characters allowed.",
                                $name, $response[$name], $maxlen),
                        WebToPayException::E_MAXLEN);
                    $e->setField($name);
                    throw $e;
                }

                if ('' != $regexp && !preg_match($regexp, $response[$name])) {
                    $e = new WebToPayException(
                        self::_("'%s' value '%s' is invalid.", $name, $response[$name]),
                        WebToPayException::E_REGEXP);
                    $e->setField($name);
                    throw $e;
                }
            }

            if (isset($response[$name])) {
                $resp_keys[] = $name;
            }
        }

        // Filter only parameters passed from webtopay
        $_response = array();
        foreach (array_keys($response) as $key) {
            if (in_array($key, $resp_keys)) {
                $_response[$key] = $response[$key];
            }
        }

        return $_response;
    }


    /**
     * Check if SS2 checking is available and enabled.
     *
     * @return bool
     */
    public static function useSS2() {
        return function_exists('openssl_pkey_get_public');
    }


    /**
     * Check for SS1, which is not depend on openssl functions.
     *
     * @param  array  $response
     * @param  string $password
     * @return bool
     */
    public static function checkSS1v2($response, $password) {
        if (32 != strlen($password)) {
            $password = md5($password);
        }

        $buffer = array($password);
        foreach ($response as $key => $value) {
            if (in_array($key, array('_ss1v2', '_ss2'))) {
                continue;
            }
            $buffer[] = $value;
        }

        $ss1v2 = md5(implode('|', $buffer));

        if ($response['_ss1v2'] != $ss1v2) {
            throw new WebToPayException(
                self::_('Can\'t verify SS1 v2'),
                WebToPayException::E_INVALID);
        }

        return true;
    }

    /**
     * Returns payment url
     *
     * @param  string $language
     * @return string $url
     */
    public static function getPaymentUrl($language) {
        $url = self::PAY_URL;
        if($language != 'LT') {
           $url = str_replace('mokejimai.lt', 'webtopay.com', $url);
        }
        return $url;
    }

    /**
     * Return type and specification of given response array.
     *
     * @param array     $response
     * @return array($type, $specs)
     */
    public static function getSpecsForResponse($response) {
        if (
                isset($response['to']) &&
                isset($response['from']) &&
                isset($response['sms']) &&
                !isset($response['projectid'])
            )
        {
            $type = 'mikro';
            $specs = self::getMikroResponseSpec();
        }
        else {
            $type = 'makro';
            $specs = self::getMakroResponseSpec();
        }

        return array($type, $specs);
    }


    public static function getPrefixed($data, $prefix) {
        if (empty($prefix)) return $data;
        $ret = array();
        foreach ($data as $key => $val) {
            if (strpos($key, $prefix) === 0 && strlen($key) > 3) {
                $ret[substr($key, 3)] = $val;
            }
        }
        return $ret;
    }

    private static function getUrlContent($URL){
        $url = parse_url($URL);
        if ('https' == $url['scheme']) {
            $host = 'ssl://'.$url['host'];
            $port = 443;
        } else {
            $host = $url['host'];
            $port = 80;
        }

        try {
            $fp = fsockopen($host, $port, $errno, $errstr, 30);
            if (!$fp) {
                throw new WebToPayException(
                    self::_('Can\'t connect to %s', $URL),
                    WebToPayException::E_INVALID);
            }

            if(isset($url['query'])) {
                $data = $url['path'].'?'.$url['query'];
            } else {
                $data = $url['path'];
            }

            $out = "GET " . $data . " HTTP/1.0\r\n";
            $out .= "Host: ".$url['host']."\r\n";
            $out .= "Connection: Close\r\n\r\n";

            $content = '';

            fwrite($fp, $out);
            while (!feof($fp)) $content .= fgets($fp, 8192);
            fclose($fp);

            list($header, $content) = explode("\r\n\r\n", $content, 2);

            return trim($content);

        } catch (WebToPayException $e) {
            throw new WebToPayException(self::_('fsockopen fail!', WebToPayException::E_INVALID));
        }
    }


    /**
     * Checks and validates response from WebToPay server.
     *
     * This function accepts both mikro and makro responses.
     *
     * First parameter usualy should by $_GET array.
     *
     * Description about response can be found here:
     * makro: https://www.mokejimai.lt/makro_specifikacija.html
     * mikro: https://www.mokejimai.lt/mikro_mokejimu_specifikacija_SMS.html
     *
     * If response is not correct, WebToPayException will be raised.
     *
     * @param array     $response       Response array.
     * @param array     $user_data
     * @return void
     */
    public static function checkResponse($response, $user_data=array()) {
        self::$verified = false;

        $response = self::getPrefixed($response, self::PREFIX);

        // *get* response type (makro|mikro)
        list($type, $specs) = self::getSpecsForResponse($response);

        try {
            // *check* response
            $version = explode('.', self::VERSION);
            $version = $version[0].'.'.$version[1];
            if ('makro' == $type && $response['version'] != $version) {
                throw new WebToPayException(
                    self::_('Incompatible library and response versions: ' .
                            'libwebtopay %s, response %s', self::VERSION, $response['version']),
                    WebToPayException::E_INVALID);
            }

            if ('makro' == $type && $response['projectid'] != $user_data['projectid']) {
                throw new WebToPayException(
                    self::_('Bad projectid: ' .
                            'libwebtopay %s, response %s', self::VERSION, $response['version']),
                    WebToPayException::E_INVALID);
            }

            if ('makro' == $type) {
                self::$verified = 'RESPONSE VERSION '.$response['version'].' OK';
            }

            $orderid = 'makro' == $type ? $response['orderid'] : $response['id'];
            $password = $user_data['sign_password'];

            // *check* SS2
            if (self::useSS2()) {
                $cert = 'public.key';
                if (self::checkResponseCert($response, $cert)) {
                    self::$verified = 'SS2 public.key';
                }
            }

            // *check* SS1 v2
            else if (self::checkSS1v2($response, $password)) {
                self::$verified = 'SS1v2';
            }

            // *check* status
            if ('makro' == $type && $response['status'] != '1') {
                throw new WebToPayException(
                    self::_('Returned transaction status is %d, successful status '.
                            'should be 1.', $response['status']),
                    WebToPayException::E_STATUS);
            }

        }

        catch (WebToPayException $e) {
            if (isset($user_data['log'])) {
                self::log('ERR',
                    self::responseToLog($type, $response) .
                    ' ('. get_class($e).': '. $e->getMessage().')',
                    $user_data['log']);
            }
            throw $e;
        }

        if (isset($user_data['log'])) {
            self::log('OK', self::responseToLog($type, $response), $user_data['log']);
        }

        return $response;
    }

    public static function responseToLog($type, $req) {
        if ('mikro' == $type) {
            return self::mikroResponseToLog($req);
        }
        else {
            return self::makroResponseToLog($req);
        }
    }

    public static function mikroResponseToLog($req) {
        $ret = array();
        foreach (array('to', 'from', 'id', 'sms') as $key) {
            $ret[] = $key.':"'.$req[$key].'"';
        }

        return 'MIKRO '.implode(', ', $ret);
    }

    public static function makroResponseToLog($req) {
        $ret = array();
        foreach (array('projectid', 'orderid', 'payment') as $key) {
            $ret[] = $key.':"'.$req[$key].'"';
        }

        return 'MAKRO '.implode(', ', $ret);
    }

    public static function mikroAnswerToLog($answer) {
        $ret = array();
        foreach (array('id', 'msg') as $key) {
            $ret[] = $key.':"'.$answer[$key].'"';
        }

        return 'MIKRO [answer] '.implode(', ', $ret);
    }

    public static function log($type, $msg, $logfile=null) {
        if (!isset($logfile)) {
            return false;
        }

        $fp = @fopen($logfile, 'a');
        if (!$fp) {
            throw new WebToPayException(
                self::_('Can\'t write to logfile: %s', $logfile), WebToPayException::E_LOG);
        }

        $logline = array();

        // *log* type
        $logline[] = $type;

        // *log* REMOTE_ADDR
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $logline[] = $_SERVER['REMOTE_ADDR'];
        }
        else {
            $logline[] = '-';
        }

        // *log* datetime
        $logline[] = date('[Y-m-d H:i:s O]');

        // *log* version
        $logline[] = 'v'.self::VERSION.':';

        // *log* message
        $logline[] = $msg;

        $logline = implode(' ', $logline)."\n";
        fwrite($fp, $logline);
        fclose($fp);

        // clear big log file
        if (filesize($logfile) > 1024 * 1024 * pi()) {
            copy($logfile, $logfile.'.old');
            unlink($logfile);
        }
    }


    /**
     * Sends SMS answer.
     *
     * @param array     $answer
     * @return void
     */
    public static function smsAnswer($answer) {

        $data = array(
                'id'            => $answer['id'],
                'msg'           => $answer['msg'],
                'transaction'   => md5($answer['sign_password'].'|'.$answer['id']),
            );

        $url = self::SMS_ANSWER_URL.'?'.http_build_query($data);
        try {
            $content = self::getUrlContent($url);
            if (strpos($content, 'OK') !== 0) {
                throw new WebToPayException(
                    self::_('Error: %s', $content),
                    WebToPayException::E_SMS_ANSWER);
            }
        } catch (WebToPayException $e) {
            if (isset($answer['log'])) {
                self::log('ERR',
                    self::mikroAnswerToLog($answer).
                    ' ('. get_class($e).': '. $e->getMessage().')',
                    $answer['log']);
            }
            throw $e;
        }

        if (isset($answer['log'])) {
            self::log('OK', self::mikroAnswerToLog($answer), $answer['log']);
        }

    }


    /**
     * I18n support.
     */
    public static function _() {
        $args = func_get_args();
        if (sizeof($args) > 1) {
            return call_user_func_array('sprintf', $args);
        }
        else {
            return $args[0];
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
        if (!function_exists('simplexml_load_string')) {
            throw new WebToPayException('You have to install libxml to use payment methods API');
        }
        $xmlAsString = self::getUrlContent(self::XML_URL . $projectId . '/currency:' . $currency);
        $useInternalErrors = libxml_use_internal_errors(false);
        $rootNode = simplexml_load_string($xmlAsString);
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);
        if (!$rootNode) {
            throw new WebToPayException('Unable to load XML from remote server');
        }
        $methodList = new WebToPay_PaymentMethodList($projectId, $currency);
        $methodList->fromXmlNode($rootNode);
        return $methodList;
    }

    /**
     * Gets available payment types as array
     *
     * @param string    $payCurrency
     * @param int       $sum
     * @param array     $currency           Not in use anymore
     * @param string    $lang
     * @param int       $projectID
     *
     * @return array
     *
     * @deprecated      use getPaymentMethodList instead
     */
    public static function getPaymentMethods($payCurrency, $sum, $currency, $lang, $projectID) {
        $result = array();

        $countries = self::getPaymentMethodList($projectID, $payCurrency)
            ->setDefaultLanguage($lang)
            ->filterForAmount($sum, $payCurrency)
            ->getCountries();
        foreach ($countries as $country) {
            foreach ($country->getGroups() as $group) {
                foreach ($group->getPaymentMethods() as $method) {
                    $result[$country->getCode()][$group->getTitle()][$method->getTitle()] = array(
                        'name' => $method->getKey(),
                        'logo' => $method->getLogoUrl(),
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Deprecated method. Left to preserve backwards compatibility in current version
     *
     * @return boolean
     *
     * @deprecated    this method will be removed in future releace
     */
    public static function getXML() {
        return true;
    }

    /**
     * Deprecated method. Left to preserve backwards compatibility in current version
     *
     * @deprecated    this method will be removed in future releace
     */
    public static function parseXML() {
        // used to write cache file, which is not used anymore
    }

    /**
     * Deprecated method. Left to preserve backwards compatibility in current version
     *
     * @return array
     *
     * @deprecated
     */
    public static function filterPayMethods() {
        return array();
    }

}



class WebToPayException extends Exception {

    /**
     * Missing field.
     */
    const E_MISSING = 1;

    /**
     * Invalid field value.
     */
    const E_INVALID = 2;

    /**
     * Max length exceeded.
     */
    const E_MAXLEN = 3;

    /**
     * Regexp for field value doesn't match.
     */
    const E_REGEXP = 4;

    /**
     * Missing or invalid user given parameters.
     */
    const E_USER_PARAMS = 5;

    /**
     * Logging errors
     */
    const E_LOG = 6;

    /**
     * SMS answer errors
     */
    const E_SMS_ANSWER = 7;

    /**
     * Macro answer errors
     */
    const E_STATUS = 8;

    /**
     * Library errors - if this happens, bug-report should be sent; also you can check for newer version
     */
    const E_LIBRARY = 9;

    /**
     * Errors in remote service - it returns some invalid data
     */
    const E_SERVICE = 10;

    protected $field_name = false;

    public function setField($field_name) {
        $this->field_name = $field_name;
    }

    public function getField() {
        return $this->field_name;
    }
}

/**
 * Class with all information about available payment methods for some project, optionally filtered by some amount.
 */
class WebToPay_PaymentMethodList {
    /**
     * Holds available payment countries
     *
     * @var WebToPay_PaymentMethodCountry[]
     */
    protected $countries;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Project ID, to which this method list is valid
     *
     * @var integer
     */
    protected $projectId;

    /**
     * Currency for min and max amounts in this list
     *
     * @var string
     */
    protected $currency;

    /**
     * If this list is filtered for some amount, this field defines it
     *
     * @var integer
     */
    protected $amount;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param string  $currency              currency for min and max amounts in this list
     * @param string  $defaultLanguage
     * @param integer $amount                null if this list is not filtered by amount
     */
    public function __construct($projectId, $currency, $defaultLanguage = 'lt', $amount = null) {
        $this->projectId = $projectId;
        $this->countries = array();
        $this->defaultLanguage = $defaultLanguage;
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethodList
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        foreach ($this->countries as $country) {
            $country->setDefaultLanguage($language);
        }
        return $this;
    }

    /**
     * Gets default language for titles
     *
     * @return string
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * Gets project ID for this payment method list
     *
     * @return integer
     */
    public function getProjectId() {
        return $this->projectId;
    }

    /**
     * Gets currency for min and max amounts in this list
     *
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * Gets whether this list is already filtered for some amount
     *
     * @return boolean
     */
    public function isFiltered() {
        return $this->amount !== null;
    }

    /**
     * Returns available countries
     *
     * @return WebToPay_PaymentMethodCountry[]
     */
    public function getCountries() {
        return $this->countries;
    }

    /**
     * Adds new country to payment methods. If some other country with same code was registered earlier, overwrites it.
     * Returns added country instance
     *
     * @param WebToPay_PaymentMethodCountry $country
     *
     * @return WebToPay_PaymentMethodCountry
     */
    public function addCountry(WebToPay_PaymentMethodCountry $country) {
        return $this->countries[$country->getCode()] = $country;
    }

    /**
     * Gets country object with specified country code. If no country with such country code is found, returns null.
     *
     * @param string $countryCode
     *
     * @return null|WebToPay_PaymentMethodCountry
     */
    public function getCountry($countryCode) {
        return isset($this->countries[$countryCode]) ? $this->countries[$countryCode] : null;
    }

    /**
     * Returns new payment method list instance with only those payment methods, which are available for provided
     * amount.
     * Returns itself, if list is already filtered and filter amount matches the given one.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return WebToPay_PaymentMethodList
     *
     * @throws WebToPayException    if this list is already filtered and not for provided amount
     */
    public function filterForAmount($amount, $currency) {
        if ($currency !== $this->currency) {
            throw new WebToPayException(
                'Currencies do not match. Given currency: ' . $currency . ', currency in list: ' . $this->currency
            );
        }
        if ($this->isFiltered()) {
            if ($this->amount === $amount) {
                return $this;
            } else {
                throw new WebToPayException('This list is already filtered, use unfiltered list instead');
            }
        } else {
            $list = new WebToPay_PaymentMethodList($this->projectId, $currency, $this->defaultLanguage, $amount);
            foreach ($this->getCountries() as $country) {
                $country = $country->filterForAmount($amount, $currency);
                if (!$country->isEmpty()) {
                    $list->addCountry($country);
                }
            }
            return $list;
        }
    }

    /**
     * Loads countries from given XML node
     *
     * @param SimpleXMLElement $xmlNode
     */
    public function fromXmlNode($xmlNode) {
        foreach ($xmlNode->country as $countryNode) {
            $titleTranslations = array();
            foreach ($countryNode->title as $titleNode) {
                $titleTranslations[(string) $titleNode->attributes()->language] = (string) $titleNode;
            }
            $this->addCountry($this->createCountry((string) $countryNode->attributes()->code, $titleTranslations))
                ->fromXmlNode($countryNode);
        }
    }

    /**
     * Method to create new country instances. Overwrite if you have to use some other country subtype.
     *
     * @param string $countryCode
     * @param array  $titleTranslations
     */
    protected function createCountry($countryCode, array $titleTranslations = array()) {
        return new WebToPay_PaymentMethodCountry($countryCode, $titleTranslations, $this->defaultLanguage);
    }
}

/**
 * Payment method configuration for some country
 */
class WebToPay_PaymentMethodCountry {
    /**
     * @var string
     */
    protected $countryCode;

    /**
     * Holds available payment types for this country
     *
     * @var WebToPay_PaymentMethodGroup[]
     */
    protected $groups;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Translations array for this country. Holds associative array of country title by language codes.
     *
     * @var array
     */
    protected $titleTranslations;

    /**
     * Constructs object
     *
     * @param string $countryCode
     * @param string $defaultLanguage
     */
    public function __construct($countryCode, $titleTranslations, $defaultLanguage = 'lt') {
        $this->countryCode = $countryCode;
        $this->defaultLanguage = $defaultLanguage;
        $this->titleTranslations = $titleTranslations;
        $this->groups = array();
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethodCountry
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        foreach ($this->groups as $group) {
            $group->setDefaultLanguage($language);
        }
        return $this;
    }

    /**
     * Gets title of the group. Tries to get title in specified language. If it is not found or if language is not
     * specified, uses default language, given to constructor.
     *
     * @param string [Optional] $languageCode
     *
     * @return string
     */
    public function getTitle($languageCode = null) {
        if ($languageCode !== null && isset($this->titleTranslations[$languageCode])) {
            return $this->titleTranslations[$languageCode];
        } elseif (isset($this->titleTranslations[$this->defaultLanguage])) {
            return $this->titleTranslations[$this->defaultLanguage];
        } else {
            return $this->countryCode;
        }
    }

    /**
     * Gets default language for titles
     *
     * @return string
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * Gets country code
     *
     * @return string
     */
    public function getCode() {
        return $this->countryCode;
    }

    /**
     * Adds new group to payment methods for this country.
     * If some other group was registered earlier with same key, overwrites it.
     * Returns given group
     *
     * @param WebToPay_PaymentMethodGroup $group
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function addGroup(WebToPay_PaymentMethodGroup $group) {
        return $this->groups[$group->getKey()] = $group;
    }

    /**
     * Gets group object with specified group key. If no group with such key is found, returns null.
     *
     * @param string $groupKey
     *
     * @return null|WebToPay_PaymentMethodGroup
     */
    public function getGroup($groupKey) {
        return isset($this->groups[$groupKey]) ? $this->groups[$groupKey] : null;
    }

    /**
     * Returns payment method groups registered for this country.
     *
     * @return WebToPay_PaymentMethodGroup[]
     */
    public function getGroups() {
        return $this->groups;
    }

    /**
     * Gets payment methods in all groups
     *
     * @return WebToPay_PaymentMethod[]
     */
    public function getPaymentMethods() {
        $paymentMethods = array();
        foreach ($this->groups as $group) {
            $paymentMethods = array_merge($paymentMethods, $group->getPaymentMethods());
        }
        return $paymentMethods;
    }

    /**
     * Returns new country instance with only those payment methods, which are available for provided amount.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return WebToPay_PaymentMethodCountry
     */
    public function filterForAmount($amount, $currency) {
        $country = new WebToPay_PaymentMethodCountry($this->countryCode, $this->titleTranslations, $this->defaultLanguage);
        foreach ($this->getGroups() as $group) {
            $group = $group->filterForAmount($amount, $currency);
            if (!$group->isEmpty()) {
                $country->addGroup($group);
            }
        }
        return $country;
    }

    /**
     * Returns whether this country has no groups
     *
     * @return boolean
     */
    public function isEmpty() {
        return count($this->groups) === 0;
    }

    /**
     * Loads groups from given XML node
     *
     * @param SimpleXMLElement $countryNode
     */
    public function fromXmlNode($countryNode) {
        foreach ($countryNode->payment_group as $groupNode) {
            $key = (string) $groupNode->attributes()->key;
            $titleTranslations = array();
            foreach ($groupNode->title as $titleNode) {
                $titleTranslations[(string) $titleNode->attributes()->language] = (string) $titleNode;
            }
            $this->addGroup($this->createGroup($key, $titleTranslations))->fromXmlNode($groupNode);
        }
    }

    /**
     * Method to create new group instances. Overwrite if you have to use some other group subtype.
     *
     * @param string $groupKey
     * @param array  $translations
     *
     * @return WebToPay_PaymentMethodGroup
     */
    protected function createGroup($groupKey, array $translations = array()) {
        return new WebToPay_PaymentMethodGroup($groupKey, $translations, $this->defaultLanguage);
    }
}

/**
 * Wrapper class to group payment methods. Wach country can have several payment method groups, each of them
 * have one or more payment methods.
 */
class WebToPay_PaymentMethodGroup {
    /**
     * Some unique (in the scope of country) key for this group
     *
     * @var string
     */
    protected $groupKey;

    /**
     * Translations array for this group. Holds associative array of group title by country codes.
     *
     * @var array
     */
    protected $translations;

    /**
     * Holds actual payment methods
     *
     * @var WebToPay_PaymentMethod[]
     */
    protected $paymentMethods;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Constructs object
     *
     * @param string $groupKey
     * @param array  $translations
     */
    public function __construct($groupKey, array $translations = array(), $defaultLanguage = 'lt') {
        $this->groupKey = $groupKey;
        $this->translations = $translations;
        $this->defaultLanguage = $defaultLanguage;
        $this->paymentMethods = array();
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        foreach ($this->paymentMethods as $paymentMethod) {
            $paymentMethod->setDefaultLanguage($language);
        }
        return $this;
    }

    /**
     * Gets default language for titles
     *
     * @return string
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * Gets title of the group. Tries to get title in specified language. If it is not found or if language is not
     * specified, uses default language, given to constructor.
     *
     * @param string [Optional] $languageCode
     *
     * @return string
     */
    public function getTitle($languageCode = null) {
        if ($languageCode !== null && isset($this->translations[$languageCode])) {
            return $this->translations[$languageCode];
        } elseif (isset($this->translations[$this->defaultLanguage])) {
            return $this->translations[$this->defaultLanguage];
        } else {
            return $this->groupKey;
        }
    }

    /**
     * Returns group key
     *
     * @return string
     */
    public function getKey() {
        return $this->groupKey;
    }

    /**
     * Returns available payment methods for this group
     *
     * @return WebToPay_PaymentMethod[]
     */
    public function getPaymentMethods() {
        return $this->paymentMethods;
    }


    /**
     * Adds new payment method for this group.
     * If some other payment method with specified key was registered earlier, overwrites it.
     * Returns given payment method
     *
     * @param WebToPay_PaymentMethod $paymentMethod
     *
     * @return WebToPay_PaymentMethod
     */
    public function addPaymentMethod(WebToPay_PaymentMethod $paymentMethod) {
        return $this->paymentMethods[$paymentMethod->getKey()] = $paymentMethod;
    }

    /**
     * Gets payment method object with key. If no payment method with such key is found, returns null.
     *
     * @param string $key
     *
     * @return null|WebToPay_PaymentMethod
     */
    public function getPaymentMethod($key) {
        return isset($this->paymentMethods[$key]) ? $this->paymentMethods[$key] : null;
    }

    /**
     * Returns new group instance with only those payment methods, which are available for provided amount.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function filterForAmount($amount, $currency) {
        $group = new WebToPay_PaymentMethodGroup($this->groupKey, $this->translations, $this->defaultLanguage);
        foreach ($this->getPaymentMethods() as $paymentMethod) {
            if ($paymentMethod->isAvailableForAmount($amount, $currency)) {
                $group->addPaymentMethod($paymentMethod);
            }
        }
        return $group;
    }

    /**
     * Returns whether this group has no payment methods
     *
     * @return boolean
     */
    public function isEmpty() {
        return count($this->paymentMethods) === 0;
    }

    /**
     * Loads payment methods from given XML node
     *
     * @param SimpleXMLElement $groupNode
     */
    public function fromXmlNode($groupNode) {
        foreach ($groupNode->payment_type as $paymentTypeNode) {
            $key = (string) $paymentTypeNode->attributes()->key;
            $titleTranslations = array();
            foreach ($paymentTypeNode->title as $titleNode) {
                $titleTranslations[(string) $titleNode->attributes()->language] = (string) $titleNode;
            }
            $logoTranslations = array();
            foreach ($paymentTypeNode->logo_url as $logoNode) {
                if ((string) $logoNode !== '') {
                    $logoTranslations[(string) $logoNode->attributes()->language] = (string) $logoNode;
                }
            }
            $minAmount = null;
            $maxAmount = null;
            $currency = null;
            if (isset($paymentTypeNode->min)) {
                $minAmount = (int) $paymentTypeNode->min->attributes()->amount;
                $currency = (string) $paymentTypeNode->min->attributes()->currency;
            }
            if (isset($paymentTypeNode->max)) {
                $maxAmount = (int) $paymentTypeNode->max->attributes()->amount;
                $currency = (string) $paymentTypeNode->max->attributes()->currency;
            }
            $this->addPaymentMethod($this->createPaymentMethod(
                $key, $minAmount, $maxAmount, $currency, $logoTranslations, $titleTranslations
            ));
        }
    }

    /**
     * Method to create new payment method instances. Overwrite if you have to use some other subclass.
     *
     * @param string $key
     * @param array  $logoList
     * @param array  $titleTranslations
     *
     * @return WebToPay_PaymentMethod
     */
    protected function createPaymentMethod(
        $key, $minAmount, $maxAmount, $currency, array $logoList = array(), array $titleTranslations = array()
    ) {
        return new WebToPay_PaymentMethod(
            $key, $minAmount, $maxAmount, $currency, $logoList, $titleTranslations, $this->defaultLanguage
        );
    }
}

/**
 * Class to hold information about payment method
 */
class WebToPay_PaymentMethod {
    /**
     * Assigned key for this payment method
     *
     * @var string
     */
    protected $key;

    /**
     * Logo url list by language. Usually logo is same for all languages, but exceptions exist
     *
     * @var array
     */
    protected $logoList;

    /**
     * Title list by language
     *
     * @var array
     */
    protected $titleTranslations;

    /**
     * Default language to use for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Constructs object
     *
     * @param string  $key
     * @param integer $minAmount
     * @param integer $maxAmount
     * @param string  $currency
     * @param array   $logoList
     * @param array   $titleTranslations
     * @param string  $defaultLanguage
     */
    public function __construct(
        $key, $minAmount, $maxAmount, $currency, array $logoList = array(), array $titleTranslations = array(),
        $defaultLanguage = 'lt'
    ) {
        $this->key = $key;
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
        $this->currency = $currency;
        $this->logoList = $logoList;
        $this->titleTranslations = $titleTranslations;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethod
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        return $this;
    }

    /**
     * Gets default language for titles
     *
     * @return string
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * Get assigned payment method key
     *
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Gets logo url for this payment method. Uses specified language or default one.
     * If logotype is not found for specified language, null is returned.
     *
     * @param string [Optional] $languageCode
     *
     * @return string|null
     */
    public function getLogoUrl($languageCode = null) {
        if ($languageCode !== null && isset($this->logoList[$languageCode])) {
            return $this->logoList[$languageCode];
        } elseif (isset($this->logoList[$this->defaultLanguage])) {
            return $this->logoList[$this->defaultLanguage];
        } else {
            return null;
        }
    }

    /**
     * Gets title for this payment method. Uses specified language or default one.
     *
     * @param string [Optional] $languageCode
     *
     * @return string
     */
    public function getTitle($languageCode = null) {
        if ($languageCode !== null && isset($this->titleTranslations[$languageCode])) {
            return $this->titleTranslations[$languageCode];
        } elseif (isset($this->titleTranslations[$this->defaultLanguage])) {
            return $this->titleTranslations[$this->defaultLanguage];
        } else {
            return $this->key;
        }
    }

    /**
     * Checks if this payment method can be used for specified amount.
     * Throws exception if currency checked is not the one, for which payment method list was downloaded.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return boolean
     *
     * @throws WebToPayException
     */
    public function isAvailableForAmount($amount, $currency) {
        if ($this->currency !== $currency) {
            throw new WebToPayException(
                'Currencies does not match. You have to get payment types for the currency you are checking. Given currency: '
                    . $currency . ', available currency: ' . $this->currency
            );
        }
        return (
            ($this->minAmount === null || $amount >= $this->minAmount)
            && ($this->maxAmount === null || $amount <= $this->maxAmount)
        );
    }

    /**
     * Returns min amount for this payment method. If no min amount is specified, returns empty string.
     *
     * @return string
     */
    public function getMinAmountAsString() {
        return $this->minAmount === null ? '' : ($this->minAmount . ' ' . $this->currency);
    }

    /**
     * Returns max amount for this payment method. If no max amount is specified, returns empty string.
     *
     * @return string
     */
    public function getMaxAmountAsString() {
        return $this->maxAmount === null ? '' : ($this->maxAmount . ' ' . $this->currency);
    }
}

