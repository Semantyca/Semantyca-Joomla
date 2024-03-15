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
	public $banner;
	public $wrapper;
	public $customFields = []; // Added customFields as an empty array by default

	public function toArray()
	{
		return [
			'id'               => $this->id,
			'name'             => $this->name,
			'maxArticles'      => $this->maxArticles,
			'maxArticlesShort' => $this->maxArticlesShort,
			'content'      => $this->content,
			'banner'       => $this->banner,
			'wrapper'      => $this->wrapper,
			'customFields' => $this->customFields // Include customFields in the array representation
		];
	}
}
