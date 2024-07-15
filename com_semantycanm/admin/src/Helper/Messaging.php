<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Uri\Uri;
use PHPMailer\PHPMailer\Exception;
use Semantyca\Component\SemantycaNM\Administrator\DTO\NewsletterDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\MessagingException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\NewsletterSenderException;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewslettersModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\SubscriberEventModel;

class Messaging
{
	private NewslettersModel $newsletterModel;
	private MailingListModel $mailingListModel;
	private StatModel $statModel;
	private SubscriberEventModel $eventModel;
	private string $baseURL;

	public function __construct(NewslettersModel     $newsletterModel,
	                            StatModel            $statModel,
	                            SubscriberEventModel $eventModel,
	                            MailingListModel     $mailingListModel)
	{
		$this->newsletterModel  = $newsletterModel;
		$this->statModel        = $statModel;
		$this->eventModel       = $eventModel;
		$this->mailingListModel = $mailingListModel;
		$this->baseURL          = Uri::root();
	}


	/**
	 * @throws Exception
	 * @throws NewsletterSenderException|MessagingException
	 * @since 1.0
	 */
	public function sendEmail(NewsletterDTO $newsletterDTO, $send_async): int
	{
		if ($newsletterDTO->isTest)
		{
			$recipients = [$newsletterDTO->testEmail];
		}
		else
		{
			$recipients = $this->mailingListModel->getEmailAddresses($newsletterDTO->mailingListIds);
		}

		if (empty($recipients))
		{
			throw new MessagingException(['No recipients found for this newsletter.']);
		}

		$newsletterId = $this->newsletterModel->upsert($newsletterDTO->id, $newsletterDTO);

		foreach ($recipients as $e_mail)
		{
			$stat_rec_id = $this->statModel->createSendingRecord(Constants::NOT_FULFILLED, $newsletterId);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_DISPATCHED);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_READ);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_UNSUBSCRIBE);
			//$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_CLICK);
		}

		if ($send_async)
		{
			$newsletterId = escapeshellarg($newsletterId);
			$baseURL      = escapeshellarg($this->baseURL);
			$realPath     = realpath(dirname(__FILE__));
			$scriptPath   = $realPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'send_newsletter.php';
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			{
				pclose(popen("start /B php \"$scriptPath\" $newsletterId $baseURL > NUL 2>&1", "r"));
			}
			else
			{
				exec("php '$scriptPath' $newsletterId $baseURL > /dev/null 2>&1 &");
			}
		}
		else
		{
			$sender = new SMTPSender($this->newsletterModel);
			$sender->sendNewsletter($newsletterId, $this->baseURL);
		}

		return $newsletterId;
	}


}



