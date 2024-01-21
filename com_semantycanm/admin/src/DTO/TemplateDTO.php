<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

class TemplateDTO
{
	public $id;
	public $regDate;
	public $name;
	public $maxArticles;
	public $maxArticlesShort;
	public $content;
	public $wrapper;

	public function toArray()
	{
		return [
			'id'               => $this->id,
			'name'             => $this->name,
			'maxArticles'      => $this->maxArticles,
			'maxArticlesShort' => $this->maxArticlesShort,
			'content' => $this->content,
			'wrapper' => $this->wrapper
		];
	}
}
