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

    public function __construct()
    {
        $this->release = new Release ();        
        $this->release->DPID = "TEST"; 
    }

    protected function initDom ()
    {
        $this->xml = new DOMDocument ('1.0', "utf-8");
        $this->formatOutput = true;

        $this->baseEntrypoint = $this->xml->createElement ("ern:NewReleaseMessage");
        $domAttribute = $domDocument->createAttribute('name');
        
        $this->xml->appendChild ($this->baseEntrypoint);
    }

    public function returnDPID()
    {
        return $this->release->DPID;
    }
}