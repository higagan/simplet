<?php

////	Feed Header Function
//
// Echoes the Header for RSS Feeds
//
// Categories();
// Categories('exclude-this-canonical');

function Feed_Header($URL) {

	global $Sitewide_Root, $Sitewide_Tagline, $Sitewide_Title;

	// Set the doctype and some basic information
	return '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="'.$Sitewide_Root.$URL.'" rel="self" type="application/rss+xml" />
		<title>'.$Sitewide_Title.'</title>
		<description>'. $Sitewide_Tagline.'</description>
		<link>'. $Sitewide_Root.'</link>
		<language>en</language>
		<generator>Simplet</generator>';

}