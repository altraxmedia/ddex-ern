<?php

/*
    (c) 2022 Al-Trax Media Limited
    This software is a trade secret. Do not distribute!

    Written by Serhii Shmaida on 12.02.2022
*/

/*
    This file includes classes that is used while generating the ddex ern xml files.
    The resulting XML must be verified before sending to Merlin or delivery platform (ex. CI, FUGA or MCMS Yavin)
*/

/*
    CLASS: Party

    CLASS MEMBERS:
    (string) partyId: DPID
    (string) partyName: Legal name of company
    (string) tradingName: Trading name (in necessary)
*/

class Party
{
    public $partyId = '';
    public $partyName = '';
    public $tradingName = '';
}

/*
    CLASS: Release
    
    CLASS MEMBERS:

    (Party) sender: who sends the message
    (Party) sentOnBehalfOf: sent on behalf of (if necessary)
    (Party) recipient: recipient
    (string) DPID: your DDEX DPID
    (string) MessageSender: who sends the message (your company name)
    (string) releaseId: the id of the release (must be unique)
    (string) releaseThreadId: the thread id of the release (must be unique)
    (boolean) isBackfill: release is backfill
    (string) updateIndicator: is message original or update (OriginalMessage/UpdateMessage)
    (string) messageControl: message control type (for compability) (LiveMessage/TestMessage)
    (string) releaseTitle: the main title of the release 
    (string) releaseSubtitle: sub title
    (string) releaseCoverArt: path to the image; will be the cover art of the release. Must be the format supported by your store/delivery platform! (ex. JPG, 3000x3000)
    (array Track) releaseTracks: an array of Track classes.
    (string) releaseRecordLabel: the record label
    (string) releaseDate: the release date in DDEX supported format
    (string) releaseEAN: ICPN EAN of the release
    (string) releaseCatalogNo: the catalog number of the release
    (integer) releaseGenre: the ID of the genre supported by this library (please use the memo)
    (array Artist) releaseArtists: an array of artists and their roles
    (boolean) releaseExplicit: is release explicit or not
    (integer) releasePLineYear: p line year
    (string) releasePLine: p line (must start with a year!)
    (integer) releaseCLineYear: c line year
    (string) releaseCLine: c line (must start with a year!)
    (Deal) releaseDeal: the class that contain deal info
*/

class Release
{
    public $sender;
    public $sentOnBehalfOf;
    public $recipient;
    public $releaseId = "";
    public $releaseThreadId = "";
    public $isBackfill = false;
    public $updateIndicator = 'OriginalMessage';
    public $messageControl = 'LiveMessage';
    public $releaseTitle = "";
    public $releaseSubtitle = "";
    public $releaseCoverArt = "";
    public $releaseTracks = [];
    public $releaseRecordLabel = "";
    public $releaseDate = "";
    public $releaseEAN = "";
    public $releaseCatalogNo = "";
    public $releaseGenre = 0;
    public $releaseArtists = [];
    public $releaseExplicit = false;
    public $releasePLineYear = 2022;
    public $releasePLine = "";
    public $releaseCLineYear = 2022;
    public $releaseCLine = "";
    public $releaseDeal = null;
}

/*
    CLASS: Artist

    CLASS MEMBERS:
    
    (string) artistName: name of the artist
    (integer) artistRole: Role ID
*/

class Artist
{
    public $artistName = "";
    public $artistRole = 0;
}

/*
    CLASS: Track

    CLASS MEMBERS:

    (array Artist) trackArtists: the array of artists
    (string) trackISRC: the ISRC code of the track
    (string) trackTitle: the title of the track
    (string) trackSubtitle: the subtitle of the track
    (string) trackDuration: duration in DDEX compatible format
    (array Contributor) trackContributors: an array of track Contributors
    (string no/yes/clean/noinfo) trackExplicit: track is explicit
    (integer) trackPreviewTime: TikTok/Instagram/etc Preview Time
    (string) trackSHA1hashsum: SHA-1 hash sum
    (string) trackFilename: path to compatible sound file
    (integer) releasePLineYear: p line year
    (string) releasePLine: p line (must start with a year!)
    (integer) releaseCLineYear: c line year
    (string) releaseCLine: c line (must start with a year!)

*/

class Track
{
    public $trackArtists = [];
    public $trackISRC = "";
    public $trackTitle = "";
    public $trackSubtitle = "";
    public $trackDuration = "";
    public $trackContributors = [];
    public $trackExplicit = 'no';
    public $trackPreviewTime = 30;
    public $trackSHA1hashsum = "";
    public $trackFilename = "";
    public $trackPLineYear = 2022;
    public $trackPLine = "";
    public $trackCLineYear = 2022;
    public $trackCLine = "";
}

/*
    CLASS: Contributor

    CLASS MEMBERS:

    (string) contributorName: the contributor's name
    (integer) contributorRoleId: the contributor's role in all this release
*/

Class Contributor
{
    public $contributorName = "";
    public $contributorRoleId = 0;
}

/*
    CLASS: Deal
    
    CLASS MEMBERS:
    (string) dealStartDate: the start date of the release
    (string) dealPreOrderDate: the preorder date of the release
    (array string) dealTerritories: the country ISO code where the release will be available
*/

class Deal
{
    public $dealStartDate = "";
    public $dealPreOrderDate = "";
    public $dealTerritories = [];
}

/*
    MEMO: Genre IDs

0    Alternative
1    Alternative Rock
2    Alternativo & Rock Latino
3    Anime
4    Baladas y Boleros
5    Big Band
6    Blues
7    Brazilian
8    C-Pop
9    Cantopop/HK-Pop
10    Children's
11    Chinese
12    Christian
13    Classical
14    Comedy
15    Contemporary Latin
16    Country
17    Dance	
18    Easy Listening
19    Educational
20    Electronic
21    Enka
22    Experimental
23    Fitness & Workout
24    Folk
25    French Pop
26    German Folk
27    German Pop
28    Hip-Hop/Rap
29    Holiday
30    Indo Pop
31    Inspirational
32    Instrumental
33    J-Pop
34    Jazz
35    K-Pop
36    Karaoke
37    Kayokyoku
38    Latin
39    Latin Jazz
40    Metal
41    New Age
42    Opera
43    Original Pilipino Music
44    Pop
45    Pop Latino
46    Punk
47    R&B
48    Raíces
49    Reggae
50    Reggaeton y Hip-Hop
51    Regional Mexicano
52    Rock
53    Salsa y Tropical
54    Singer/Songwriter
55    Soul
56    Soundtrack
57    Spoken Word
58    Tai-Pop
59    Thai Pop
60    Trot
61    Vocal/Nostalgia
62    World

    MEMO: Artist Role IDs

0   Main Artist
1   Featured Artist
2   Artist

    MEMO: Contributor Role IDs

0   Lyricist
1   MusicPublisher
2   Remixer
3   Producer
4   Composer (required)

*/