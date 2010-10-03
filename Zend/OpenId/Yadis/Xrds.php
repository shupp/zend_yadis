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
 * @category   Zend
 * @package    Zend_OpenId_Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Id: Xrds.php 290041 2009-10-29 05:46:30Z shupp $
 */

/** Zend_OpenId_Yadis_Xrds_Namespace */
require_once 'Zend/OpenId/Yadis/Xrds/Namespace.php';

/**
 * The Zend_OpenId_Yadis_Xrds class is a wrapper for elements of an
 * XRD document which is parsed using SimpleXML, and contains methods for
 * retrieving data about the document. The concrete aspects of retrieving
 * specific data elements is left to a concrete subclass.
 *
 * @category   Zend
 * @package    Zend_OpenId_Yadis
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 */
class Zend_OpenId_Yadis_Xrds
{
 
    /**
     * Current key/pointer for the Iterator
     * 
     * @var integer
     */ 
    protected $_currentKey = 0;

    /**
     * Contains the valid xrd:XRD nodes parsed from the XRD document.
     *
     * @var SimpleXMLElement
     */
    protected $_xrdNodes = null;

    /**
     * Instance of Zend_OpenId_Yadis_Xrds_Namespace for managing namespaces
     * associated with an XRDS document.
     *
     * @var Zend_OpenId_Yadis_Xrds_Namespace
     */
    protected $_namespace = null;
 
    /**
     * Constructor; parses and validates an XRD document. All access to
     * the data held in the XML is left to a concrete subclass specific to
     * expected XRD format and data types.
     * Cannot be directly instantiated; must call from subclass.
     * 
     * @param   SimpleXMLElement $xrds
     * @param   Zend_OpenId_Yadis_Xrds_Namespace $namespace
     */ 
    protected function __construct(SimpleXMLElement $xrds, Zend_OpenId_Yadis_Xrds_Namespace $namespace)
    {
        $this->_namespace = $namespace;
        $xrdNodes = $this->_getValidXrdNodes($xrds);
        if (!$xrdNodes) {
            require_once 'Zend/OpenId/Yadis/Exception.php';
            throw new Zend_OpenId_Yadis_Exception('The XRD document was found to be invalid');
        }
        $this->_xrdNodes = $xrdNodes;
    }
 
    /**
     * Add a list (array) of additional namespaces to be utilised by the XML
     * parser when it receives a valid XRD document.
     *
     * @param   array $namespaces
     * @return  Zend_OpenId_Yadis
     */
    public function addNamespaces(array $namespaces)
    {
        $this->_namespace->addNamespaces($namespaces);
        return $this;
    }

    /**
     * Add a single namespace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param   string $namespace
     * @param   string $namespaceUrl
     * @return  Zend_OpenId_Yadis
     */
    public function addNamespace($namespace, $namespaceUrl)
    {
        $this->_namespace->addNamespace($namespace, $namespaceUrl);
        return $this;
    }

    /**
     * Return the value of a specific namespace.
     *
     * @return string|boolean
     */
    public function getNamespace($namespace)
    {
        return $this->_namespace->getNamespace($namespace);
    }

    /**
     * Returns an array of all currently set namespaces.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->_namespace->getNamespaces();
    }

    /**
     * Returns an array of all xrd elements located in the XRD document.
     *
     * @param SimpleXMLElement
     * @return array
     */
    protected function _getValidXrdNodes(SimpleXMLElement $xrds)
    {
        /**
         * Register all namespaces to this SimpleXMLElement.
         */
        $this->_namespace->registerXpathNamespaces($xrds);

        /**
         * Verify the XRDS resource has a root element called "xrds:XRDS".
         */
        $root = $xrds->xpath('/xrds:XRDS[1]');
        if (count($root) == 0) {
            return null;
        }

        /**
         * Check namespace urls of standard xmlns (no suffix) or xmlns:xrd
         * (if present and of priority) for validity.
         * No loss if neither exists, but they really should be.
         */
        $namespaces = $xrds->getDocNamespaces();
        if (array_key_exists('xrd', $namespaces) && $namespaces['xrd'] != 'xri://$xrd*($v*2.0)') {
            return null;
        } elseif (array_key_exists('', $namespaces) && $namespaces[''] != 'xri://$xrd*($v*2.0)') {
            // Hack for the namespace declaration in the XRD node, which SimpleXML misses
            $xrdHack = false;
            if (!isset($xrds->XRD)) {
                return null;
            }

            foreach ($xrds->XRD as $xrd) {
                $namespaces = $xrd->getNamespaces();
                if (array_key_exists('', $namespaces)
                    && $namespaces[''] == 'xri://$xrd*($v*2.0)') {

                    $xrdHack = true;
                    break;
                }
            }

            if (!$xrdHack) {
                return null;
            }
        }

        /**
         * Grab the XRD elements which contains details of the service provider's
         * Server url, service types, and other details. Concrete subclass may
         * have additional requirements concerning node priority or valid position
         * in relation to other nodes. E.g. Yadis requires only using the *last*
         * node.
         */
        $xrdNodes = $xrds->xpath('/xrds:XRDS[1]/xrd:XRD');
        if (!$xrdNodes) {
            return null;
        }
        return $xrdNodes;
    }

    /**
     * Order an array of elements by priority. This assumes an array form of:
     *      $array[$priority] = <array of elements with equal priority>
     * Where multiple elements are assigned to a priority, their order in the
     * priority array should be made random. After ordering, the array is
     * flattened to a single array of elements for iteration.
     *
     * @param   array $unsorted
     * @return  array
     */
    public static function sortByPriority(array $unsorted)
    {
        ksort($unsorted);
        $flattened = array();
        foreach ($unsorted as $priority) {
            if (count($priority) > 1) {
                shuffle($priority);
                $flattened = array_merge($flattened, $priority);
            } else {
                $flattened[] = $priority[0];
            } 
        }
        return $flattened;
    }

}
