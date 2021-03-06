<?php

////	Browning: A Mailgun Script (v0.26)
// https://github.com/eustasy/browning-a-mailgun-script

////	Mailgun
// Sign up at https://mailgun.com/signup
// First 10,00 mails a month free.

// Mailgun API URL
// Replace example.com with your domain after signing up at
// $Browning['URL'] = 'https://api.mailgun.net/v2/example.com';
$Browning['URL'] = 'https://api.mailgun.net/v2/eustasy.org';

// Mailgun API Key
// Use API Key found at https://mailgun.com/cp
// $Browning['Key'] = 'key-123456-abcdefg-789012-abc-34567';
$Browning['Key'] = 'key-4z89osii74llxixgvr-k5rbg-brwq2s0';
// Not the Public Key. We don't need that.

// From for Mail
// $Browning['Default']['Regards'] = 'Example Support';
$Browning['Default']['Regards'] = 'eustasy Support';

// Reply-to address
// Please don't use noreply.
// Should match the domain in your API URL.
// $Browning['Default']['ReplyTo'] = 'support@example.com';
$Browning['Default']['ReplyTo'] = 'support@eustasy.org';