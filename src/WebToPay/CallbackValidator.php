<?php

declare(strict_types=1);

/**
 * Parses and validates callbacks
 */
class WebToPay_CallbackValidator
{
    protected WebToPay_Sign_SignCheckerInterface $signer;

    protected WebToPay_Util $util;

    protected int $projectId;

    protected ?string $password;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param WebToPay_Sign_SignCheckerInterface $signer
     * @param WebToPay_Util $util
     * @param string|null $password
     */
    public function __construct(
        int $projectId,
        WebToPay_Sign_SignCheckerInterface $signer,
        WebToPay_Util $util,
        ?string $password = null
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
     * @param array<string, string> $requestData
     *
     * @return array<string, string> Parsed callback parameters
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     */
    public function validateAndParseData(array $requestData): array
    {
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

        if (!isset($request['type']) || !in_array($request['type'], ['micro', 'macro'], true)) {
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
     * @param array<string, string> $data
     * @param array<string, string> $expected
     *
     * @throws WebToPayException
     */
    public function checkExpectedFields(array $data, array $expected): void
    {
        foreach ($expected as $key => $value) {
            $passedValue = $data[$key] ?? null;
            if ($passedValue !== $value) {
                throw new WebToPayException(
                    sprintf('Field %s is not as expected (expected %s, got %s)', $key, $value, $passedValue)
                );
            }
        }
    }
}
