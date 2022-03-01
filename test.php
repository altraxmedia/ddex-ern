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

$cover = new Artwork;
$cover->proprietaryId = 'qwertyuiopasdfghjklzxcvbnm';

$ddex->release->releaseCoverArt = $cover;

for ($i = 0; $i < 5; ++$i)
{
	$track = new Track;
	$track->trackDeal[] = md5 (time () . random_int (0, 928492) . random_bytes (64)) . '_aboba';
	$ddex->release->releaseTracks[] = $track;
}

file_put_contents ('batch.xml', $ddex);
