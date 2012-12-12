<?php

/**
 * Builds and signs requests
 */
class WebToPay_RequestBuilder {

    /**
     * @var string
     */
    protected $projectPassword;

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * @var integer
     */
    protected $projectId;

    /**
     * Constructs object
     *
     * @param integer       $projectId
     * @param string        $projectPassword
     * @param WebToPay_Util $util
     */
    public function __construct($projectId, $projectPassword, WebToPay_Util $util) {
        $this->projectId = $projectId;
        $this->projectPassword = $projectPassword;
        $this->util = $util;
    }

    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * @param  array $data information about current payment request
     *
     * @return array
     *
     * @throws WebToPayException
     */
    public function buildRequest($data) {
        $this->validateRequest($data, self::getRequestSpec());
        $data['version'] = WebToPay::VERSION;
        $data['projectid'] = $this->projectId;
        unset($data['repeat_request']);
        return $this->createRequest($data);
    }

    /**
     * Builds repeat request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException on failure.
     *
     * @param string $orderId order id of repeated request
     *
     * @return array
     *
     * @throws WebToPayException
     */
    public function buildRepeatRequest($orderId) {
        $data['orderid'] = $orderId;
        $data['version'] = WebToPay::VERSION;
        $data['projectid'] = $this->projectId;
        $data['repeat_request'] = '1';
        return $this->createRequest($data);
    }

    /**
     * Checks data to be valid by passed specification
     *
     * @param array $data
     * @param array $specs
     *
     * @throws WebToPay_Exception_Validation
     */
    protected function validateRequest($data, $specs) {
        foreach ($specs as $spec) {
            list($name, $maxlen, $required, $regexp) = $spec;
            if ($required && !isset($data[$name])) {
                throw new WebToPay_Exception_Validation(
                    sprintf("'%s' is required but missing.", $name),
                    WebToPayException::E_MISSING,
                    $name
                );
            }

            if (!empty($data[$name])) {
                if ($maxlen && strlen($data[$name]) > $maxlen) {
                    throw new WebToPay_Exception_Validation(sprintf(
                        "'%s' value is too long (%d), %d characters allowed.",
                        $name,
                        strlen($data[$name]),
                        $maxlen
                    ), WebToPayException::E_MAXLEN, $name);
                }

                if ($regexp !== ''  && !preg_match($regexp, $data[$name])) {
                    throw new WebToPay_Exception_Validation(
                        sprintf("'%s' value '%s' is invalid.", $name, $data[$name]),
                        WebToPayException::E_REGEXP,
                        $name
                    );
                }
            }
        }
    }

    /**
     * Makes request data array from parameters, also generates signature
     *
     * @param array $request
     *
     * @return array
     */
    protected function createRequest(array $request) {
        $data = $this->util->encodeSafeUrlBase64(http_build_query($request));
        return array(
            'data' => $data,
            'sign' => md5($data . $this->projectPassword),
        );
    }

    /**
     * Returns specification of fields for request.
     *
     * Array structure:
     *   name      – request item name
     *   maxlen    – max allowed value for item
     *   required  – is this item is required
     *   regexp    – regexp to test item value
     *
     * @return array
     */
    protected static function getRequestSpec() {
        return array(
            array('orderid',       40,  true,  ''),
            array('accepturl',     255, true,  ''),
            array('cancelurl',     255, true,  ''),
            array('callbackurl',   255, true,  ''),
            array('lang',          3,   false, '/^[a-z]{3}$/i'),
            array('amount',        11,  false, '/^\d+$/'),
            array('currency',      3,   false, '/^[a-z]{3}$/i'),
            array('payment',       20,  false, ''),
            array('country',       2,   false, '/^[a-z_]{2}$/i'),
            array('paytext',       255, false, ''),
            array('p_firstname',   255, false, ''),
            array('p_lastname',    255, false, ''),
            array('p_email',       255, false, ''),
            array('p_street',      255, false, ''),
            array('p_city',        255, false, ''),
            array('p_state',       20,  false, ''),
            array('p_zip',         20,  false, ''),
            array('p_countrycode', 2,   false, '/^[a-z]{2}$/i'),
            array('test',          1,   false, '/^[01]$/'),
            array('time_limit',    19,  false, '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'),
        );
    }
}