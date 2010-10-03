<?php

require_once 'Zend/OpenId/Consumer.php';

$consumer = new Zend_OpenId_Consumer();
if (!$consumer->login($_POST['openid_identifier'], 'example-1_3.php')) {
    die("OpenID login failed.");
}
