<?php

use Glay\Network\SMTP;

header('Content-Type: text/plain');

ini_set('assert.exception', true);
mb_internal_encoding('utf-8');

$smtp = new SMTP();
$smtp->from('sender@mirari.fr', 'sender');
$smtp->add_to('recipient@mirari.fr', 'recipient');

assert($smtp->send('Hello, World! À³©', "Body.\n\nÀ³©"));

echo 'OK';
