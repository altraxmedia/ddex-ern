<?php

/*
    (c) 2022 Al-Trax Media Limited
    This software is a trade secret. Do not distribute!

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

        $MessageRecipient = $this->xml->createElement ("MessageRecipient");
        $MessageHeader->appendChild ($MessageRecipient);

        $PartyId = $this->xml->createElement ("PartyId", $this->release->recipient->partyId);
        $MessageRecipient->appendChild ($PartyId);

        $PartyName = $this->xml->createElement ("PartyName", $this->release->recipient->partyName);
        $MessageRecipient->appendChild ($PartyName);

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

    public function __toString ()
    {
        $this->initDom ();
        $this->writeHeader ();
        $this->initResourceEntrypoint ();
        $this->initReleasesEntrypoint ();
        $this->initDealsEntrypoint ();

        return $this->xml->saveXML();
    }

    public function returnDPID ()
    {
        return $this->release->DPID;
    }
}