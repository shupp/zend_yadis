<?php
/**
 * Implementation of the Yadis Specification 1.0 protocol for service
 * discovery from an Identity URI/XRI or other.
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Copyright (c) 2007 Pádraic Brady <padraic.brady@yahoo.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the 
 *      documentation and/or other materials provided with the distribution.
 *    * The name of the author may not be used to endorse or promote products 
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Zend
 * @package  Zend_Yadis
 * @author   Pádraic Brady <padraic.brady@yahoo.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/services_yadis
 */

/** Zend_Yadis */
require_once 'Zend/Yadis.php';

/** Zend_Yadis_Exception */
require_once 'Zend/Yadis/Exception.php';

/**
 * The Zend_Yadis_Xrds_Namespace class is a container for namespaces
 * which need to be registered to an XML parser in order to correctly consume
 * an XRDS document using the parser's XPath functionality.
 *
 * @category Zend
 * @package  Zend_Yadis
 * @author   Pádraic Brady <padraic.brady@yahoo.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/services_yadis
 */
class Zend_Yadis_Xrds_Namespace
{

    /**
     * Default XRDS namespaces which should always be registered.
     *
     * @var array
     */
    protected $namespaces = array(
        'xrds' => 'xri://$xrds',
        'xrd'  => 'xri://$xrd*($v*2.0)'
    );

    /**
     * Add a list (array) of additional namespaces to be utilised by the XML
     * parser when it receives a valid XRD document.
     *
     * @param array $namespaces Array of namespaces to add
     *
     * @return  void
     */
    public function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespaceKey=>$namespaceUrl) {
            $this->addNamespace($namespaceKey, $namespaceUrl);
        }
    }

    /**
     * Add a single namespace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param string $namespaceKey Namespace key
     * @param string $namespaceUrl Namepspace URL
     *
     * @return  void
     */
    public function addNamespace($namespaceKey, $namespaceUrl)
    {
        if (!isset($namespaceKey) || !isset($namespaceUrl)
                   || empty($namespaceKey) || empty($namespaceUrl)) {

            throw new Zend_Yadis_Exception(
                'Parameters must be non-empty strings'
            );
        } elseif (!Zend_Yadis::validateUri($namespaceUrl)) {
            throw new Zend_Yadis_Exception(
                'Invalid namespace URI: '
                . htmlentities($namespaceUrl, ENT_QUOTES, 'utf-8')
            );
        } elseif (array_key_exists($namespaceKey, $this->getNamespaces())) {
            throw new Zend_Yadis_Exception(
                'You may not redefine the "xrds" or "xrd" XML Namespaces'
            ); 
        }

        $this->namespaces[$namespaceKey] = $namespaceUrl;
    }

    /**
     * Return the value of a specific namespace, or FALSE if not found.
     *
     * @param string $namespaceKey Namespace key
     *
     * @return string|boolean
     */
    public function getNamespace($namespaceKey)
    {
        if (array_key_exists($namespaceKey, $this->namespaces)) {
            return $this->namespaces[$namespaceKey];
        }
        return false;
    }

    /**
     * Returns an array of all currently set namespaces.
     *
     * @return  array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Register all stored namespaces to the parameter SimpleXMLElement object.
     *
     * @param SimpleXMLElement $element Instance of SimpleXMLElement
     *
     * @return void
     */
    public function registerXpathNamespaces(SimpleXMLElement $element)
    {
        foreach ($this->namespaces as $namespaceKey => $namespaceUrl) {
            $element->registerXPathNamespace($namespaceKey, $namespaceUrl);
        }
    }
}
?>
