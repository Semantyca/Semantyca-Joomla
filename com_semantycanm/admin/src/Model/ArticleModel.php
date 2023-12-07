<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use ContentHelperRoute;
use JFactory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use JRoute;

class ArticleModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
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
					$db->quoteName('c.title', 'category')
				))
				->from($db->quoteName('#__content', 'a'))
				->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')')
				->where($db->quoteName('a.state') . ' = 1')
				->where($db->quoteName('a.created') . ' > ' . $threeMonthsAgo)
				->order('a.created DESC');
			$db->setQuery($query);

			$articles = $db->loadObjectList();
			foreach ($articles as $article) {
				$article->url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id));
			}
			return $articles;
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');

			return null;
		}
	}

	public function find($id)
	{
		try
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
			foreach ($articles as $article) {
				$article->url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id));
			}
			return $articles;
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');

			return null;
		}
	}


	public function search($searchTerm)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__content')
				->where('title LIKE ' . $db->quote('%' . $searchTerm . '%'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');

			return null;
		}
	}
}
