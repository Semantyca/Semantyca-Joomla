<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;


class ServiceController extends BaseController
{
	public function sendEmail()
	{
		try
		{
			$request    = $this->input->post->get('body', '', 'RAW');
			$subject    = $this->input->post->get('subject', '', 'RAW');
			$user_group = $this->input->post->get('user_group', '', 'RAW');

			$mailingListModel = $this->getModel('MailingList');
			$statModel        = $this->getModel('Stat');

			$result = (new Messaging($mailingListModel, $statModel))->sendEmail($request, $subject, $user_group);
			header('Content-Type: application/json; charset=UTF-8');
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}
		catch
		(\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
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
			Factory::getApplication()->close();

		}
		catch
		(\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}

}
