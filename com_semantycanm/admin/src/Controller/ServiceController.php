<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\MessagingException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;


class ServiceController extends BaseController
{
	public function sendEmail()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$subject     = $this->input->post->get('subject', '', 'RAW');
			$user_group  = $this->input->post->get('user_group', '', 'RAW');
			$encodedBody = $this->input->post->get('encoded_body', '', 'RAW');

			$errors = [];

			if (empty($user_group))
			{
				$errors[] = 'User group is required';
			}

			if (empty($encodedBody))
			{
				$errors[] = 'Encoded body is required';
			}

			if (!empty($errors))
			{
				throw new ValidationErrorException($errors);
			}

			$mailingListModel = $this->getModel('MailingList');
			$statModel        = $this->getModel('Stat');
			$newLetterModel   = $this->getModel('NewsLetter');

			$newsLetterUpsertResults = $newLetterModel->upsert($subject, $encodedBody);

			$messagingHelper = new Messaging($mailingListModel, $statModel);
			$result          = $messagingHelper->sendEmail(urldecode($encodedBody), $subject, $user_group, $newsLetterUpsertResults);
			if ($result)
			{
				echo new JsonResponse($result);
			}
			else
			{
				throw new MessagingException(['An error happened while sending the message']);

			}

		}
		catch (ValidationErrorException|MessagingException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getErrors(), 'Error', true);

		}
		catch (\Exception $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function getSubject()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$type = $this->input->get->get('type', '', 'RAW');
			if ($type == 'random')
			{
				$util   = new Util();
				$result = $util->generateSubject();
			}
			else
			{
				$result = 'no subject';
			}
			echo new JsonResponse($result);
		}
		catch
		(\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
