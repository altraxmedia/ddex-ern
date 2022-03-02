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
    protected $dealsFromRelease;

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
        
        for ($i = 1; $i <= count ($this->release->releaseTracks); ++$i)
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
        
        for ($i = 1; $i <= count ($this->release->releaseTracks); ++$i)
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

            # MEAD

            if ($trackData->trackMediaEnrichment instanceof MEAD)
            {
                # Bool variables

                $mead = $trackData->trackMediaEnrichment;

                $Var = $this->xml->createElement ("IsMedley", $mead->IsMedley ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsPotpourri", $mead->IsPotpourri ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsInstrumental", $mead->IsInstrumental ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsBackground", $mead->IsBackground ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsHiddenResource", $mead->IsHiddenResource ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsBonusResource", $mead->IsBonusResource ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsComputerGenerated", $mead->IsComputerGenerated ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("IsRemastered", $mead->IsRemastered ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("NoSilenceBefore", $mead->NoSilenceBefore ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                $Var = $this->xml->createElement ("NoSilenceAfter", $mead->NoSilenceAfter ? 'true' : 'false');
                $SoundRecording->appendChild ($Var);

                # String variables

                if ($mead->LanguageAndScriptCode != '')
                {
                    $Var = $this->xml->createElement ("LanguageAndScriptCode", $mead->LanguageAndScriptCode);
                    $SoundRecording->appendChild ($Var);
                }

                if ($mead->CreationDate != '')
                {
                    $Var = $this->xml->createElement ("CreationDate", $mead->CreationDate);
                    $SoundRecording->appendChild ($Var);
                }

                if ($mead->MasteredDate != '')
                {
                    $Var = $this->xml->createElement ("MasteredDate", $mead->MasteredDate);
                    $SoundRecording->appendChild ($Var);
                }

                if ($mead->RemasteredDate != '')
                {
                    $Var = $this->xml->createElement ("RemasteredDate", $mead->RemasteredDate);
                    $SoundRecording->appendChild ($Var);
                }

            }

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

            if ($this->release->releaseNoData == false)
            {
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

        if ($this->release->releaseNoData == false)
        {
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
    }

    protected function writeTrackReleases ()
    {
        $pos = 0;
        foreach ($this->release->releaseTracks as $trackData)
        {
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

            $ReleaseReference = $this->xml->createElement ("ReleaseReference", $this->releaseReferences[$pos]);
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

            $ReleaseResourceReferenceList = $this->xml->createElement ("ReleaseResourceReferenceList");
            $Release->appendChild ($ReleaseResourceReferenceList);

            $ReleaseResourceReference = $this->xml->createElement ("ReleaseResourceReference", $this->resourceReferences[$pos - 1]);
            $ReleaseResourceReference->setAttribute ('ReleaseResourceType', 'PrimaryResource');
            $ReleaseResourceReferenceList->appendChild ($ReleaseResourceReference);

            # TrackRelease

            $ReleaseType = $this->xml->createElement ("ReleaseType", 'TrackRelease');
            $Release->appendChild ($ReleaseType);

            # Territory entrypoint

            $DetailsForTerritory = $this->xml->createElement ("ReleaseDetailsByTerritory");
            $Release->appendChild ($DetailsForTerritory);

            # Territory code Worldwide

            $Worldwide = $this->xml->createElement ("TerritoryCode", "Worldwide");
            $DetailsForTerritory->appendChild ($Worldwide);

            # Display artist

            $DisplayArtistName = $this->xml->createElement ("DisplayArtistName", $trackData->trackDisplayArtist);
            $DetailsForTerritory->appendChild ($DisplayArtistName);

            # Label name

            $LabelName = $this->xml->createElement ("LabelName", $this->release->releaseRecordLabel);
            $DetailsForTerritory->appendChild ($LabelName);

            # Formal title

            $Title = $this->xml->createElement ("Title");
            $DetailsForTerritory->appendChild ($Title);

            $Title->setAttribute ('LanguageAndScriptCode', $trackData->trackTitleLang);
            $Title->setAttribute ('TitleType', 'FormalTitle');

            $TitleText = $this->xml->createElement ("TitleText", $trackData->trackTitle);
            $SubTitle = $this->xml->createElement ("SubTitle", $trackData->trackSubtitle);

            $Title->appendChild ($TitleText);
            $Title->appendChild ($SubTitle);

            # Display title

            $Title = $this->xml->createElement ("Title");
            $DetailsForTerritory->appendChild ($Title);

            $Title->setAttribute ('TitleType', 'DisplayTitle');

            $TitleText = $this->xml->createElement ("TitleText", $trackTitle);
            $Title->appendChild ($TitleText);

            # Grouping Title

            $Title = $this->xml->createElement ("Title");
            $DetailsForTerritory->appendChild ($Title);

            $Title->setAttribute ('TitleType', 'GroupingTitle');

            $TitleText = $this->xml->createElement ("TitleText", $trackData->trackTitle);
            $SubTitle = $this->xml->createElement ("SubTitle", $trackData->trackSubtitle);

            $Title->appendChild ($TitleText);
            $Title->appendChild ($SubTitle);

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

            # Parental Advisory (Explicit Content)

            $ParentalWarningType = $this->xml->createElement ("ParentalWarningType", $trackData->getExplicit ());
            $DetailsForTerritory->appendChild ($ParentalWarningType);

            # Genre text

            $Genre = $this->xml->createElement ("Genre");
            $DetailsForTerritory->appendChild ($Genre);

            $GenreText = $this->xml->createElement ("GenreText", $trackData->trackGenre);

            $Genre->appendChild ($GenreText);

            # Release Date (NOT A DEAL)

            $ReleaseDate = $this->xml->createElement ("ReleaseDate", $trackData->trackReleaseDate);
            $DetailsForTerritory->appendChild ($ReleaseDate);

            # Original release date (NOT A DEAL)

            $OriginalReleaseDate = $this->xml->createElement ("OriginalReleaseDate", $trackData->trackOriginalReleaseDate);
            $DetailsForTerritory->appendChild ($OriginalReleaseDate);

            # Keywords (SEO)

            $Keywords = $this->xml->createElement ("Keywords", $trackData->trackKeywords);
            $DetailsForTerritory->appendChild ($Keywords);

            # P-Line (Phonogram or Producer)

            $PLine = $this->xml->createElement ("PLine");
            $Release->appendChild ($PLine);

            $Year = $this->xml->createElement ("Year", strval ($trackData->trackPLineYear));
            $PLineText = $this->xml->createElement ("PLineText", $trackData->trackPLine);

            $PLine->appendChild ($Year);
            $PLine->appendChild ($PLineText);

            # C-Line (Copyright)

            $CLine = $this->xml->createElement ("CLine");
            $Release->appendChild ($CLine);

            $Year = $this->xml->createElement ("Year", strval ($trackData->trackCLineYear));
            $CLineText = $this->xml->createElement ("CLineText", $trackData->trackCLine);

            $CLine->appendChild ($Year);
            $CLine->appendChild ($CLineText);
        }
    }

    protected function writeAlbumRelease ()
    {
        $Release = $this->xml->createElement ("Release");
        $Release->setAttribute ('isMainRelease', 'true');
        $this->releasesEntrypoint->appendChild ($Release);

        # Track IDs

        $ReleaseId = $this->xml->createElement ("ReleaseId");
        $Release->appendChild ($ReleaseId);

        # ICPN

        $ICPN = $this->xml->createElement ("ICPN", $this->release->releaseICPN);
        $ICPN->setAttribute ('isEan', $this->release->releaseICPNIsEan ? 'true' : 'false');
        $ReleaseId->appendChild ($ICPN);

        # Catalog ID

        $CatalogNumber = $this->xml->createElement ("CatalogNumber", $this->release->releaseCatalogNo);
        $CatalogNumber->setAttribute ('Namespace', 'DPID:' . $this->getSenderDPID ());
        $ReleaseId->appendChild ($CatalogNumber);

        # Proprietary ID

        $ProprietaryId = $this->xml->createElement ("ProprietaryId", $this->release->releaseProprietaryId);
        $ProprietaryId->setAttribute ('Namespace', 'DPID:' . $this->getSenderDPID ());
        $ReleaseId->appendChild ($ProprietaryId);

        # Release Reference

        $ReleaseReference = $this->xml->createElement ("ReleaseReference", $this->releaseReferences[0]);
        $Release->appendChild ($ReleaseReference);

        # Resource Reference

        $ReleaseResourceReferenceList = $this->xml->createElement ("ReleaseResourceReferenceList");
        $Release->appendChild ($ReleaseResourceReferenceList);

        $resourceReferencesNew = $this->resourceReferences;

        $artwork = array_pop ($resourceReferencesNew);

        foreach ($resourceReferencesNew as $resourceRef)
        {
            $ReleaseResourceReference = $this->xml->createElement ("ReleaseResourceReference", $resourceRef);
            $ReleaseResourceReference->setAttribute ('ReleaseResourceType', 'PrimaryResource');
            $ReleaseResourceReferenceList->appendChild ($ReleaseResourceReference);
        }

        $ReleaseResourceReference = $this->xml->createElement ("ReleaseResourceReference", $artwork);
        $ReleaseResourceReference->setAttribute ('ReleaseResourceType', 'SecondaryResource');
        $ReleaseResourceReferenceList->appendChild ($ReleaseResourceReference);

        # ReleaseType

        $ReleaseType = $this->xml->createElement ("ReleaseType", $this->release->releaseType);
        $Release->appendChild ($ReleaseType);

        # Territory entrypoint

        $DetailsForTerritory = $this->xml->createElement ("ReleaseDetailsByTerritory");
        $Release->appendChild ($DetailsForTerritory);

        # Territory code Worldwide

        $Worldwide = $this->xml->createElement ("TerritoryCode", "Worldwide");
        $DetailsForTerritory->appendChild ($Worldwide);

        # Display artist name

        $DisplayArtistName = $this->xml->createElement ("DisplayArtistName", $this->release->releaseDisplayArtist);
        $DetailsForTerritory->appendChild ($DisplayArtistName);

        # Label name

        $LabelName = $this->xml->createElement ("LabelName", $this->release->releaseRecordLabel);
        $DetailsForTerritory->appendChild ($LabelName);

        # DisplayArtist

        $pointer = 0;

        foreach ($this->release->releaseArtists as $art)
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

        # Parental Advisory (Explicit Content)

        $ParentalWarningType = $this->xml->createElement ("ParentalWarningType", $this->release->getExplicit ());
        $DetailsForTerritory->appendChild ($ParentalWarningType);

        # Resource group

        $ResourceGroup = $this->xml->createElement ("ResourceGroup");
        $DetailsForTerritory->appendChild ($ResourceGroup);

        $ResourceGroup2 = $this->xml->createElement ("ResourceGroup");
        $ResourceGroup->appendChild ($ResourceGroup2);

        $SequenceNumber = $this->xml->createElement ("SequenceNumber", 1);
        $ResourceGroup2->appendChild ($SequenceNumber);

        $resPointer = -1;

        foreach ($resourceReferencesNew as $resRef2)
        {
            ++$resPointer;

            $ResourceGroupContentItem = $this->xml->createElement ("ResourceGroupContentItem");
            $ResourceGroup2->appendChild ($ResourceGroupContentItem);

            $SequenceNumber1 = $this->xml->createElement ("SequenceNumber", $resPointer + 1);
            $ResourceGroupContentItem->appendChild ($SequenceNumber1);

            $ResourceType = $this->xml->createElement ("ResourceType", 'SoundRecording');
            $ResourceGroupContentItem->appendChild ($ResourceType);

            $ReleaseResourceReference = $this->xml->createElement ("ReleaseResourceReference", $this->resourceReferences[$resPointer]);
            $ResourceGroupContentItem->appendChild ($ReleaseResourceReference);
        }

        $ResourceGroupContentItem = $this->xml->createElement ("ResourceGroupContentItem");
        $ResourceGroup->appendChild ($ResourceGroupContentItem);

        $SequenceNumber1 = $this->xml->createElement ("SequenceNumber", $resPointer + 2);
        $ResourceGroupContentItem->appendChild ($SequenceNumber1);

        $ResourceType = $this->xml->createElement ("ResourceType", 'Image');
        $ResourceGroupContentItem->appendChild ($ResourceType);

        $ReleaseResourceReference = $this->xml->createElement ("ReleaseResourceReference", $this->resourceReferences[$resPointer + 1]);
        $ResourceGroupContentItem->appendChild ($ReleaseResourceReference);

        # Genre

        $Genre = $this->xml->createElement ("Genre");
        $DetailsForTerritory->appendChild ($Genre);

        $GenreText = $this->xml->createElement ("GenreText", $this->release->releaseGenre);

        $Genre->appendChild ($GenreText);

        # Original release date (NOT A DEAL)

        $OriginalReleaseDate = $this->xml->createElement ("OriginalReleaseDate", $this->release->releaseDate);
        $DetailsForTerritory->appendChild ($OriginalReleaseDate);

        # P-Line (Phonogram or Producer)

        $PLine = $this->xml->createElement ("PLine");
        $Release->appendChild ($PLine);

        $Year = $this->xml->createElement ("Year", strval ($this->release->releasePLineYear));
        $PLineText = $this->xml->createElement ("PLineText", $this->release->releasePLine);

        $PLine->appendChild ($Year);
        $PLine->appendChild ($PLineText);

        # C-Line (Copyright)

        $CLine = $this->xml->createElement ("CLine");
        $Release->appendChild ($CLine);

        $Year = $this->xml->createElement ("Year", strval ($this->release->releaseCLineYear));
        $CLineText = $this->xml->createElement ("CLineText", $this->release->releaseCLine);

        $CLine->appendChild ($Year);
        $CLine->appendChild ($CLineText);
    }

    public function enumDeals ()
    {
        $deals = ['R0' => $this->release->releaseDeal];
        
        $pointer = 0;

        foreach ($this->release->releaseTracks as $track)
        {
            ++$pointer;

            $deals['R' . $pointer] = $track->trackDeal;
        }

        $this->dealsFromRelease = $deals;
    }

    protected function deals ()
    {
        foreach ($this->dealsFromRelease as $ref => $deals)
        {
            $ReleaseDeal = $this->xml->createElement ("ReleaseDeal");
            $this->dealsEntrypoint->appendChild ($ReleaseDeal);

            $DealReleaseReference = $this->xml->createElement ("DealReleaseReference", $ref);
            $ReleaseDeal->appendChild ($DealReleaseReference);

            foreach ($deals as $oneDeal)
            {
                $Deal = $this->xml->createElement ("Deal");
                $ReleaseDeal->appendChild ($Deal);

                $DealTerms = $this->xml->createElement ("DealTerms");
                $Deal->appendChild ($DealTerms);

                if ($oneDeal->subscriptionAvailable == true)
                {
                    $CommercialModelType = $this->xml->createElement ("CommercialModelType", "SubscriptionModel");
                    $DealTerms->appendChild ($CommercialModelType);
                }

                if ($oneDeal->adSupportAvailable == true)
                {
                    $CommercialModelType = $this->xml->createElement ("CommercialModelType", "AdvertisementSupportedModel");
                    $DealTerms->appendChild ($CommercialModelType);
                }

                if ($oneDeal->payAsYouGoAvailable == true)
                {
                    $CommercialModelType = $this->xml->createElement ("CommercialModelType", "PayAsYouGoModel");
                    $DealTerms->appendChild ($CommercialModelType);
                }

                if ($oneDeal->onDemandStream == true || $oneDeal->permanentDownload == true)
                {
                    $Usage = $this->xml->createElement ("Usage");
                    $DealTerms->appendChild ($Usage);

                    if ($oneDeal->permanentDownload == true)
                    {
                        $UseType = $this->xml->createElement ("UseType", "PermanentDownload");
                        $Usage->appendChild ($UseType);
                    }

                    if ($oneDeal->onDemandStream == true)
                    {
                        $UseType = $this->xml->createElement ("UseType", "OnDemandStream");
                        $Usage->appendChild ($UseType);
                    }
                }

                if ($oneDeal->takedown == true)
                {
                    $TakeDown = $this->xml->createElement ("TakeDown", "true");
                    $DealTerms->appendChild ($TakeDown);
                }

                foreach ($oneDeal->dealTerritories as $terCode)
                {
                    $TerritoryCode = $this->xml->createElement ("TerritoryCode", "$terCode");
                    $DealTerms->appendChild ($TerritoryCode);
                }

                if ($oneDeal->takedown == true)
                {
                    $ValidityPeriod = $this->xml->createElement ("ValidityPeriod");
                    $DealTerms->appendChild ($ValidityPeriod);

                    $EndDate = $this->xml->createElement ("EndDate", date ("Y-m-d", strtotime ("-1 week")));
                    $ValidityPeriod->appendChild ($EndDate);
                }
                else
                {
                    $ValidityPeriod = $this->xml->createElement ("ValidityPeriod");
                    $DealTerms->appendChild ($ValidityPeriod);

                    $StartDate = $this->xml->createElement ("StartDate", $oneDeal->dealStartDate);
                    $ValidityPeriod->appendChild ($StartDate);
                }

                if ($oneDeal->dealPreOrderDate != '')
                {
                    $PreOrderReleaseDate = $this->xml->createElement ("PreOrderReleaseDate", $oneDeal->dealPreOrderDate);
                    $DealTerms->appendChild ($PreOrderReleaseDate);
                }

            }

            $EffectiveDate = $this->xml->createElement ("EffectiveDate", $this->release->releaseDate);
            $ReleaseDeal->appendChild ($EffectiveDate);
        }
    }

    public function __toString ()
    {

        if (sizeof ($this->release->releaseTracks) < 1)
            throw new LengthException ('Tracks count must to be 1 or more.');

        if (!($this->release->releaseCoverArt instanceof Artwork))
            throw new InvalidArgumentException ('Artwork for release is not defined.');

        $this->initDom (); # Init XML document
        $this->writeHeader (); # Write MessageHeader
        $this->enumReleaseRefs (); # Enumerate release references
        $this->enumResourceRefs (); # Enumerate resource references
        $this->enumTechnicalRefs (); # Enumerate technical file specification references
        $this->initResourceEntrypoint (); # Init ResourceList
        $this->initReleasesEntrypoint (); # Init ReleaseList
        $this->initDealsEntrypoint (); # Init DealList
        $this->soundRecordings (); # Write SoundRecording resources
        $this->frontCover (); # Write artwork Image resource
        $this->writeTrackReleases (); # Write track releases
        $this->writeAlbumRelease (); # Write album release
        $this->enumDeals (); # Enumerate deals in releases
        $this->deals (); # Write deals for releases

        return $this->xml->saveXML(null, LIBXML_NOEMPTYTAG);
    }

}
