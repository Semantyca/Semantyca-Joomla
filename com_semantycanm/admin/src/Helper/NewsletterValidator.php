<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use DateTime;
use Semantyca\Component\SemantycaNM\Administrator\DTO\NewsletterDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;

class NewsletterValidator
{
	public static function validateAndCreateDTO(array $input): NewsletterDTO
	{
		if (empty($input['content']))
		{
			throw new ValidationErrorException(['Message content is required']);
		}

		if (empty($input['subject']))
		{
			throw new ValidationErrorException(['Subject is required']);
		}

		$isTest = $input['isTestMessage'] ?? false;

		if (empty($input['templateId']))
		{
			throw new ValidationErrorException(['Template ID is required']);
		}

		if ($isTest)
		{
			if (empty($input['testEmail']))
			{
				throw new ValidationErrorException(['Test email is required when isTest is true']);
			}
		}
		else
		{
			if (empty($input['mailingList']))
			{
				throw new ValidationErrorException(['Mailing list is required when isTest is false']);
			}
		}

		$newsletterDTO                     = new NewsletterDTO();
		$newsletterDTO->regDate            = new DateTime();
		$newsletterDTO->id                 = $input['id'];
		$newsletterDTO->templateId         = $input['templateId'];
		$newsletterDTO->customFieldsValues = json_encode($input['customFieldsValues'] ?? []);
		$newsletterDTO->articlesIds        = $input['articlesIds'] ?? [];
		$newsletterDTO->isTest             = $input['isTestMessage'];
		$newsletterDTO->mailingListIds     = $input['mailingList'] ?? [];
		$newsletterDTO->testEmail          = $input['testEmail'] ?? '';
		$newsletterDTO->subject            = $input['subject'];
		$newsletterDTO->messageContent     = $input['content'];
		$newsletterDTO->useWrapper         = $input['useWrapper'];

		return $newsletterDTO;
	}
}