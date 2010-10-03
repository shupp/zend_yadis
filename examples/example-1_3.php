<?php

require_once 'Zend/OpenId/Consumer.php';

$consumer = new Zend_OpenId_Consumer();
if ($consumer->verify($_GET, $id)) {
    echo "VALID " . htmlspecialchars($id);
} else {
    echo "INVALID " . htmlspecialchars($id);
}
?>
