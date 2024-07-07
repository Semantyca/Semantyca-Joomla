<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use JFactory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class ArticleModel extends BaseDatabaseModel
{
	protected string $base;

	public function __construct()
	{
		parent::__construct();
		$this->base = Uri::root();
	}

	public function getList()
	{
		$params = ComponentHelper::getParams(Constants::COMPONENT_NAME);
		$days   = $params->get('retrieval_gap_in_days', 1);
		$db     = $this->getDatabase();
		$date   = JFactory::getDate();
		$date->modify("-$days days");
		$dateCondition = $db->quote($date->toSql());
		$query         = $db->getQuery(true)
			->select(array(
				$db->quoteName('a.id'),
				$db->quoteName('a.title'),
				$db->quoteName('a.introtext'),
				$db->quoteName('a.alias'),
				$db->quoteName('a.catid'),
				$db->quoteName('c.title', 'category')
			))
			->from($db->quoteName('#__content', 'a'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')')
			->where($db->quoteName('a.state') . ' = 1')
			->where($db->quoteName('a.created') . ' > ' . $dateCondition)
			->order('a.created DESC');
		$db->setQuery($query);

		$articles = $db->loadObjectList();
		foreach ($articles as $article)
		{
			$article->url = $this->constructArticleUrl($article);
		}

		return $articles;
	}


	public function find($id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(array(
				$db->quoteName('a.id'),
				$db->quoteName('a.title'),
				$db->quoteName('a.introtext'),
				$db->quoteName('a.alias'),
				$db->quoteName('a.catid'),
				$db->quoteName('c.title', 'category')
			))
			->from($db->quoteName('#__content', 'a'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')')
			->where($db->quoteName('a.state') . ' = 1')
			->where('a.id = ' . $db->quote($id))
			->order('a.created DESC');
		$db->setQuery($query);

		$articles = $db->loadObjectList();
		foreach ($articles as $article)
		{
			$article->url = $this->constructArticleUrl($article);
		}

		return $articles;
	}


	public function search($searchTerm)
	{
		$params = ComponentHelper::getParams(Constants::COMPONENT_NAME);
		$days = $params->get('retrieval_gap_in_days', 365);
		$db = $this->getDatabase();
		$date = JFactory::getDate();
		$date->modify("-$days days");
		$dateCondition = $db->quote($date->toSql());
		$query = $db->getQuery(true)
			->select(array(
				$db->quoteName('a.id'),
				$db->quoteName('a.title'),
				$db->quoteName('a.introtext'),
				$db->quoteName('a.alias'),
				$db->quoteName('a.catid'),
				$db->quoteName('c.title', 'category')
			))
			->from($db->quoteName('#__content', 'a'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')')
			->where($db->quoteName('a.state') . ' = 1')
			->where($db->quoteName('a.created') . ' > ' . $dateCondition);

		if (!empty($searchTerm))
		{
			$query->where($db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . $searchTerm . '%'));
		}

		$query->order('a.created DESC');
		$db->setQuery($query, 0, 10);
		$articles = $db->loadObjectList();
		foreach ($articles as $article)
		{
			$article->url = $this->constructArticleUrl($article);
		}

		return $articles;
	}


	private function constructArticleUrl($article): string
	{
		$relativeUrl = 'index.php?option=com_content&view=article&id='
			. $article->id . '&catid=' . $article->catid . $this->getItemId($article);

		$parsedBase = parse_url($this->base);

		return $parsedBase['scheme'] . '://' . $parsedBase['host'] . $parsedBase['path'] . $relativeUrl;
	}

	private function getItemId($article): string
	{
		$params         = ComponentHelper::getParams(Constants::COMPONENT_NAME);
		$itemIdSourcing = $params->get('itemid_sourcing', 0);

		switch ($itemIdSourcing)
		{
			case 'custom':
				return '&Itemid=' . $params->get('defined_item_id', 1);
			case 'smart':
				$db        = $this->getDatabase();
				$menuQuery = $db->getQuery(true)
					->select($db->quoteName('id'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_content&view=category&layout=blog&id=' . (int) $article->catid . '%'))
					->where($db->quoteName('published') . ' = 1')
					->setLimit(1);

				$db->setQuery($menuQuery);
				$itemId = $db->loadResult();
				if ($itemId)
				{
					return '&Itemid=' . $itemId;
				}
				else
				{
					LogHelper::logWarn('The itemId has not been resolved with the smart option', __CLASS__);

					return '&Itemid=';
				}
			default:
				return '&Itemid=';
		}
	}

}
