<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Semantyca\Component\SemantycaNM\Administrator\Exception\NewsletterSenderException;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewsLetterModel;

class SMTPSender
{
	private $mailer;
	private $config;
	private NewsLetterModel $newsletterModel;
	private const TRACKING_PIXEL_TEMPLATE = '<img src="%sindex.php?option=com_semantycanm&task=SiteSubscriberEvent.postEvent&id=%s" width="1" height="1" alt="" style="display:none;">';


	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function __construct($newsletterModel)
	{
		$this->newsletterModel = $newsletterModel;
		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_BASE . '/includes/framework.php';

		$this->config = Factory::getConfig();
		$this->mailer = new PHPMailer(true);
		$this->setupMailer();
	}

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	private function setupMailer(): void
	{
		$this->mailer->SMTPDebug = 0;

		$this->mailer->Debugoutput = function($str, $level) {
			error_log("SMTP debug level $level: $str");
		};

		$this->mailer->isSMTP();
		$this->mailer->Host       = $this->config->get('smtphost');
		$this->mailer->SMTPAuth   = $this->config->get('smtpauth');
		$this->mailer->Username   = $this->config->get('smtpuser');
		$this->mailer->Password   = $this->config->get('smtppass');
		$this->mailer->SMTPSecure = $this->config->get('smtpsecure');
		$this->mailer->Port       = $this->config->get('smtpport');
		$this->mailer->setFrom($this->config->get('mailfrom'), $this->config->get('fromname'));
	}

	/**
	 * @throws NewsletterSenderException|Exception
	 * @since 1.0
	 */
	public function sendNewsletter($newsletterId, $baseURL): bool
	{
		$results = $this->newsletterModel->findUnprocessedEvent($newsletterId, Constants::EVENT_TYPE_READ);
		if (empty($results)) {
			throw new NewsletterSenderException(["No data found for newsletters ID " . $newsletterId]);
		}

		foreach ($results as $data) {
			$email = $data['subscriber_email'];
			$subject = $data['subject'];
			$messageContent = $data['message_content'];
			$readTriggerToken = $data['trigger_token'];

			if (!$this->sendEmail($email, $subject, $messageContent, $readTriggerToken, $baseURL)) {
				throw new NewsletterSenderException(["Failed to send to: " . $email]);
			} else {
				$this->newsletterModel->updateFulfilledForEvents(Constants::EVENT_TYPE_DISPATCHED, $newsletterId, $email);
			}
		}

		return true;
	}


	/**
	 * @throws Exception
	 * @since 1.0
	 */
	private function sendEmail($email, $subject, $messageContent, $readTriggerToken, $baseURL): bool
	{
		$trackingPixel  = sprintf(self::TRACKING_PIXEL_TEMPLATE, $baseURL, urlencode($readTriggerToken));
		$customizedBody = str_replace('</body>', $trackingPixel . '</body>', $messageContent);

		$this->mailer->addAddress($email);
		$this->mailer->isHTML(true);
		$this->mailer->Subject = $subject;
		$this->mailer->Body    = $customizedBody;

		$result = $this->mailer->send();

		$this->mailer->clearAllRecipients();

		return $result;
	}
}
