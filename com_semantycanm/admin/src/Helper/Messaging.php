<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Semantyca\Component\SemantycaNM\Administrator\Exception\MessagingException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\UpdateRecordException;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\SubscriberEventModel;

class Messaging
{
	private MailingListModel $mailingListModel;
	private StatModel $statModel;
	private SubscriberEventModel $eventModel;
	private string $baseURL;
	const TRACKING_PIXEL_TEMPLATE = '<img src="%sindex.php?option=com_semantycanm&task=SiteSubscriberEvent.postEvent&id=%s" width="1" height="1" alt="" style="display:none;">';

	public function __construct(StatModel $statModel, SubscriberEventModel $eventModel = null, MailingListModel $mailingListModel = null)
	{
		$this->statModel        = $statModel;
		$this->eventModel = $eventModel;
		$this->mailingListModel = $mailingListModel;
		$this->baseURL = $mailingListModel ? Uri::root() : null;
	}


	public function sendEmailAsync($user_or_user_group, $newsletter_id): bool
	{
		if (filter_var($user_or_user_group, FILTER_VALIDATE_EMAIL))
		{
			$recipients = [$user_or_user_group];
		}
		else
		{
			$recipients = $this->mailingListModel->getEmailAddresses($user_or_user_group);
		}

		$subject      = "Your Email Subject"; // Define your subject here
		$body         = "Your Email Body"; // Define your body here
		$pathToScript = "/path/to/joomla/cli/sendemail.php"; // Adjust this path

		foreach ($recipients as $e_mail)
		{
			$stat_rec_id = $this->statModel->createStatRecord($recipients, Constants::NOT_FULFILLED, $newsletter_id);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_DISPATCHED);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_READ);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_UNSUBSCRIBE);
			$this->eventModel->createSubscriberEvent($stat_rec_id, $e_mail, Constants::EVENT_TYPE_CLICK);

			// Prepare the command
			$command = escapeshellcmd("php $pathToScript --subject=" . escapeshellarg($subject) . " --body=" . escapeshellarg($body) . " --recipient=" . escapeshellarg($e_mail));
			// Execute the command
			exec($command);
		}

		return $this->statModel->createStatRecord($recipients, Constants::NOT_FULFILLED, $newsletter_id);

	}

	/**
	 * @throws MessagingException
	 * @throws UpdateRecordException
	 * @since 1.0
	 */
	public function sendEmail($body, $subject, $user_or_user_group, $newsletter_id): bool
	{
		//$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$mailer = Factory::getMailer();

		$mailer->setSubject($subject);
		$mailer->isHtml(true);

		if (filter_var($user_or_user_group, FILTER_VALIDATE_EMAIL))
		{
			return $this->sendMessage($mailer, [$user_or_user_group], $body, $newsletter_id);
		}
		else
		{
			$result     = true;
			$recipients = $this->mailingListModel->getEmailAddresses($user_or_user_group);

			if (!$this->sendMessage($mailer, $recipients, $body, $newsletter_id))
			{
				$result = false;
			};
		}

		return $result;
	}

	/**
	 * @throws MessagingException
	 * @throws UpdateRecordException
	 * @since
	 */
	private function sendMessage($mailer, $recipients, $body, $newsletter_id): bool
	{
		$stat_rec_id = $this->statModel->createStatRecord($recipients, Constants::NOT_FULFILLED, $newsletter_id);
		$allSentSuccessfully = true;

		foreach ($recipients as $e_mail)
		{
			$mailer->clearAllRecipients();
			$mailer->addRecipient($e_mail);

			$read_event_token  = $this->eventModel->createSubscriberEvent($newsletter_id, $e_mail, Constants::EVENT_TYPE_READ);
			$unsub_event_token = $this->eventModel->createSubscriberEvent($newsletter_id, $e_mail, Constants::EVENT_TYPE_UNSUBSCRIBE);
			$click_event_token = $this->eventModel->createSubscriberEvent($newsletter_id, $e_mail, Constants::EVENT_TYPE_CLICK);
			$trackingPixel     = sprintf(Messaging::TRACKING_PIXEL_TEMPLATE, $this->baseURL, urlencode($read_event_token));
			$customizedBody    = str_replace('</body>', $trackingPixel . '</body>', $body);

			$mailer->setBody($customizedBody);
			$send = $mailer->send();

			if ($send !== true)
			{
				$allSentSuccessfully = false;
				$this->eventModel->deleteEventByTriggerToken($read_event_token);
				$this->eventModel->deleteEventByTriggerToken($unsub_event_token);
				$this->eventModel->deleteEventByTriggerToken($click_event_token);

			}
		}

		if ($allSentSuccessfully)
		{
			$this->statModel->updateStatRecord($stat_rec_id, Constants::DONE);
		}
		else
		{
			$this->statModel->updateStatRecord($stat_rec_id, Constants::NOT_FULFILLED);
			throw new MessagingException(['Error occurred in sending emails. Not all recipients will get the message']);
		}

		return $allSentSuccessfully;
	}


}



