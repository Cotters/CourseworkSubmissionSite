<?php
    namespace Config;
/**
 * Generated by framework installer - Tue, 17 Oct 2017 18:47:31 +0200
*/
    class Config
    {
	const BASEDNAME	= '/webproject';
	const DBHOST	= 'localhost';
	const DB	= 'webproject';
	const DBUSER	= 'josh';
	const DBPW	= 'csc3123';
	const SITENAME	= 'webproject';
	const SITEURL	= 'http://webproject.co.uk';
	const SITENOREPLY	= 'j.cotterell1@ncl.ac.uk';
	const SYSADMIN	= 'j.cotterell1@ncl.ac.uk';
	const DBRX	= FALSE;
	const UPUBLIC	= FALSE;
	const UPRIVATE	= TRUE;
	const USEPHPM	= FALSE;

        public static function setup()
        {
            \Framework\Web\Web::getinstance()->addheader([
            'Date'			=> gmstrftime('%b %d %Y %H:%M:%S', time()),
            'Window-target'		=> '_top',	# deframes things
            'X-Frame-Options'	=> 'DENY',	# deframes things
            'Content-Language'	=> 'en',
            'Vary'			=> 'Accept-Encoding',
            ]);
        }
    }
?>