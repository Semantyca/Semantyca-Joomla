<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Bootstrap;
use Joomla\CMS\Log\Log;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

HTMLHelper::_('form.csrf', '_csrf');
HTMLHelper::_('jquery.framework');

$app = Joomla\CMS\Factory::getApplication();
$doc = $app->getDocument();
global $media;
$media = "administrator/components/com_semantycanm/assets/";

//$doc->addScript(JURI::root() . $media . "js/jquery-3.7.1.min.js");
$doc->addScript(JURI::root() . $media . "js/Sortable.min.js");
$doc->addScript(JURI::root() . $media . "js/trumbowyg.min.js");
$doc->addScript(JUri::root() . $media . "js/bootstrap.min.js");
$doc->addScript(JURI::root() . $media . "js/templates.js");
$doc->addScript(JUri::root() . $media . "js/typeahead.bundle.js");
$doc->addScript(JUri::root() . $media . "js/common.js");
$doc->addScript(JUri::root() . $media . "js/dragdrop.js");
$doc->addScript(JUri::root() . $media . "js/newsletter-builder.js");
$doc->addStyleSheet(JUri::root() . $media . "css/trumbowyg.min.css");
$doc->addStyleSheet(JUri::root() . $media . "css/default.css");
$doc->addStyleSheet(JUri::root() . $media . "css/dragdrop.css");

try
{
	Bootstrap::tab('#nav-tab');
}
catch (Exception $e)
{
	Log::add($e, Log::ERROR, Constants::COMPONENT_NAME);
}

$this->usergroups      = $this->user_groups;
$this->articlesList    = $this->articles;
$this->mailingLists    = $this->mailing_lists;
$this->selectedLetters = [];

?>


<div class="bd-example">
    <nav>
        <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
            <button class="nav-link active" id="nav-list-tab" data-bs-toggle="tab" data-bs-target="#nav-list"
                    type="button"
                    role="tab" aria-controls="nav-list"
                    aria-selected="true"><?php echo JText::_('COM_SEMANTYCANM_LISTS'); ?></button>
            <button class="nav-link" id="nav-composer-tab" data-bs-toggle="tab" data-bs-target="#nav-composer"
                    type="button"
                    role="tab" aria-controls="nav-composer"
                    aria-selected="false"><?php echo JText::_('COM_SEMANTYCANM_COMPOSER'); ?></button>
            <button class="nav-link" id="nav-newsletters-tab" data-bs-toggle="tab" data-bs-target="#nav-newsletters"
                    type="button"
                    role="tab" aria-controls="nav-newsletters"
                    aria-selected="false"><?php echo JText::_('COM_SEMANTYCANM_NEWSLETTERS'); ?></button>
            <button class="nav-link" id="nav-stats-tab" data-bs-toggle="tab" data-bs-target="#nav-stats" type="button"
                    role="tab" aria-controls="nav-stats"
                    aria-selected="false"><?php echo JText::_('COM_SEMANTYCANM_STATS'); ?></button>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
			<?php echo $this->loadTemplate('lists_tab'); ?>
        </div>
        <div class="tab-pane fade" id="nav-composer" role="tabpanel" aria-labelledby="nav-composer-tab">
			<?php echo $this->loadTemplate('composer_tab'); ?>
        </div>
        <div class="tab-pane fade" id="nav-newsletters" role="tabpanel" aria-labelledby="nav-newsletters-tab">
			<?php echo $this->loadTemplate('news_letter_tab'); ?>
        </div>
        <div class="tab-pane fade" id="nav-stats" role="tabpanel" aria-labelledby="nav-stats-tab">
			<?php echo $this->loadTemplate('stats_tab'); ?>
        </div>
    </div>
</div>


