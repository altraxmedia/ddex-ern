<?php

/*
    A library to generate DDEX ERN XML files from pre-defined PHP classes

    Format: DDEX ERN 3.8.2 (Standart).

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.

    This library licensed under the MIT License.

    Copyright (c) 2022 Georgy Akhmetov, Serhii Shmaida, Saveliy Safonov, Al-Trax Media Limited
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
    (string no/yes/clean/noinfo/unk) releaseExplicit: is release explicit or not
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
    public $releaseICPN = "";
    public $releaseICPNIsEan = true;
    public $releaseCatalogNo = "";
    public $releaseProprietaryId = '';
    public $releaseGenre = '';
    public $releaseArtists = [];
    public $releaseExplicit = '';
    public $releasePLineYear = 2022;
    public $releasePLine = "";
    public $releaseCLineYear = 2022;
    public $releaseCLine = "";
    public $releaseDeal = [];
    public $releaseType = 'Album';
    public $releaseDisplayArtist = '';
    public $releaseNoData = false;

    public function getExplicit ()
    {
        switch ($this->releaseExplicit)
        {
            case 'no':
                return 'NotExplicit';
            break;

            case 'yes':
                return 'Explicit';
            break;

            case 'clean':
                return 'ExplicitContentEdited';
            break;

            case 'noinfo':
                return 'NoAdviceAvailable';
            break;

            case 'unk':
                return 'Unknown';
            break;
        }
    }
}

/*
    CLASS: Artist

    CLASS MEMBERS:
    
    (string) artistName: name of the artist
    (integer) artistRole: Role ID (0 - Main Artist, 1 - Feat, 2 - Artist)
*/

class Artist
{
    public $artistName = [];
    public $artistRole = 0;

    public function getRole ()
    {
        switch ($this->artistRole)
        {
            case 0:
                return 'MainArtist';
            break;

            case 1:
                return 'FeaturedArtist';
            break;

            case 2:
                return 'Artist';
            break;
        }
    }

}

class ArtistLanguage
{
    public $artistName;
    public $artistLanguage;
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
    (string no/yes/clean/noinfo/unk) trackExplicit: track is explicit
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
    public $trackIndirectContributors = [];
    public $trackExplicit = 'no';
    public $trackPreviewTime = 30;
    public $trackSHA1hashsum = "";
    public $trackFilename = "";
    public $trackPLineYear = 2022;
    public $trackPLine = "";
    public $trackCLineYear = 2022;
    public $trackCLine = "";
    public $trackTitleLang = 'en';
    public $trackGenre = '';
    public $trackCodecType = 'PCM';
    public $trackIsPreview = false;
    public $filepath = '';
    public $filename = '';
    public $hash;
    public $hashType = 'SHA1';
    public $actualFilePath = '';
    public $trackProprietaryID = '';
    public $trackDisplayArtist = '';
    public $trackReleaseDate = '';
    public $trackOriginalReleaseDate = '';
    public $trackKeywords = '';
    public $trackDeal = [];
    public $trackMediaEnrichment = null;

    public function getExplicit ()
    {
        switch ($this->trackExplicit)
        {
            case 'no':
                return 'NotExplicit';
            break;

            case 'yes':
                return 'Explicit';
            break;

            case 'clean':
                return 'ExplicitContentEdited';
            break;

            case 'noinfo':
                return 'NoAdviceAvailable';
            break;

            case 'unk':
                return 'Unknown';
            break;
        }
    }
}

class Artwork
{
    public $width = 1080;
    public $height = 1080;
    public $filepath = '';
    public $filename = '';
    public $hash;
    public $hashType = 'SHA1';
    public $actualFilePath = '';
    public $proprietaryId;
    public $imageCodec = 'JPEG';
}

/*
    CLASS: Contributor

    CLASS MEMBERS:

    (string) contributorName: the contributor's name
    (string) contributorRole: the contributor's role in all this release
*/

class Contributor
{
    public $artistName = [];
    public $artistRole = '';

}

class ContributorLanguage
{
    public $artistName;
    public $artistLanguage;
}

class IndirectContributor
{
    public $artistName = [];
    public $artistRole = '';

}

class IndirectContributorLanguage
{
    public $artistName;
    public $artistLanguage;
}

class MEAD
{
    public $IsMedley;
    public $IsPotpourri;
    public $IsInstrumental;
    public $IsBackground;
    public $IsHiddenResource;
    public $IsBonusResource;
    public $IsComputerGenerated;
    public $IsRemastered;
    public $NoSilenceBefore;
    public $NoSilenceAfter;

    public $LanguageOfPerformance = '';

    public $CreationDate = '';
    public $MasteredDate = '';
    public $RemasteredDate = '';
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
    public $subscriptionAvailable = true;
    public $adSupportAvailable = true;
    public $payAsYouGoAvailable = true;
    public $onDemandStream = true;
    public $permanentDownload = true;
    public $takedown = false;
}
