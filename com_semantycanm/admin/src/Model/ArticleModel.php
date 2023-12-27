<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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
			$article->url = $this->constructArticleUrl($article);
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
			$article->url = $this->constructArticleUrl($article);
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
			$article->url = $this->constructArticleUrl($article);
		}

		return $articles;

	}

	private function constructArticleUrl($article): string
	{
		$relativeUrl = 'index.php?option=com_content&view=article&id=' . $article->id . '&catid=' . $article->catid;
		$relativeUrl = str_replace("/administrator", "", html_entity_decode(Route::_($relativeUrl)));

		$parsedBase     = parse_url($this->base);
		$parsedRelative = parse_url($relativeUrl);

		$basePath     = rtrim($parsedBase['path'], '/') . '/';
		$relativePath = ltrim($parsedRelative['path'], '/');

		if (substr($relativePath, 0, strlen($basePath)) == $basePath)
		{
			$relativePath = substr($relativePath, strlen($basePath));
		}

		$finalUrl = $parsedBase['scheme'] . '://' . $parsedBase['host'] . $basePath . $relativePath;

		if (isset($parsedRelative['query']))
		{
			$finalUrl .= '?' . $parsedRelative['query'];
		}

		return $finalUrl;
	}


}
