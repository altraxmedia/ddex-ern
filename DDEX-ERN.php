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

    function __construct()
    {
        $this->release = new Release();        
        $this->release->DPID = "TEST"; 
    }

    public function printDPID()
    {
        echo $this->release->DPID;
    }
}