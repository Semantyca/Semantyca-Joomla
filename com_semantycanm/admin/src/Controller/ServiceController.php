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
		header('Content-Type: application/json; charset=UTF-8');
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

	public function postStat()
	{
		try
		{
			$id = $this->input->post->get('id', '', 'RAW');

			$statModel = $this->getModel('Stat');
			$statModel->updateStatRecord($id, Constants::HAS_BEEN_READ);
			header("Content-type: image/png");
			echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/wHtRbk7AAA=");
		}
		catch
		(\Exception $e)
		{
			header('Content-Type: application/json; charset=UTF-8');
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
		header('Content-Type: application/json; charset=UTF-8');
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
