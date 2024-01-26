<?php

use Joomla\CMS\HTML\Helpers\Bootstrap;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Semantyca\Component\SemantycaNM\Site\Helper\SiteConsts;

HTMLHelper::_('form.csrf', '_csrf');
HTMLHelper::_('jquery.framework');

$app = Joomla\CMS\Factory::getApplication();
$doc = $app->getDocument();
global $smtca_assets;


$smtca_assets = "administrator/components/com_semantycanm/assets/";
$rootUrl      = JUri::root();
$host         = JUri::getInstance()->toString(['host']);
$doc->addScript($rootUrl . $smtca_assets . "js/Pagination.js");
$doc->addScript($rootUrl . $smtca_assets . "js/MailingListRequest.js");
$doc->addScript($rootUrl . $smtca_assets . "js/NewsletterRequest.js");
$doc->addScript($rootUrl . $smtca_assets . "js/Sortable.min.js");
$doc->addScript($rootUrl . $smtca_assets . "js/bootstrap.min.js");
$doc->addScript($rootUrl . $smtca_assets . "js/typeahead.bundle.js");
$doc->addScript($rootUrl . $smtca_assets . "js/common.js");
$doc->addScript($rootUrl . $smtca_assets . "js/dragdrop.js");
$doc->addScript($rootUrl . $smtca_assets . "js/newsletter-builder.js");
$doc->addScript($rootUrl . $smtca_assets . "js/handlebars.min.js");
$doc->addStyleSheet($rootUrl . $smtca_assets . "css/default.css");
$doc->addStyleSheet($rootUrl . $smtca_assets . "css/dragdrop.css");

$translations     = array(
	'NEXT'               => JText::_('NEXT'),
	'PREVIOUS'           => JText::_('PREVIOUS'),
	'LAST'               => JText::_('LAST'),
	'FIRST'              => JText::_('FIRST'),
	'STATUS'             => JText::_('STATUS'),
	'SEND_TIME'          => JText::_('SEND_TIME'),
	'RECIPIENTS'         => JText::_('RECIPIENTS'),
	'OPENS'              => JText::_('OPENS'),
	'CLICKS'             => JText::_('CLICKS'),
	'UNSUBS'             => JText::_('UNSUBS'),
	'AVAILABLE_ARTICLES' => JText::_('AVAILABLE_ARTICLES'),
	'SELECTED_ARTICLES'  => JText::_('SELECTED_ARTICLES'),
	'STATISTICS'         => JText::_('STATISTICS'),
	'RESET'              => JText::_('RESET'),
	'COPY_CODE'          => JText::_('COPY_CODE'),
	'SAVE'             => JText::_('SAVE'),
	'AVAILABLE_LISTS'  => JText::_('AVAILABLE_LISTS'),
	'SELECTED_LISTS'   => JText::_('SELECTED_LISTS'),
	'SEND_NEWSLETTER'  => JText::_('SEND_NEWSLETTER'),
	'TEST_ADDRESS'     => JText::_('TEST_ADDRESS'),
	'FETCH_SUBJECT'    => JText::_('FETCH_SUBJECT'),
	'MESSAGE_CONTENT'  => JText::_('MESSAGE_CONTENT'),
	'SAVE_NEWSLETTER'  => JText::_('SAVE_NEWSLETTER'),
	'EDIT'             => JText::_('EDIT'),
	'NEWSLETTERS_LIST' => JText::_('NEWSLETTERS_LIST'),
	'SUBJECT'          => JText::_('SUBJECT'),
	'REGISTERED'       => JText::_('REGISTERED'),

);
$jsonTranslations = json_encode($translations);


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
<script type="module">
    window.tinymceLic = "<?php echo $this->tinymce_lic; ?>";
    window.globalTranslations = <?php echo $jsonTranslations; ?>;
</script>


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
            <div class="container mt-2" id="composerSection">Composer is not available</div>
        </div>
        <div class="tab-pane fade" id="nav-newsletters" role="tabpanel" aria-labelledby="nav-newsletters-tab">
            <div class="container mt-2" id="newletterSection">Newsletter dashboard is not available</div>
        </div>
        <div class="tab-pane fade" id="nav-stats" role="tabpanel" aria-labelledby="nav-stats-tab">
            <div class="container mt-2" id="statSection">Stat is not available</div>
        </div>
        <div class="tab-pane fade" id="nav-template" role="tabpanel" aria-labelledby="nav-template-tab">
            <div class="container mt-2" id="templateSection">Template editor is not available</div>
        </div>
    </div>
</div>

<?php if (isset($this->js_bundle) && $this->js_bundle): ?>
    <script type="module" src="<?php echo $rootUrl . $smtca_assets . $this->js_bundle; ?>"></script>
<?php endif; ?>


<script type="module">

</script>


