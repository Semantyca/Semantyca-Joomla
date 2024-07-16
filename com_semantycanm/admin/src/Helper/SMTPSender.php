<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Semantyca\Component\SemantycaNM\Administrator\Exception\NewsletterSenderException;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewslettersModel;

class SMTPSender
{
	private $mailer;
	private $config;
	private NewslettersModel $newsletterModel;

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
			$useWrapper = $data['use_wrapper'];
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
		$trackingPixelUrl = $baseURL . 'index.php?option=com_semantycanm&task=SiteSubscriberEvent.postEvent&id=' . urlencode($readTriggerToken);
		$trackingPixel = '<img src="' . $trackingPixelUrl . '" width="1" height="1" alt="" style="display:none;">';
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
