<?php

use Semantyca\Component\SemantycaNM\Administrator\Helper\SMTPSender;

if (php_sapi_name() != 'cli') {
	die('This script can only be run from the command line.');
}

require_once 'SMTPSender.php';

$newsletterId = $argv[1] ?? null;
$baseUrl = $argv[2] ?? null;

if (!$newsletterId || !$baseUrl) {
	echo "Usage: php send_newsletter.php [newsletterId] [baseURL]\n";
	exit;
}

try {
	$sender = new SMTPSender();
	$sender->sendNewsletter($newsletterId, $baseUrl);
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}
?>
