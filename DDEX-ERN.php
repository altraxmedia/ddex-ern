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

require_once __DIR__ . '/DDEX-Release.php';

class DDEX
{
    public $release;
    protected $xml;
    public $baseEntrypoint;
    protected $language;
    public $resourceEntrypoint;
    public $releasesEntrypoint;
    public $dealsEntrypoint;
    protected $resourceReferences;
    protected $releaseReferences;
    protected $technicalReferences;

    protected function genGuid ()
    {
        return sprintf ('%04x%04x%04x%04x%04x%04x%04x%04x',
            random_int (0, 0xffff), random_int (0, 0xffff),
            random_int (0, 0xffff),
            random_int (0, 0x0fff) | 0x4000,
            random_int (0, 0x3fff) | 0x8000,
            random_int (0, 0xffff), random_int (0, 0xffff), random_int (0, 0xffff)
        );
    }

    public function __construct ($language, $sender, $recipient)
    {
        $this->release = new Release ();        
        $this->release->sender = $sender; 
        $this->release->recipient = $recipient; 
        $this->release->releaseId = $this->genGuid ();
        $this->release->releaseThreadId = $this->genGuid ();
        $this->language = $language;
    }

    protected function getSenderDPID ()
    {
        return $this->release->sender->partyId;
    }

