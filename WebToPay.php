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
 * @author     Mantas Zimnickas <mantas@evp.lt>
 * @author     Remigijus Jarmalavičius <remigijus@evp.lt>
 * @license    http://www.gnu.org/licenses/lgpl.html
 * @version    1.2.4
 * @link       http://www.webtopay.com/
 */

class WebToPay {

    /**
     * WebToPay Library version.
     */
    const VERSION = '1.2.4';


    /**
     * Server URL where all requests should go.
     */
    const PAY_URL = 'https://www.mokejimai.lt/pay/';


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
     *  - SS1       SS1 verification
     *  - SS2       SS2 verification
     */
    public static $verified = false;


    /**
     * If true, check SS2 if false, skip to SS1
     */
    private static $SS2 = true;


    /**
     * Toggle SS2 checking. Usualy you don't need to use this method, because
     * by default first SS2 support are checked and if it doesn't work,
     * fallback to SS1.
     *
     * Use this method if your server supports SS2, but you want to use SS1.
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
                '0x1'   => self::_('mokėjimo suma per maža'),
                '0x2'   => self::_('mokėjimo suma per didelė'),
                '0x3'   => self::_('nurodyta valiuta neaptarnaujama'),
                '0x4'   => self::_('nėra sumos arba valiutos'),
                '0x6'   => self::_('klaidos kodas nebenaudojamas'),
                '0x7'   => self::_('išjungtas testavimo režimas'),
                '0x8'   => self::_('jūs uždraudėte šį mokėjimo būdą'),
                '0x9'   => self::_('blogas "paytext" kintamojo kodavimas (turi būti utf-8)'),
                '0x10'  => self::_('tuščias arba neteisingai užpildytas "orderid"'),
                '0x11'  => self::_('mokėjimas negalimas, kol projektas nepatvirtintas arba jeigu jis yra blokuotas'),
                '0x12'  => self::_('negautas "projectid" parametras, nors jis yra privalomas'),
                '0x13'  => self::_('"accepturl", "cancellurl" arba "callbacurl" skiriasi nuo projekte patvirtintų adresų'),
                '0x14'  => self::_('blogai sugeneruotas paraštas ("sign" parametras)'),
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
                array('country',        2,      false,  true,   true,   '/^[a-z]{2}$/i'),
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
                array('test',           1,      false,  true,   true,   '/^[01]$/'),
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
                'country'       => array(2,      false,  false,  true,  '/^[a-z]{2}$/i'),
                'paytext'       => array(0,      false,  false,  true,  ''),
                '_ss2'          => array(0,      true,   false,  true,  ''),
                '_ss1'          => array(0,      false,  false,  true,  ''),
                'name'          => array(255,    false,  false,  true,  ''),
                'surename'      => array(255,    false,  false,  true,  ''),
                'status'        => array(255,    false,  false,  true,  ''),
                'error'         => array(20,     false,  false,  true,  ''),
                'test'          => array(1,      false,  false,  true,  '/^[01]$/'),

                'p_email'       => array(0,      false,  false,  true,  ''),
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
                '_ss1'          => array(0,      true,   false,  true,  ''),
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
    public static function checkRequestData($data) {
        $request = array();
        $specs = self::getRequestSpec();
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
     * @param  string  $request
     * @param  string  $password
     * @return string
     */
    public static function signRequest($request, $password) {
        $fields = array(
                'projectid', 'orderid', 'lang', 'amount', 'currency',
                'accepturl', 'cancelurl', 'callbackurl', 'payment', 'country',
                'p_firstname', 'p_lastname', 'p_email', 'p_street',
                'p_city', 'p_state', 'p_zip', 'p_countrycode', 'test',
                'version'
            );
        $data = '';
        foreach ($fields as $key) {
            if (isset($request[$key]) && trim($request[$key]) != '') {
                $data .= sprintf("%03d", strlen($request[$key])) . strtolower($request[$key]);
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
        $request = self::checkRequestData($data);
        $version = explode('.', self::VERSION);
        $request['version'] = $version[0].'.'.$version[1];
        $request = self::signRequest($request, $data['sign_password']);
        return $request;
    }

    /**
     * Download certificate from webtopay.com.
     *
     * @param  string $cert
     * @return string
     */
    public static function getCert($cert) {
        $fp = fsockopen("downloads.webtopay.com", 80, $errno, $errstr, 30);
        if (!$fp) {
            throw new WebToPayException(
                self::_('Payment check: Can\'t get cert from '.
                        'downloads.webtopay.com/download/%s',
                        $cert),
                WebToPayException::E_INVALID);
            return false;
        }

        $out = "GET /download/" . $cert . " HTTP/1.1\r\n";
        $out .= "Host: downloads.webtopay.com\r\n";
        $out .= "Connection: Close\r\n\r\n";
    
        $content = '';
        
        fwrite($fp, $out);
        while (!feof($fp)) $content .= fgets($fp, 8192);
        fclose($fp);
        
        list($header, $content) = explode("\r\n\r\n", $content, 2);

        return $content;
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
            if ($key!='_ss2') $_SS2 .= "{$value}|";
        }
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
        if (!self::$SS2) return false;
        if (!function_exists('openssl_pkey_get_public')) return false;
        return true;
    }


    /**
     * Check for SS1, which is not depend on openssl functions.
     *
     * @param  array  $response
     * @param  string $passwd
     * @param  int    $orderid
     * @return bool
     */
    public static function checkSS1($response, $passwd, $orderid) {
        if (32 != strlen($passwd)) {
            $passwd = md5($passwd);
        }

        $_SS1 = array(
                $passwd,
                $orderid,
                intval($response['test']),
                1
            );

        $_SS1 = implode('|', $_SS1);
        if ($response['_ss1'] != md5($_SS1)) {
            throw new WebToPayException(
                self::_('Can\'t verify SS1'),
                WebToPayException::E_INVALID);
        }

        return true; 
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

            self::checkResponseData($response, $user_data, $specs);
            self::$verified = 'RESPONSE';

            // *check* response
            $version = explode('.', self::VERSION);
            $version = $version[0].'.'.$version[1];
            if ('makro' == $type && $response['version'] != $version) {
                throw new WebToPayException(
                    self::_('Incompatible library and response versions: ' .
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

            // *check* SS1
            else if (self::checkSS1($response, $password, $orderid)) {
                self::$verified = 'SS1';
            }

            // *check* status
            if ('makro' == $type && '1' != $response['status']) {
                throw new WebToPayException(
                    self::_('Returned transaction status is %d, successful status '.
                            'should be 1.', $response['status']),
                    WebToPayException::E_INVALID);
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
        $url = parse_url(self::SMS_ANSWER_URL);
        if ('https' == $url['scheme']) {
            $host = 'ssl://'.$url['host'];
            $port = 443;
        }
        else {
            $host = $url['host'];
            $port = 80;
        }

        try {
            $fp = fsockopen($host, $port, $errno, $errstr, 30);
            if (!$fp) {
                throw new WebToPayException(
                    self::_('Can\'t connect to %s', self::SMS_ANSWER_URL),
                    WebToPayException::E_SMS_ANSWER);
            }

            $data = array(
                    'id'            => $answer['id'],
                    'msg'           => $answer['msg'],
                    'transaction'   => md5($answer['sign_password'].'|'.$answer['id']),
                );

            $query = $url['path'].'?'.http_build_query($data);

            $out = "GET " . $query . " HTTP/1.1\r\n";
            $out .= "Host: ".$url['host']."\r\n";
            $out .= "Connection: Close\r\n\r\n";

            $content = '';
            
            fwrite($fp, $out);
            while (!feof($fp)) $content .= fgets($fp, 8192);
            fclose($fp);
            
            list($header, $content) = explode("\r\n\r\n", $content, 2);

            $content = trim($content);
            if (strpos($content, 'OK') !== 0) {
                throw new WebToPayException(
                    self::_('Error: %s', $content),
                    WebToPayException::E_SMS_ANSWER);
            }
        }

        catch (WebToPayException $e) {
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



    protected $field_name = false;

    public function setField($field_name) {
        $this->field_name = $field_name;
    }

    public function getField() {
        return $this->field_name;
    }

}

