<?php
declare(strict_types=1);

namespace Bigcommerce\ORM\Client;

/**
 * Class AbstractConfig
 * @package Bigcommerce\ORM\Client
 */
abstract class AbstractConfig
{
    const API_BASE_URL = 'https://api.bigcommerce.com';
    const API_VERSION_V3 = "v3";
    const API_STORE_PREFIX_V3 = '/stores/%s/v3';
    const API_PATH_PREFIX_V3 = '/api/v3';
    const PAYMENT_BASE_URL = 'https://payments.bigcommerce.com';
    const PAYMENT_STORE_PREFIX = '/stores/%s';
    const RESOURCE_TYPE_API = 'api';
    const RESOURCE_TYPE_PAYMENT = 'payment';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_BCV1 = 'application/vnd.bc.v1+json';
    const CONTENT_TYPE_WWW = 'application/x-www-form-urlencoded';

    /** @var string */
    protected $apiVersion = self::API_VERSION_V3;

    /** @var string */
    protected $proxy;

    /** @var bool */
    protected $verify = false;

    /** @var float */
    protected $timeout = 60;

    /** @var string */
    protected $accept = self::CONTENT_TYPE_JSON;

    /** @var bool */
    protected $debug = false;

    /**
     * @return string
     */
    abstract public function getApiUrl();

    /**
     * @return string
     */
    abstract public function getPaymentUrl();

    /**
     * @return array|null
     */
    abstract public function getAuthHeaders();

    /**
     * @return array|null
     */
    abstract public function getAuth();

    /**
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @param string|null $proxy
     * @return \Bigcommerce\ORM\Client\AbstractConfig
     */
    public function setProxy(?string $proxy): AbstractConfig
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVerify()
    {
        return $this->verify;
    }

    /**
     * @param bool $verify
     * @return \Bigcommerce\ORM\Client\AbstractConfig
     */
    public function setVerify(?bool $verify): AbstractConfig
    {
        $this->verify = $verify;

        return $this;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param float|null $timeout
     * @return \Bigcommerce\ORM\Client\AbstractConfig
     */
    public function setTimeout(?float $timeout): AbstractConfig
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * @param string|null $accept
     * @return \Bigcommerce\ORM\Client\AbstractConfig
     */
    public function setAccept(?string $accept): AbstractConfig
    {
        /** Only accept json response */
        if ($accept != self::CONTENT_TYPE_JSON) {
            return $this;
        }

        $this->accept = $accept;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string|null $apiVersion
     * @return \Bigcommerce\ORM\Client\AbstractConfig
     */
    public function setApiVersion(?string $apiVersion): AbstractConfig
    {
        /** Only support API V3 */
        if (!in_array($apiVersion, [AbstractConfig::API_VERSION_V3])) {
            return $this;
        }

        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return \Bigcommerce\ORM\Client\AbstractConfig
     */
    public function setDebug(bool $debug): AbstractConfig
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathPrefix()
    {
        switch ($this->apiVersion) {
            case self::API_VERSION_V3:
            default:
                return self::API_PATH_PREFIX_V3;
        }
    }

    /**
     * @return string
     */
    public function getApiStorePrefix()
    {
        switch ($this->apiVersion) {
            case self::API_VERSION_V3:
            default:
                return self::API_STORE_PREFIX_V3;
        }
    }

    /**
     * @return string
     */
    public function getPaymentStorePrefix()
    {
        switch ($this->apiVersion) {
            case self::API_VERSION_V3:
            default:
                return self::PAYMENT_STORE_PREFIX;
        }
    }
}