    protected function initDom ()
    {
        $this->xml = new DOMDocument ('1.0', "utf-8");
        $this->xml->formatOutput = true;

        $this->baseEntrypoint = $this->xml->createElement ("ern:NewReleaseMessage");
        
        $this->baseEntrypoint->setAttribute ('xmlns:ern', 'http://ddex.net/xml/ern/382');
        $this->baseEntrypoint->setAttribute ('xmlns:xs', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->baseEntrypoint->setAttribute ('LanguageAndScriptCode', $this->language);
        $this->baseEntrypoint->setAttribute ('MessageSchemaVersionId', 'ern/382');
        $this->baseEntrypoint->setAttribute ('xs:schemaLocation', 'http://ddex.net/xml/ern/382 http://ddex.net/xml/ern/382/release-notification.xsd');

        $this->xml->appendChild ($this->baseEntrypoint);
    }

    public function writeHeader ()
    {
        $MessageHeader = $this->xml->createElement ("MessageHeader");
        $this->baseEntrypoint->appendChild ($MessageHeader);

        $MessageThreadId = $this->xml->createElement ("MessageThreadId", $this->release->releaseThreadId);
        $MessageHeader->appendChild ($MessageThreadId);

        $MessageId = $this->xml->createElement ("MessageId", $this->release->releaseId);
        $MessageHeader->appendChild ($MessageId);

        $MessageSender = $this->xml->createElement ("MessageSender");
        $MessageHeader->appendChild ($MessageSender);

        $PartyId = $this->xml->createElement ("PartyId", $this->release->sender->partyId);
        $MessageSender->appendChild ($PartyId);

        $PartyName = $this->xml->createElement ("PartyName", $this->release->sender->partyName);
        $MessageSender->appendChild ($PartyName);

        if ($this->release->sender->tradingName != '')
        {
            $TradingName = $this->xml->createElement ("TradingName", $this->release->sender->tradingName);
            $MessageSender->appendChild ($TradingName);
        }

        if ($this->release->sentOnBehalfOf instanceof Party)
        {
            $SentOnBehalfOf = $this->xml->createElement ("SentOnBehalfOf");
            $MessageHeader->appendChild ($SentOnBehalfOf);

            $PartyId = $this->xml->createElement ("PartyId", $this->release->sentOnBehalfOf->partyId);
            $SentOnBehalfOf->appendChild ($PartyId);

            $PartyName = $this->xml->createElement ("PartyName", $this->release->sentOnBehalfOf->partyName);
            $SentOnBehalfOf->appendChild ($PartyName);

            if ($this->release->sentOnBehalfOf->tradingName != '')
            {
                $TradingName = $this->xml->createElement ("TradingName", $this->release->sentOnBehalfOf->tradingName);
                $SentOnBehalfOf->appendChild ($TradingName);
            }
        }

        $MessageRecipient = $this->xml->createElement ("MessageRecipient");
        $MessageHeader->appendChild ($MessageRecipient);

        $PartyId = $this->xml->createElement ("PartyId", $this->release->recipient->partyId);
        $MessageRecipient->appendChild ($PartyId);

        $PartyName = $this->xml->createElement ("PartyName", $this->release->recipient->partyName);
        $MessageRecipient->appendChild ($PartyName);

        if ($this->release->recipient->tradingName != '')
        {
            $TradingName = $this->xml->createElement ("TradingName", $this->release->recipient->tradingName);
            $MessageRecipient->appendChild ($TradingName);
        }

        $MessageCreatedDateTime = $this->xml->createElement ("MessageCreatedDateTime", date (DATE_ATOM));
        $MessageHeader->appendChild ($MessageCreatedDateTime);

        $MessageControl = $this->xml->createElement ("MessageControlType", $this->release->messageControl);
        $MessageHeader->appendChild ($MessageControl);

        $UpdateIndicator = $this->xml->createElement ("UpdateIndicator", $this->release->updateIndicator);
        $this->baseEntrypoint->appendChild ($UpdateIndicator);

        $IsBackfill = $this->xml->createElement ("IsBackfill", $this->release->isBackfill ? 'true' : 'false');
        $this->baseEntrypoint->appendChild ($IsBackfill);
    }

    protected function initResourceEntrypoint ()
    {
        $this->resourceEntrypoint = $this->xml->createElement ("ResourceList");
        $this->baseEntrypoint->appendChild ($this->resourceEntrypoint);
    }

    protected function initReleasesEntrypoint ()
    {
        $this->releasesEntrypoint = $this->xml->createElement ("ReleaseList");
        $this->baseEntrypoint->appendChild ($this->releasesEntrypoint);
    }

    protected function initDealsEntrypoint ()
    {
        $this->dealsEntrypoint = $this->xml->createElement ("DealList");
        $this->baseEntrypoint->appendChild ($this->dealsEntrypoint);
    }

    protected function enumResourceRefs ()
    {
        $resourceReferences = [];
        
        for ($i = 0; $i <= count ($this->release->releaseTracks); ++$i)
            $resourceReferences[] = 'A' . $i;

        $resourceReferences[] = 'A' . $i;

        $this->resourceReferences = $resourceReferences;
    }

    protected function enumReleaseRefs ()
    {
        $releaseReferences = [];
        
        for ($i = 0; $i <= count ($this->release->releaseTracks); ++$i)
            $releaseReferences[] = 'R' . $i;

        $releaseReferences[] = 'R' . $i;

        $this->releaseReferences = $releaseReferences;
    }

    protected function enumTechnicalRefs ()
    {
        $technicalReferences = [];
        
        for ($i = 0; $i <= count ($this->release->releaseTracks); ++$i)
            $technicalReferences[] = 'T' . $i;

        $technicalReferences[] = 'T' . $i;

        $this->technicalReferences = $technicalReferences;

    }

    protected function soundRecordings ()
    {
        $pos = -1;
        foreach ($this->release->releaseTracks as $trackData)
        {
            ++$pos;
            
            $SoundRecording = $this->xml->createElement ("SoundRecording");
            $this->resourceEntrypoint->appendChild ($SoundRecording);

            $SoundRecordingType = $this->xml->createElement ("SoundRecordingType", "MusicalWorkSoundRecording");
            $SoundRecording->appendChild ($SoundRecordingType);

            $SoundRecordingId = $this->xml->createElement ("SoundRecordingId");
            $SoundRecording->appendChild ($SoundRecordingId);

            # ISRC

            $ISRC = $this->xml->createElement ("ISRC", $trackData->trackISRC);
            $SoundRecordingId->appendChild ($ISRC);

            $ResourceReference = $this->xml->createElement ("ResourceReference", $this->resourceReferences[$pos]);
            $SoundRecording->appendChild ($ResourceReference);

            # Title

            $ReferenceTitle = $this->xml->createElement ("ReferenceTitle");
            $SoundRecording->appendChild ($ReferenceTitle);

            $TitleText = $this->xml->createElement ("TitleText", $trackData->trackTitle);
            $ReferenceTitle->appendChild ($TitleText);

            $SubTitle = $this->xml->createElement ("SubTitle", $trackData->trackSubtitle);
            $ReferenceTitle->appendChild ($SubTitle);

            # Duration

            $Duration = $this->xml->createElement ("Duration", $trackData->trackDuration);
            $SoundRecording->appendChild ($Duration);

            $DetailsForTerritory = $this->xml->createElement ("SoundRecordingDetailsByTerritory");
            $SoundRecording->appendChild ($DetailsForTerritory);

            $Worldwide = $this->xml->createElement ("TerritoryCode", "Worldwide");
            $DetailsForTerritory->appendChild ($Worldwide);

            $trackTitle = $trackData->trackTitle;
            if ($trackData->trackSubtitle != NULL)
                $trackTitle .= ' (' . $trackData->trackSubtitle . ')';

            # Formal title

            $Title = $this->xml->createElement ("Title");
            $DetailsForTerritory->appendChild ($Title);

            $Title->setAttribute ('LanguageAndScriptCode', $trackData->trackTitleLang);
            $Title->setAttribute ('TitleType', 'FormalTitle');

            $TitleText = $this->xml->createElement ("TitleText", $trackTitle);
            $Title->appendChild ($TitleText);

            # Display title

            $Title = $this->xml->createElement ("Title");
            $DetailsForTerritory->appendChild ($Title);

            $Title->setAttribute ('LanguageAndScriptCode', $trackData->trackTitleLang);
            $Title->setAttribute ('TitleType', 'DisplayTitle');

            $TitleText = $this->xml->createElement ("TitleText", $trackTitle);
            $Title->appendChild ($TitleText);

            # Abbreviated display title

            $Title = $this->xml->createElement ("Title");
            $DetailsForTerritory->appendChild ($Title);

            $Title->setAttribute ('LanguageAndScriptCode', $trackData->trackTitleLang);
            $Title->setAttribute ('TitleType', 'AbbreviatedDisplayTitle');

            $TitleText = $this->xml->createElement ("TitleText", $trackTitle);
            $Title->appendChild ($TitleText);

            # Display artists

            $pointer = 0;

            foreach ($trackData->trackArtists as $art)
            {
                ++$pointer;

                $DisplayArtist = $this->xml->createElement ("DisplayArtist");
                $DetailsForTerritory->appendChild ($DisplayArtist);

                $DisplayArtist->setAttribute ('SequenceNumber', strval ($pointer));

                # Artist name

                foreach ($art->artistName as $pName)
                {
                    $PartyName = $this->xml->createElement ("PartyName", $pName->artistName);
                    $PartyName->setAttribute ('LanguageAndScriptCode', $pName->artistLanguage);
                    $DisplayArtist->appendChild ($PartyName);
                }

                # Artist role

                $ArtistRole = $this->xml->createElement ("ArtistRole", $art->getRole ());
                $DisplayArtist->appendChild ($ArtistRole);
            }

            # Label name

            $LabelName = $this->xml->createElement ("LabelName", $this->release->releaseRecordLabel);
            $DetailsForTerritory->appendChild ($LabelName);

            # P-Line (Phonogram or Producer)

            $PLine = $this->xml->createElement ("PLine");
            $DetailsForTerritory->appendChild ($PLine);

            $Year = $this->xml->createElement ("Year", strval ($trackData->trackPLineYear));
            $PLineText = $this->xml->createElement ("PLineText", $trackData->trackPLine);

            $PLine->appendChild ($Year);
            $PLine->appendChild ($PLineText);

            # Genre text

            $Genre = $this->xml->createElement ("Genre");
            $DetailsForTerritory->appendChild ($Genre);

            $GenreText = $this->xml->createElement ("GenreText", $trackData->trackGenre);

            $Genre->appendChild ($GenreText);

            # Parental Advisory (Explicit Content)

            $ParentalWarningType = $this->xml->createElement ("ParentalWarningType", $trackData->getExplicit ());
            $DetailsForTerritory->appendChild ($ParentalWarningType);

            # File specifications

            $WorldwideSoundRecordingDetails = $this->xml->createElement ("TechnicalSoundRecordingDetails");
            $DetailsForTerritory->appendChild ($WorldwideSoundRecordingDetails);

            $TechnicalID = $this->xml->createElement ("TechnicalResourceDetailsReference", $this->technicalReferences[$pos]);
            $WorldwideSoundRecordingDetails->appendChild ($TechnicalID);

            $AudioCodecType = $this->xml->createElement ("AudioCodecType", $trackData->trackCodecType);
            $WorldwideSoundRecordingDetails->appendChild ($AudioCodecType);

            $IsPreview = $this->xml->createElement ("IsPreview", $trackData->trackIsPreview);
            $WorldwideSoundRecordingDetails->appendChild ($IsPreview);

            $File = $this->xml->createElement ("File");
            $WorldwideSoundRecordingDetails->appendChild ($File);

            $FileName = $this->xml->createElement ("FileName", $trackData->filename);
            $File->appendChild ($FileName);

            $FilePath = $this->xml->createElement ("FilePath", $trackData->filepath);
            $File->appendChild ($FilePath);
            
            $HashSum = $this->xml->createElement ("HashSum");
            $File->appendChild ($HashSum);

            $HashSum2 = $this->xml->createElement ("HashSum", $trackData->hash);
            $HashSum->appendChild ($HashSum2);

            $HashSumAlgorithmType = $this->xml->createElement ("HashSumAlgorithmType", $trackData->hashType);
            $HashSum->appendChild ($HashSumAlgorithmType);
        }
    }

    protected function frontCover ()
    {
        $Image = $this->xml->createElement ("Image");
        $this->resourceEntrypoint->appendChild ($Image);

        $Type = $this->xml->createElement ("ImageType", "FrontCoverImage");
        $Image->appendChild ($Type);

        $ImageId = $this->xml->createElement ("ImageId");
        $Image->appendChild ($ImageId);

        $ProprietaryId = $this->xml->createElement ("ProprietaryId", $this->release->releaseCoverArt->proprietaryId);

        $ProprietaryId->setAttribute ('Namespace', 'DPID:' . $this->getSenderDPID ());

        $ImageId->appendChild ($ProprietaryId);

        $ResourceReference = $this->xml->createElement ("ResourceReference", end ($this->resourceReferences));
        $Image->appendChild ($ResourceReference);

        $DetailsForTerritory = $this->xml->createElement ("ImageDetailsByTerritory");
        $Image->appendChild ($DetailsForTerritory);

        $Worldwide = $this->xml->createElement ("TerritoryCode", "Worldwide");
        $DetailsForTerritory->appendChild ($Worldwide);

        $WorldwideImageDetails = $this->xml->createElement ("TechnicalImageDetails");
        $DetailsForTerritory->appendChild ($WorldwideImageDetails);

        $TechnicalID = $this->xml->createElement ("TechnicalResourceDetailsReference", end ($this->technicalReferences));
        $WorldwideImageDetails->appendChild ($TechnicalID);

        $ImageCodecType = $this->xml->createElement ("ImageCodecType", $this->release->releaseCoverArt->imageCodec);
        $WorldwideImageDetails->appendChild ($ImageCodecType);

        $ImageHeight = $this->xml->createElement ("ImageHeight", $this->release->releaseCoverArt->height);
        $WorldwideImageDetails->appendChild ($ImageHeight);
        
        $ImageWidth = $this->xml->createElement ("ImageWidth", $this->release->releaseCoverArt->width);
        $WorldwideImageDetails->appendChild ($ImageWidth);

        $File = $this->xml->createElement ("File");
        $WorldwideImageDetails->appendChild ($File);

        $FileName = $this->xml->createElement ("FileName", $this->release->releaseCoverArt->filename);
        $File->appendChild ($FileName);

        $FilePath = $this->xml->createElement ("FilePath", $this->release->releaseCoverArt->filepath);
        $File->appendChild ($FilePath);
        
        $HashSum = $this->xml->createElement ("HashSum");
        $File->appendChild ($HashSum);

        $HashSum2 = $this->xml->createElement ("HashSum", $this->release->releaseCoverArt->hash);
        $HashSum->appendChild ($HashSum2);

        $HashSumAlgorithmType = $this->xml->createElement ("HashSumAlgorithmType", $this->release->releaseCoverArt->hashType);
        $HashSum->appendChild ($HashSumAlgorithmType);


    }

    protected function writeTrackReleases ()
    {
        $releasePos = 0;
        $pos = -1;
        foreach ($this->release->releaseTracks as $trackData)
        {
            ++$releasePos;
            ++$pos;
            
            $Release = $this->xml->createElement ("Release");
            $this->releasesEntrypoint->appendChild ($Release);

            # Track IDs

            $ReleaseId = $this->xml->createElement ("ReleaseId");
            $Release->appendChild ($ReleaseId);

            # ISRC

            $ISRC = $this->xml->createElement ("ISRC", $trackData->trackISRC);
            $ReleaseId->appendChild ($ISRC);

            # Proprietary ID

            $ProprietaryId = $this->xml->createElement ("ProprietaryId", $trackData->trackProprietaryID);

            $ProprietaryId->setAttribute ('Namespace', 'DPID:' . $this->getSenderDPID ());

            $ReleaseId->appendChild ($ProprietaryId);

            # Release Reference

            $ReleaseReference = $this->xml->createElement ("ReleaseReference", $this->releaseReferences[$releasePos]);
            $Release->appendChild ($ReleaseReference);

            # Reference Title

            $trackTitle = $trackData->trackTitle;
            if ($trackData->trackSubtitle != NULL)
                $trackTitle .= ' (' . $trackData->trackSubtitle . ')';

            $ReferenceTitle = $this->xml->createElement ("ReferenceTitle");
            $Release->appendChild ($ReferenceTitle);

            $TitleText = $this->xml->createElement ("TitleText", $trackTitle);
            $ReferenceTitle->appendChild ($TitleText);

            # Reference List

            # TrackRelease

            # Territory entrypoint

            # Territory code Worldwide

            # Display artist

            # Label name

            # FormalTitle

            # DisplayTitle

            # GroupingTitle

            # Display artists

            # Parental Advisory (Explicit Content)

            # Link to SoundRecording

            # Genre text

            # Release Date (NOT A DEAL)

            # Original release date (NOT A DEAL)

            # Keywords (SEO)

            # P-Line (Phonogram or Producer)

            # C-Line (Copyright)
        }
    }

    protected function writeAlbumRelease ()
    {
        $Release = $this->xml->createElement ("Release");
        $Release->setAttribute ('isMainRelease', 'true');
        $this->releasesEntrypoint->appendChild ($Release);
    }

    protected function deals ()
    {
        # Todo
    }

    public function __toString ()
    {

        #if (sizeof ($this->release->releaseTracks) < 1)
        #    throw new LengthException ('Tracks count must to be 1 or more.');

        #if (!($this->release->releaseCoverArt instanceof Artwork))
        #    throw new InvalidArgumentException ('Artwork for release is not defined.');

        $this->initDom ();
        $this->writeHeader ();
        $this->enumReleaseRefs ();
        $this->enumResourceRefs ();
        $this->enumTechnicalRefs ();
        $this->initResourceEntrypoint ();
        $this->initReleasesEntrypoint ();
        $this->initDealsEntrypoint ();
        $this->soundRecordings ();
        $this->frontCover ();
        $this->writeTrackReleases ();
        $this->writeAlbumRelease ();
        $this->deals ();

        return $this->xml->saveXML();
    }

}
