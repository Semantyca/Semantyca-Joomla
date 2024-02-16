<?php
// Ensure this script is being run from the command line
if (php_sapi_name() !== 'cli')
{
	die('This script can only be run from the command line.');
}

// Initialize Joomla framework
define('_JEXEC', 1);
define('JPATH_BASE', realpath(__DIR__ . '/../../../../../')); // Move up 5 levels to get to the Joomla base directory

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Boot the Joomla framework.
$app = JFactory::getApplication('site');

// Import your custom helper class
JLoader::register('MessagingHelper', JPATH_BASE . '/path/to/your/helper/MessagingHelper.php'); // Adjust the path as needed

// Process command line arguments
$options = getopt('', ['subject:', 'body:', 'recipient:']);

$subject   = $options['subject'] ?? 'Default Subject';
$body      = $options['body'] ?? 'Default Body';
$recipient = $options['recipient'] ?? 'default@example.com';

// Call your email sending function
try
{
	// Assuming MessagingHelper::sendEmail statically handles email sending
	MessagingHelper::sendEmail($subject, $body, [$recipient]);
	echo "Email sent successfully to {$recipient}.\n";
}
catch (Exception $e)
{
	// Handle exceptions or errors as needed
	echo "Error sending email: " . $e->getMessage() . "\n";
}
