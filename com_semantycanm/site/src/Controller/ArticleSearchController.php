<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class ArticleSearchController extends BaseController
{
	public function search()
	{
		$searchTerms = $this->input->getString('searchTerms', '');
		$model = $this->getModel('Articles');
		$articles = $model->getItems([
			'search' => $searchTerms,
		]);
		$response = [];
		foreach ($articles as $article) {
			$response[] = $article->title;
		}
		echo json_encode($response);
	}
}
