<?php

require_once __DIR__ . '/DDEX-ERN.php';

$sender = new Party;
$recipient = new Party;
$sentOnBehalfOf = new Party;

$sender->partyId = 'aboba';
$recipient->partyId = 'amogus';

$sender->partyName = 'when the impostor';
$recipient->partyName = 'is sus';

$sentOnBehalfOf->partyId = 'when the impostor';
$sentOnBehalfOf->partyName = 'is sus';

$ddex = new DDEX ('ru', $sender, $recipient);

$ddex->release->sentOnBehalfOf = $sentOnBehalfOf;

file_put_contents ('batch.xml', $ddex);
