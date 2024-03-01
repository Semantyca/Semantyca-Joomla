<?php

if (php_sapi_name() != 'cli')
{
	die('This script can only be run from the command line.');
}

use Joomla\CMS\Factory;
use PHPMailer\PHPMailer\PHPMailer;

define('_JEXEC', 1);
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../../'));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';


$config = Factory::getConfig();
$mailer = new PHPMailer(true);
$mailer->isSMTP();
$mailer->Host       = $config->get('smtphost');
$mailer->SMTPAuth   = $config->get('smtpauth');
$mailer->Username   = $config->get('smtpuser');
$mailer->Password   = $config->get('smtppass');
$mailer->SMTPSecure = $config->get('smtpsecure');
$mailer->Port       = $config->get('smtpport');
$mailer->setFrom($config->get('mailfrom'), $config->get('fromname'));

$db = Factory::getContainer()->get('DatabaseDriver');

$TRACKING_PIXEL_TEMPLATE = '<img src="%sindex.php?option=com_semantycanm&task=SiteSubscriberEvent.postEvent&id=%s" width="1" height="1" alt="" style="display:none;">';

function sendNewsletterAsync($newsletterId, $baseURL)
{
	$results = fetchNewsletterData($newsletterId);
	if (empty($results))
	{
		echo "No data found for newsletter ID " . $newsletterId . "\n";

		return;
	}

	foreach ($results as $row)
	{
		sendEmail($row, $baseURL);
	}
}

function fetchNewsletterData($newsletterId)
{
	global $db;
	$query = $db->getQuery(true);
	$query->select($db->quoteName([
		'nl.subject',
		'nl.message_content',
		'e.subscriber_email',
		'e.trigger_token',
		'e.id']))
		->from($db->quoteName('#__semantyca_nm_newsletters', 'nl'))
		->join('INNER', $db->quoteName('#__semantyca_nm_stats', 's') . ' ON ' . $db->quoteName('nl.id') . ' = ' . $db->quoteName('s.newsletter_id'))
		->join('INNER', $db->quoteName('#__semantyca_nm_subscriber_events', 'e') . ' ON ' . $db->quoteName('e.stats_id') . ' = ' . $db->quoteName('s.id'))
		->where($db->quoteName('e.event_type') . ' = 100')
		->where($db->quoteName('e.fulfilled') . ' = 0')
		->where($db->quoteName('nl.id') . ' = ' . $db->quote($newsletterId));

	$db->setQuery($query);

	return $db->loadObjectList();
}

function sendEmail($rowData, $baseURL)
{
	global $mailer, $TRACKING_PIXEL_TEMPLATE;
	$trackingPixel  = sprintf($TRACKING_PIXEL_TEMPLATE, $baseURL, urlencode($rowData->trigger_token));
	$customizedBody = str_replace('</body>', $trackingPixel . '</body>', $rowData->message_content);

	$mailer->addAddress($rowData->subscriber_email);
	$mailer->isHTML(true);
	$mailer->Subject = $rowData->subject;
	$mailer->Body    = $customizedBody;

	try
	{
		if ($mailer->send())
		{
			updateEventFulfilled($rowData->id, true);
		}
		else
		{
			updateEventFulfilled($rowData->id, false, 'Mailer send() returned false');
		}
	}
	catch (\Throwable $e)
	{
		echo $e->getMessage() . "\n";
		error_log($e);
		updateEventFulfilled($rowData->id, false, $e->getMessage());
	}

	$mailer->clearAllRecipients();
}


function updateEventFulfilled($eventId, $success, $errorMessage = null)
{
	global $db;
	$query = $db->getQuery(true);

	$fulfilledValue = $success ? '1' : '0';

	if ($success)
	{
		$fields = [
			$db->quoteName('fulfilled') . ' = ' . $db->quote($fulfilledValue),
			$db->quoteName('event_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')),
		];
	}
	else
	{
		$fields = [
			$db->quoteName('fulfilled') . ' = ' . $db->quote($fulfilledValue),
			$db->quoteName('event_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')),
			$db->quoteName('errors') . ' = JSON_ARRAY_APPEND(' . $db->quoteName('errors') . ', "$", ' . $db->quote($errorMessage) . ')',
		];
	}

	$conditions = [$db->quoteName('id') . ' = ' . $db->quote($eventId)];
	$query->update($db->quoteName('#__semantyca_nm_subscriber_events'))->set($fields)->where($conditions);

	$db->setQuery($query);
	$db->execute();

	if ($success)
	{
		echo "Marked event ID $eventId as fulfilled.\n";
	}
	else
	{
		echo "Marked event ID $eventId as not fulfilled and logged error: $errorMessage\n";
	}
}


$newsletter_id = $argv[1] ?? null;
$base_url      = $argv[2] ?? null;

if (!$newsletter_id || !$base_url)
{
	echo "Usage: php script.php [newsletterId] [baseURL]\n";
	exit;
}

try
{
	sendNewsletterAsync($newsletter_id, $base_url);
}
catch (Exception $e)
{
	echo 'Caught exception: ', $e->getMessage(), "\n";
}

