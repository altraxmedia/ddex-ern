<?php

/*
    (c) 2022 Al-Trax Media Limited
    This software is a trade secret. Do not distribute!

    Standart: DDEX ERN v3.8.2 (Original)

    Written by Serhii Shmaida, Georgy Akhmetov, Saveliy Safonov on 12.02.2022
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

    protected function genGuid ()
    {
        return sprintf ('%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand (0, 0xffff), mt_rand (0, 0xffff),
            mt_rand (0, 0xffff),
            mt_rand (0, 0x0fff) | 0x4000,
            mt_rand (0, 0x3fff) | 0x8000,
            mt_rand (0, 0xffff), mt_rand (0, 0xffff), mt_rand (0, 0xffff)
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
        $attr1 = $this->xml->createAttribute ('xmlns:ern');
        $attr2 = $this->xml->createAttribute ('xmlns:xs');
        $attr3 = $this->xml->createAttribute ('LanguageAndScriptCode');
        $attr4 = $this->xml->createAttribute ('MessageSchemaVersionId');
        $attr5 = $this->xml->createAttribute ('xs:schemaLocation');

        $attr1->value = 'http://ddex.net/xml/ern/382';
        $attr2->value = 'http://www.w3.org/2001/XMLSchema-instance';
        $attr3->value = $this->language;
        $attr4->value = 'ern/382';
        $attr5->value = 'http://ddex.net/xml/ern/382 http://ddex.net/xml/ern/382/release-notification.xsd';

        $this->baseEntrypoint->appendChild ($attr1);
        $this->baseEntrypoint->appendChild ($attr2);
        $this->baseEntrypoint->appendChild ($attr3);
        $this->baseEntrypoint->appendChild ($attr4);
        $this->baseEntrypoint->appendChild ($attr5);

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
        
        for ($i = 1; $i < count ($this->release->releaseTracks); ++$i)
            $resourceReferences[] = 'A' . $i;

        $resourceReferences[] = 'A' . ($i + 1);

        $this->resourceReferences = $resourceReferences;

    }

    protected function enumReleaseRefs ()
    {
        $releaseReferences = [];
        
        for ($i = 1; $i < count ($this->release->releaseTracks); ++$i)
            $releaseReferences[] = 'R' . $i;

        $releaseReferences[] = 'R' . ($i + 1);

        $this->releaseReferences = $releaseReferences;

    }

    protected function soundRecordings ()
    {
        // to do
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

        $attr1 = $this->xml->createAttribute ('Namespace');
        $attr1->value = 'DPID:' . $this->getSenderDPID ();

        $ProprietaryId->appendChild ($attr1);
        $ImageId->appendChild ($ProprietaryId);

        $ResourceReference = $this->xml->createElement ("ResourceReference", end ($this->resourceReferences));
        $Image->appendChild ($ResourceReference);

        $DetailsForTerritory = $this->xml->createElement ("ImageDetailsByTerritory");
        $Image->appendChild ($DetailsForTerritory);

        $Worldwide = $this->xml->createElement ("TerritoryCode", "Worldwide");
        $DetailsForTerritory->appendChild ($Worldwide);


    }

    public function __toString ()
    {
        $this->initDom ();
        $this->writeHeader ();
        $this->enumReleaseRefs ();
        $this->enumResourceRefs ();
        $this->initResourceEntrypoint ();
        $this->initReleasesEntrypoint ();
        $this->initDealsEntrypoint ();
        $this->soundRecordings ();
        $this->frontCover ();

        return $this->xml->saveXML();
    }

}
