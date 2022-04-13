<?php

/**
 * Parses and validates callbacks
 */
class WebToPay_CallbackValidator {

    /**
     * @var WebToPay_Sign_SignCheckerInterface
     */
    protected $signer;

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * @var integer
     */
    protected $projectId;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param WebToPay_Sign_SignCheckerInterface $signer
     * @param WebToPay_Util $util
     * @param string|null $password
     */
    public function __construct(
        $projectId,
        WebToPay_Sign_SignCheckerInterface $signer,
        WebToPay_Util $util,
        $password = null
    ) {
        $this->signer = $signer;
        $this->util = $util;
        $this->projectId = $projectId;
        $this->password = $password;
    }

    /**
     * Parses callback parameters from query parameters and checks if sign is correct.
     * Request has parameter "data", which is signed and holds all callback parameters
     *
     * @param array $requestData
     *
     * @return array Parsed callback parameters
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     */
    public function validateAndParseData(array $requestData) {
        if (!isset($requestData['data'])) {
            throw new WebToPay_Exception_Callback('"data" parameter not found');
        }

        $data = $requestData['data'];

        if (isset($requestData['ss1']) || isset($requestData['ss2'])) {
            if (!$this->signer->checkSign($requestData)) {
                throw new WebToPay_Exception_Callback('Invalid sign parameters, check $_GET length limit');
            }

            $queryString = $this->util->decodeSafeUrlBase64($data);
        } else {
            if (null === $this->password) {
                throw new WebToPay_Exception_Configuration('You have to provide project password');
            }

            $queryString = $this->util->decryptGCM(
                $this->util->decodeSafeUrlBase64($data),
                $this->password
            );

            if (null === $queryString) {
                throw new WebToPay_Exception_Callback('Callback data decryption failed');
            }
        }
        $request = $this->util->parseHttpQuery($queryString);

        if (!isset($request['projectid'])) {
            throw new WebToPay_Exception_Callback(
                'Project ID not provided in callback',
                WebToPayException::E_INVALID
            );
        }

        if ((string) $request['projectid'] !== (string) $this->projectId) {
            throw new WebToPay_Exception_Callback(
                sprintf('Bad projectid: %s, should be: %s', $request['projectid'], $this->projectId),
                WebToPayException::E_INVALID
            );
        }

        if (!isset($request['type']) || !in_array($request['type'], array('micro', 'macro'))) {
            $micro = (
                isset($request['to'])
                && isset($request['from'])
                && isset($request['sms'])
            );
            $request['type'] = $micro ? 'micro' : 'macro';
        }

        return $request;
    }

    /**
     * Checks data to have all the same parameters provided in expected array
     *
     * @param array $data
     * @param array $expected
     *
     * @throws WebToPayException
     */
    public function checkExpectedFields(array $data, array $expected) {
        foreach ($expected as $key => $value) {
            $passedValue = isset($data[$key]) ? $data[$key] : null;
            if ($passedValue != $value) {
                throw new WebToPayException(
                    sprintf('Field %s is not as expected (expected %s, got %s)', $key, $value, $passedValue)
                );
            }
        }
    }
}
