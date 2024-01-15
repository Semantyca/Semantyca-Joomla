<?php

use Joomla\CMS\HTML\Helpers\Bootstrap;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Semantyca\Component\SemantycaNM\Site\Helper\SiteConsts;

HTMLHelper::_('form.csrf', '_csrf');
HTMLHelper::_('jquery.framework');

$app = Joomla\CMS\Factory::getApplication();
$doc = $app->getDocument();
global $media;


$media = "administrator/components/com_semantycanm/assets/";
$rootUrl = JUri::root();
$bannerImageUrl = $rootUrl . "images/2020/EMSA_logo_full_600-ed.png";
$host = JUri::getInstance()->toString(['host']);
$doc->addScript($rootUrl . $media . "js/Pagination.js");
$doc->addScript($rootUrl . $media . "js/MailingListRequest.js");
$doc->addScript($rootUrl . $media . "js/NewsletterRequest.js");
$doc->addScript($rootUrl . $media . "js/Sortable.min.js");
$doc->addScript($rootUrl . $media . "js/trumbowyg.min.js");
$doc->addScript($rootUrl . $media . "js/bootstrap.min.js");
$doc->addScript($rootUrl . $media . "js/templates.js");
$doc->addScript($rootUrl . $media . "js/typeahead.bundle.js");
$doc->addScript($rootUrl . $media . "js/common.js");
$doc->addScript($rootUrl . $media . "js/dragdrop.js");
$doc->addScript($rootUrl . $media . "js/newsletter-builder.js");
$doc->addStyleSheet($rootUrl . $media . "css/trumbowyg.min.css");
$doc->addStyleSheet($rootUrl . $media . "css/default.css");
$doc->addStyleSheet($rootUrl . $media . "css/dragdrop.css");



try
{
	Bootstrap::tab('#nav-tab');
}
catch (Exception $e)
{
	Log::add($e, Log::ERROR, SiteConsts::COMPONENT_NAME);
}

$this->usergroups      = $this->user_groups;
$this->selectedLetters = [];

?>

<div id="alertPlaceholder"></div>
<div class="bd-example">
    <nav>
        <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
            <button class="nav-link active" id="nav-list-tab" data-bs-toggle="tab" data-bs-target="#nav-list"
                    type="button"
                    role="tab" aria-controls="nav-list"
                    aria-selected="true"><?php echo JText::_('LISTS_TAB'); ?></button>
            <button class="nav-link" id="nav-composer-tab" data-bs-toggle="tab" data-bs-target="#nav-composer"
                    type="button"
                    role="tab" aria-controls="nav-composer"
                    aria-selected="false"><?php echo JText::_('COMPOSER_TAB'); ?></button>
            <button class="nav-link" id="nav-newsletters-tab" data-bs-toggle="tab" data-bs-target="#nav-newsletters"
                    type="button"
                    role="tab" aria-controls="nav-newsletters"
                    aria-selected="false"><?php echo JText::_('NEWSLETTERS_TAB'); ?></button>
            <button class="nav-link" id="nav-stats-tab" data-bs-toggle="tab" data-bs-target="#nav-stats" type="button"
                    role="tab" aria-controls="nav-stats"
                    aria-selected="false"><?php echo JText::_('STATS_TAB'); ?></button>
            <button class="nav-link" id="nav-template-tab" data-bs-toggle="tab" data-bs-target="#nav-template"
                    type="button"
                    role="tab" aria-controls="nav-template"
                    aria-selected="false"><?php echo JText::_('TEMPLATES_TAB'); ?></button>


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
        <div class="tab-pane fade" id="nav-template" role="tabpanel" aria-labelledby="nav-template-tab">
		    <?php echo $this->loadTemplate('template_tab'); ?>
        </div>
    </div>
</div>


<script>
    const ITEMS_PER_PAGE = 5;
    const host = window.location.protocol + '//' + window.location.hostname;
    const port = window.location.port;
    const joomlaHost = host + (port ? ':' + port : '');
    const bannerUrl = "<?php echo htmlspecialchars($bannerImageUrl); ?>"
    const removeButtonText = "<?php echo JText::_('REMOVE'); ?>";
</script>


