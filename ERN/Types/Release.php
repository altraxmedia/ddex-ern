<?php

/**
 * Release object.
 * 
 * @author sergi4ua
 * @copyright Copyright (C) 2022 Al-Trax Media Limited
 * @package ddex-ern
 */

class Release
{
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $DPID = "";
	
	/**
	 * Message sender
	 * 
	 * @access public
	 * @var string Company name
	 */
    public $MessageSender = "";
	
	/**
	 * Release ID
	 * 
	 * @access public
	 * @var string Release ID (must to be unique)
	 */
    public $releaseId = "";

	/**
	 * Release Thread ID
	 * 
	 * @access public
	 * @var string Release ID (must to be unique)
	 */
    public $releaseThreadId = "";
	
	/**
	 * Release Title
	 * 
	 * @access public
	 * @var string Title
	 */
    public $releaseTitle = "";
	
	/**
	 * Release Subtitle
	 * 
	 * @access public
	 * @var string Subtitle
	 */
    public $releaseSubtitle = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseCoverArt = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseTracks = [];
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseRecordLabel = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseDate = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseEAN = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseCatalogNo = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseGenre = 0;
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseArtists = [];
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseExplicit = false;
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releasePLineYear = 2022;
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releasePLine = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseCLineYear = 2022;
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseCLine = "";
	
	/**
	 * Sender DDEX Party ID
	 * 
	 * @access public
	 * @var string DDEX Party ID
	 */
    public $releaseDeal;
}
