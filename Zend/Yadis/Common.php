<?php
/**
 * Common base class for for Zend_Yadis and Zend_Yadis_Xri
 *
 * @category Zend
 * @package  Zend_Yadis
 * @author   Pádraic Brady <padraic.brady@yahoo.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/shupp/zend_yadis
 */

/**
 * Common base class for for Zend_Yadis and Zend_Yadis_Xri
 *
 * @category Zend
 * @package  Zend_Yadis
 * @author   Pádraic Brady <padraic.brady@yahoo.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/shupp/zend_yadis
 */
abstract class Zend_Yadis_Common
{
    /*
     * Array of characters which if found at the 0 index of a Yadis ID string
     * may indicate the use of an XRI.
     *
     * @var array
     */
    protected $xriIdentifiers = array(
        '=', '$', '!', '@', '+'
    );

    /**
     * The Zend_Config or array of options for Zend_Client_Http
     * 
     * @var array
     */
    protected $httpClientConfig = array();

    /**
     * Zend_Http_Client object utilised by this class if externally set
     *
     * @var Zend_Http_Client
     */
    protected $httpClient = null;

    /**
     * Holds the response received during Service Discovery.
     *
     * @var Zend_Http_Response
     */
    protected $httpResponse = null;


    /**
     * Set options to be passed to the Zend_Http_Client constructor
     *
     * @param Zend_Config|array $options Options for Zend_Http_Client
     *
     * @return Zend_Yadis
     */
    public function setHttpClientConfig($options)
    {
        $this->httpClientConfig = $options;
        return $this;
    }

    /**
     * Get options to be passed to the Zend_Http_Client constructor
     *
     * @return Zend_Config|array
     */
    public function getHttpClientConfig()
    {
        return $this->httpClientConfig;
    }

    /**
     * Return the final HTTP response
     *
     * @return Zend_Http_Response
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * Setter for custom Zend_Http_Client type object
     *
     * @param Zend_Http_Client $client Instance of Zend_Http_Client
     *
     * @return Zend_Yadis_Common
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Gets the Zend_Http_Client object
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Zend_Http_Client();
            $this->httpClient->setConfig($this->getHttpClientConfig());
        }
        return $this->httpClient;
    }

    /**
     * Validates an HTTP URI
     * 
     * @param string $uri The URI to validate
     * 
     * @return bool
     */
    static public function validateUri($uri)
    {
        try {
            $object = Zend_Uri_Http::fromString($uri);
            return true;
        } catch (Zend_Uri_Exception $e) {
            return false;
        }
    }

    // @codeCoverageIgnoreStart
    /**
     * Sends the request via Zend_Http_Client.  Abstracted for testing.
     * 
     * @return Zend_Http_Response
     */
    protected function _sendRequest()
    {
        return $this->getHttpClient()->request();
    }
    // @codeCoverageIgnoreEnd
}
