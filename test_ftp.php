<?php

require_once __DIR__ . '/DDEX-ERN.php';
require_once __DIR__ . '/DDEX-Settings.php';
require_once __DIR__ . '/DDEX-FTP.php';

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
$ddex->release->releaseNoData = true;

$cover = new Artwork;
$cover->proprietaryId = 'qwertyuiopasdfghjklzxcvbnm';

$ddex->release->releaseCoverArt = $cover;

$bebra = new Artist;
$bebra->artistName[] = new ArtistLanguage;
$bebra->artistName[0]->artistName = 'MC Bebra';
$bebra->artistName[0]->artistRole = 0;

$bebra1 = new Contributor;
$bebra1->artistName[] = new ContributorLanguage;
$bebra1->artistName[0]->artistName = 'MC Bebra';
$bebra1->artistName[0]->artistRole = 'Composer';

$bebra2 = new IndirectContributor;
$bebra2->artistName[] = new IndirectContributorLanguage;
$bebra2->artistName[0]->artistName = 'MC Bebra';
$bebra2->artistName[0]->artistRole = 'Composer';

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

$credentials = new UploaderSettings;
$credentials->port = 21;
$credentials->login = 'abobusddex';
$credentials->password = '123456amogus';
$credentials->serverIp = 'localhost';
$credentials->confirmationFile = new MCMSConfirmationFile;

$ftp = new DDEXFTP;
$ftp->ern = $ddex;
$ftp->uploadSettings = $credentials;

$ftp->uploadData ();
