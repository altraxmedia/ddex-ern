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

$bebra = new Artist;
$bebra->artistName[] = new ArtistLanguage;
$bebra->artistName[0]->artistName = 'MC Bebra';
$bebra->artistName[0]->artistLanguage = 'en';
$bebra->artistRole = 26;

$bebra1 = new Contributor;
$bebra1->artistName[] = new ContributorLanguage;
$bebra1->artistName[0]->artistName = 'MC Bebra';
$bebra1->artistName[0]->artistLanguage = 'en';
$bebra1->artistRole = 4;

$bebra2 = new IndirectContributor;
$bebra2->artistName[] = new IndirectContributorLanguage;
$bebra2->artistName[0]->artistName = 'MC Bebra';
$bebra2->artistName[0]->artistLanguage = 'en';
$bebra2->artistRole = 4;

for ($i = 0; $i < 5; ++$i)
{
	$track = new Track;
	$deal = new Deal;
	$deal->dealTerritories[] = 'Worldwide';
	$track->trackDeal[] = $deal;
	$track->trackArtists[] = $bebra;
	$track->trackContributors[] = $bebra1;
	$track->trackIndirectContributors[] = $bebra2;
	$ddex->release->releaseTracks[] = $track;
}

file_put_contents ('batch.xml', $ddex->gen ());
