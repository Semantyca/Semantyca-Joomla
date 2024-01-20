<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

class TemplateDTO
{
	public $id;
	public $regDate;
	public $name;
	public $maxArticles;
	public $maxArticlesShort;
	public $html;
	public $dynamic;
	public $main;
	public $ending;
	public $wrapper;
	public $dynamicShort;

	public function toArray()
	{
		return [
			'id'               => $this->id,
			'name'             => $this->name,
			'maxArticles'      => $this->maxArticles,
			'maxArticlesShort' => $this->maxArticlesShort,
			'html' => $this->html,
			'dynamic'          => $this->dynamic,
			'main'             => $this->main,
			'ending'           => $this->ending,
			'wrapper'          => $this->wrapper,
			'dynamicShort'     => $this->dynamicShort,
		];
	}
}
