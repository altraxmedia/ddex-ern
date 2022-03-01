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
	$deal = new Deal;
	$deal->dealTerritories[] = 'Worldwide';
	$track->trackDeal[] = $deal;
	$ddex->release->releaseTracks[] = $track;
}

file_put_contents ('batch.xml', $ddex);
