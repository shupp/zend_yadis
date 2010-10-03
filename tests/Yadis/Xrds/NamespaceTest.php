<?php

require_once 'Services/Yadis/Xrds/Namespace.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Services_Yadis_Xrds_NamespaceTest extends PHPUnit_Framework_TestCase
{

    public function testInitialState()
    {
        $name = new Services_Yadis_Xrds_Namespace;
        $this->assertEquals(array('xrds' => 'xri://$xrds','xrd' => 'xri://$xrd*($v*2.0)'), $name->getNamespaces());
    }

    public function testAddNamespace()
    {
        $name = new Services_Yadis_Xrds_Namespace;
        $name->addNamespace('test', 'http://example.com/test');
        $this->assertEquals('http://example.com/test', $name->getNamespace('test'));
    }

    public function testAddNamespaces()
    {
        $initial = array(
            'xrds' => 'xri://$xrds',
            'xrd' => 'xri://$xrd*($v*2.0)'
        );
        $spaces = array(
           'test'=>'http://example.com/test',
           'test2'=>'http://example.com/test'
        );
        $name = new Services_Yadis_Xrds_Namespace;
        $name->addNamespaces($spaces);
        $this->assertEquals($initial + $spaces, $name->getNamespaces());
    }

    // tests that if provider changes namespaces, our code's XPath can still
    // substitute the prior prefix
    public function testRegisterXpathNamespaces()
    {
        $string = <<<XML
<a xmlns:t2="http://example.com/t">
 <b>
  <t2:c>text</t2:c>
 </b>
</a>
XML;
        $xml = new SimpleXMLElement($string);
        $name = new Services_Yadis_Xrds_Namespace;
        $name->addNamespace('t', 'http://example.com/t');
        $name->registerXpathNamespaces($xml);
        $c = $xml->xpath('//t:c');
        $this->assertEquals('text', (string) $c[0]);
    }

}