<?php
declare(strict_types=1);
namespace OnPay\API;


use function GuzzleHttp\Psr7\str;

class PaymentWindow
{
    const METHOD_CARD = 'card';
    const METHOD_MOBILEPAY = 'mobilepay';

    private $gatewayId;
    private $currency;
    private $amount;
    private $reference;
    private $acceptUrl;
    private $type;
    private $method;
    private $secureEnabled;
    private $language;
    private $declineUrl;
    private $callbackUrl;
    private $design;
    private $testMode;
    private $secret;
    private $availableFields;
    private $requiredFields;
    private $actionUrl = "https://onpay.io/window/v3/";

    /**
     * PaymentWindow constructor.
     */
    public function __construct()
    {
        $this->availableFields = [
            "gatewayId",
            "currency",
            "amount",
            "reference",
            "acceptUrl",
            "type",
            "secureEnabled",
            "language",
            "declineUrl",
            "callbackUrl",
            "design",
            "testMode",
            "method"
        ];

        $this->requiredFields = [
            "gatewayId",
            "currency",
            "amount",
            "reference",
            "acceptUrl",
        ];
    }

    /**
     * @param string $gatewayId
     */
    public function setGatewayId($gatewayId): void
    {
        $this->gatewayId = $gatewayId;
    }

    /**
     * @return string
     */
    public function getGatewayId() {
        return $this->gatewayId;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * @param string $acceptUrl
     */
    public function setAcceptUrl(string $acceptUrl): void
    {
        $this->acceptUrl = $acceptUrl;
    }

    /**
     * @return string
     */
    public function getAcceptUrl() {
        return $this->acceptUrl;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param bool $secureEnabled
     */
    public function setSecureEnabled(bool $secureEnabled): void
    {
        if($secureEnabled) {
            $this->secureEnabled = "force";
        } else {
            $secureEnabled = null;
        }
    }

    /**
     * @return bool
     */
    public function hasSecureEnabled() {
        if($this->secureEnabled === "force") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language): void
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param mixed $declineUrl
     */
    public function setDeclineUrl($declineUrl): void
    {
        $this->declineUrl = $declineUrl;
    }

    /**
     * @return string
     */
    public function getDeclineUrl() {
        return $this->declineUrl;
    }

    /**
     * @param mixed $callbackUrl
     */
    public function setCallbackUrl($callbackUrl): void
    {
        $this->callbackUrl = $callbackUrl;
    }

    public function getCallbackUrl() {
        return $this->callbackUrl;
    }

    /**
     * @param mixed $design
     */
    public function setDesign($design): void
    {
        $this->design = $design;
    }

    /**
     * @return string
     */
    public function getDesign() {
        return $this->design;
    }

    /**
     * @param mixed $testMode
     */
    public function setTestMode($testMode): void
    {
        $this->testMode = $testMode;
    }

    /**
     * @return mixed
     */
    public function getTestMode() {
        return $this->testMode;
    }

    /**
     * @param mixed $secret
     */
    public function setSecret($secret): void
    {
        $this->secret = $secret;
    }

    public function getSecret() {
        return $this->secret;
    }

    /**
     * Generates hmac secret
     * @return string
     */
    public function generateSecret() {

        $fields = $this->getAvailableFields();
        $queryString = strtolower(http_build_query($fields));
        $hmac = hash_hmac('sha1', $queryString, $this->secret);
        return $hmac;
    }

    /**
     * Gets all filled fields
     * @return array
     */
    private function getAvailableFields() {

        $fields = [];

        foreach ($this->availableFields as $field) {
            if(property_exists($this, $field) && null !== $this->{$field}) {
                $key = 'onpay_' . strtolower($field);
                $fields[$key] = $this->{$field};
            }
        }

        ksort($fields);
        return $fields;
    }

    /**
     * Get fields for form
     * @return array
     */
    public function getFormFields() {

        $fields = $this->getAvailableFields();
        $fields['onpay_hmac_sha1'] = $this->generateSecret();
        return $fields;
    }

    /**
     * Checks if the PaymentWindow has the required fields to do a payment
     */
    public function isValid() {

        foreach ($this->requiredFields as $field) {
            if(property_exists($this, $field) && null === $this->{$field}) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns URL to post to
     * @return string
     */
    public function getActionUrl() {
        return $this->actionUrl;
    }


    /**
     * Validate payment
     * @param array $fields
     * @return bool
     */
    public function validatePayment(array $fields) {

        $validFields = [];

        foreach ($fields as $key => $value) {
            if(strpos($key, 'onpay') !== false) {
                $validFields[$key] = $value;
            }
        }

        $verify = $validFields['onpay_hmac_sha1'];

        unset($validFields['onpay_hmac_sha1']);

        ksort($validFields);

        $queryString = http_build_query($validFields);
        $hmac = hash_hmac('sha1', $queryString, $this->secret);

        if($verify === $hmac) {
            return true;
        }

        return false;
    }
}
