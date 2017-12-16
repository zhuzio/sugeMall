<?php
$protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
echo $protocol."\n";echo $_SERVER['HTTPS']."9999999999\n";
?>
