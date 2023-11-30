<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

class ArticleController extends BaseController
{
	public function search()
	{
		try
		{
			$search = $this->input->getString('q');
			$article_model = $this->getModel('Article');
			$results = $article_model->search($search);
			$view = $this->getView('Article', 'json');
			$view->set('articles',  $results);
		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');
		}
	}
}
