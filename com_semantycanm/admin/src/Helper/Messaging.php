<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Uri\Uri;
use PHPMailer\PHPMailer\Exception;
use Semantyca\Component\SemantycaNM\Administrator\Exception\NewsletterSenderException;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewsLetterModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\SubscriberEventModel;

class Messaging
{
	private NewsLetterModel $newsletterModel;
	private ?MailingListModel $mailingListModel;
	private StatModel $statModel;
	private SubscriberEventModel $eventModel;
	private string $baseURL;

	public function __construct(NewsLetterModel $newsletterModel, StatModel $statModel, SubscriberEventModel $eventModel = null, MailingListModel $mailingListModel = null)
	{
		$this->newsletterModel = $newsletterModel;
		$this->statModel        = $statModel;
		$this->eventModel       = $eventModel;
		$this->mailingListModel = $mailingListModel;
		$this->baseURL          = Uri::root();
	}


	/**
	 * @throws Exception
	 * @throws NewsletterSenderException
	 * @since 1.0
	 */
	public function sendEmail($subject, $encodedBody, $user_or_user_group, $send_async = false): bool
	{
		if (filter_var($user_or_user_group[0], FILTER_VALIDATE_EMAIL))
		{
			$recipients = $user_or_user_group;
		}
		else
		{
			$recipients = $this->mailingListModel->getEmailAddresses($user_or_user_group);
		}

		$newsletter_id = $this->newsletterModel->upsert($subject, $encodedBody);

		foreach ($recipients as $e_mail)
		{
			$stat_rec_id = $this->statModel->createStatRecord(Constants::NOT_FULFILLED, $newsletter_id);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_DISPATCHED);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_READ);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_UNSUBSCRIBE);
			//$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_CLICK);
		}

		if ($send_async)
		{
			$newsletterId = escapeshellarg($newsletter_id);
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
			$sender->sendNewsletter($newsletter_id, $this->baseURL);
		}

		return $newsletter_id;
	}


}



