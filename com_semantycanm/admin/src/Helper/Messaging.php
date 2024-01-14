<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;
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

	public function __construct(MailingListModel $mailingListModel, StatModel $statModel, SubscriberEventModel $eventModel)
	{
		$this->mailingListModel = $mailingListModel;
		$this->statModel        = $statModel;
		$this->eventModel = $eventModel;
		$this->baseURL = Uri::root();
	}

	/**
	 * @throws MessagingException
	 * @throws UpdateRecordException
	 * @since 1.0
	 */
	public function sendEmail($body, $subject, $user_or_user_group, $newsletter_id): bool
	{
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
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
	function sendMessage($mailer, $recipients, $body, $newsletter_id): bool
	{
		$stat_rec_id         = $this->statModel->createStatRecord($recipients, Constants::SENDING_ATTEMPT, $newsletter_id);
		$allSentSuccessfully = true;

		foreach ($recipients as $recipient)
		{
			$mailer->clearAllRecipients();
			$mailer->addRecipient($recipient);

			$read_event_token  = $this->eventModel->createSubscriberEvent($recipient, Constants::EVENT_TYPE_READ);
			$unsub_event_token = $this->eventModel->createSubscriberEvent($recipient, Constants::EVENT_TYPE_UNSUBSCRIBE);
			$click_event_token = $this->eventModel->createSubscriberEvent($recipient, Constants::EVENT_TYPE_CLICK);
			$trackingPixel     = '<img src="' . $this->baseURL . 'index.php?option=com_semantycanm&task=sitestat.postStat&id=' . urlencode($read_event_token) . '" width="1" height="1" alt="" style="display:none;">';
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
			$this->statModel->updateStatRecord($stat_rec_id, Constants::HAS_BEEN_SENT);
		}
		else
		{
			$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
			throw new MessagingException(['Error occurred in sending emails. Not all recipients will get the message']);
		}

		return $allSentSuccessfully;
	}


}



