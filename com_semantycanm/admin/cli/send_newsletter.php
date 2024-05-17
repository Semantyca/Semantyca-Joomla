<?php

use Semantyca\Component\SemantycaNM\Administrator\Helper\NewsletterSender;

if (php_sapi_name() != 'cli') {
	die('This script can only be run from the command line.');
}

require_once 'NewsletterSender.php';

$newsletterId = $argv[1] ?? null;
$baseUrl = $argv[2] ?? null;

if (!$newsletterId || !$baseUrl) {
	echo "Usage: php send_newsletter.php [newsletterId] [baseURL]\n";
	exit;
}

try {
	$sender = new NewsletterSender();
	$sender->sendNewsletterAsync($newsletterId, $baseUrl);
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}
?>
