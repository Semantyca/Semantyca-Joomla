<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use ContentHelperRoute;
use JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use JRoute;

class ArticleModel extends BaseDatabaseModel
{
	public function getList()
	{
		$db   = $this->getDatabase();
		$date = JFactory::getDate();
		$date->modify('-3 months');
		$threeMonthsAgo = $db->quote($date->toSql());
		$query          = $db->getQuery(true)
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
			->where($db->quoteName('a.created') . ' > ' . $threeMonthsAgo)
			->order('a.created DESC');
		$db->setQuery($query);

		$articles = $db->loadObjectList();
		foreach ($articles as $article)
		{
			$article->url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
		}

		return $articles;
	}

	public function find($id)
	{
		$db   = $this->getDatabase();
		$date = JFactory::getDate();
		$date->modify('-3 months');
		$threeMonthsAgo = $db->quote($date->toSql());
		$query          = $db->getQuery(true)
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
			->where($db->quoteName('a.created') . ' > ' . $threeMonthsAgo)
			->where('a.id = ' . $db->quote($id))
			->order('a.created DESC');
		$db->setQuery($query);

		$articles = $db->loadObjectList();
		foreach ($articles as $article)
		{
			$article->url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
		}

		return $articles;

	}

	public function search($searchTerm)
	{
		$db   = $this->getDatabase();
		$date = JFactory::getDate();
		$date->modify('-3 months');
		$threeMonthsAgo = $db->quote($date->toSql());
		$query          = $db->getQuery(true)
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
			->where($db->quoteName('a.created') . ' > ' . $threeMonthsAgo)
			->where($db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . $searchTerm . '%'))
			->order('a.created DESC');

		$db->setQuery($query);
		$articles = $db->loadObjectList();
		foreach ($articles as $article)
		{
			$article->url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));

		}

		return $articles;

	}

}
