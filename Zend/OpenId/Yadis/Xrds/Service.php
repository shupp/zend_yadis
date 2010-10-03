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
 * @version    $Id: Service.php 290041 2009-10-29 05:46:30Z shupp $
 */

/** Zend_OpenId_Yadis_Xrds */
require_once 'Zend/OpenId/Yadis/Xrds.php';

/** Zend_OpenId_Yadis_Service */
require_once 'Zend/OpenId/Yadis/Service.php';

/**
 * The Zend_OpenId_Yadis_Xrds_Service class is a wrapper for Service elements
 * of an XRD document which is parsed using SimpleXML, and contains methods for
 * retrieving data about each Service, including Type, Url and other arbitrary
 * data added in a separate namespace, e.g. openid:Delegate for OpenID 1.1.
 *
 * This class extends the basic Zend_OpenId_Yadis_Xrds wrapper to implement a
 * Service object specific to the Yadis Specification 1.0. XRDS itself is not
 * an XML format ruled by Yadis, but by an OASIS proposal.
 *
 * @category   Zend
 * @package    Zend_OpenId_Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 */
class Zend_OpenId_Yadis_Xrds_Service extends Zend_OpenId_Yadis_Xrds implements Iterator
{

    /**
     * Establish a lowest priority integer; we'll take the upper 2^31
     * integer limit.
     * Highest priority is 0.
     */
    const SERVICE_LOWEST_PRIORITY = 2147483647;

    /**
     * Holds the last XRD node of the XRD document as required by Yadis 1.0.
     *
     * @var SimpleXMLElement
     */
    protected $_xrdNode = null;
    
    /**
     * The Yadis Services resultset
     *
     * @var array
     */ 
    protected $_services = array();

    /**
     * Flag holding whether or not the array endpoint has been reached.
     *
     * @var boolean
     */
    protected $_valid = true;

    /**
     * Constructor; Accepts an XRD document for parsing.
     * Parses the XRD document by <xrd:Service> element to construct an array
     * of Zend_OpenId_Yadis_Service objects ordered by their priority.
     *
     * @param   SimpleXMLElement $xrds
     * @param   Zend_OpenId_Yadis_Xrds_Namespace $namespace
     */
    public function __construct(SimpleXMLElement $xrds, Zend_OpenId_Yadis_Xrds_Namespace $namespace)
    {
        parent::__construct($xrds, $namespace);
        /**
         * The Yadis Specification requires we only use the last xrd node. The
         * rest being ignored (if present for whatever reason). Important to
         * note when writing an XRD document for multiple services - put
         * the authentication service XRD node last.
         */
        $this->_xrdNode = $this->_xrdNodes[count($this->_xrdNodes) - 1];
        $this->_namespace->registerXpathNamespaces($this->_xrdNode);
        $services = $this->_xrdNode->xpath('xrd:Service');
        foreach ($services as $service) {
            $serviceObj = new Zend_OpenId_Yadis_Service($service, $this->_namespace);
            $this->_addService($serviceObj);
        }
        $this->_services = Zend_OpenId_Yadis_Xrds::sortByPriority($this->_services);
    }

    /**
     * Implements Iterator::current()
     * 
     * Return the current element.
     *
     * @return Zend_OpenId_Yadis_Service
     */ 
    public function current()
    {
         return current($this->_services);
    }
 
    /**
     * Implements Iterator::key()
     *
     * Return the key of the current element.
     * 
     * @return integer
     */ 
    public function key()
    {
         return key($this->_services);
    }
 
    /**
     * Implements Iterator::next()
     * 
     * Increments pointer to next Service object.
     *
     * @return void
     */ 
    public function next()
    {
         $this->_valid = (false !== next($this->_services));
    }
 
    /**
     * Implements Iterator::rewind()
     * 
     * Rewinds the Iterator to the first Service object
     *
     * @return boolean
     */ 
    public function rewind()
    {
        $this->_valid = (false !== reset($this->_services)); 
    }
 
    /**
     * Implement Iterator::valid()
     *
     * @return boolean
     */ 
    public function valid()
    {
         return $this->_valid;
    }

    /**
     * Add a service to the Service list indexed by priority. Assumes
     * a missing or invalid priority should be shuffled to the bottom
     * of the priority order.
     *
     * @param Zend_OpenId_Yadis_Service $service
     */
    protected function _addService(Zend_OpenId_Yadis_Service $service)
    {
        $servicePriority = $service->getPriority();
        if(is_null($servicePriority) || !is_numeric($servicePriority)) {
            $servicePriority = self::SERVICE_LOWEST_PRIORITY;
        }
        if (!array_key_exists($servicePriority, $this->_services)){
            $this->_services[$servicePriority] = array();
        }
        $this->_services[$servicePriority][] = $service;
    }

}
