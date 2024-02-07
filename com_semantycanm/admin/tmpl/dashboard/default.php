<?php

use Joomla\CMS\HTML\HTMLHelper;

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
$doc->addScript($rootUrl . $smtca_assets . "js/handlebars.min.js");
$doc->addStyleSheet($rootUrl . $smtca_assets . "css/default.css");
$doc->addStyleSheet($rootUrl . $smtca_assets . "css/dragdrop.css");

$translations     = array(
	'NEXT'                  => JText::_('NEXT'),
	'PREVIOUS'              => JText::_('PREVIOUS'),
	'LAST'                  => JText::_('LAST'),
	'FIRST'                 => JText::_('FIRST'),
	'STATUS'                => JText::_('STATUS'),
	'SEND_TIME'             => JText::_('SEND_TIME'),
	'RECIPIENTS'            => JText::_('RECIPIENTS'),
	'OPENS'                 => JText::_('OPENS'),
	'CLICKS'                => JText::_('CLICKS'),
	'UNSUBS'                => JText::_('UNSUBS'),
	'AVAILABLE_ARTICLES'    => JText::_('AVAILABLE_ARTICLES'),
	'SELECTED_ARTICLES'     => JText::_('SELECTED_ARTICLES'),
	'STATISTICS'            => JText::_('STATISTICS'),
	'RESET'                 => JText::_('RESET'),
	'COPY_CODE'             => JText::_('COPY_CODE'),
	'SAVE'                  => JText::_('SAVE'),
	'AVAILABLE_LISTS'       => JText::_('AVAILABLE_LISTS'),
	'SELECTED_LISTS'        => JText::_('SELECTED_LISTS'),
	'SEND_NEWSLETTER'       => JText::_('SEND_NEWSLETTER'),
	'TEST_ADDRESS'          => JText::_('TEST_ADDRESS'),
	'FETCH_SUBJECT'         => JText::_('FETCH_SUBJECT'),
	'MESSAGE_CONTENT'       => JText::_('MESSAGE_CONTENT'),
	'SAVE_NEWSLETTER'       => JText::_('SAVE_NEWSLETTER'),
	'EDIT'                  => JText::_('EDIT'),
	'NEWSLETTERS_LIST'      => JText::_('NEWSLETTERS_LIST'),
	'SUBJECT'               => JText::_('SUBJECT'),
	'REGISTERED'            => JText::_('REGISTERED'),
	'MAILING_LISTS'         => JText::_('MAILING_LISTS'),
	'AVAILABLE_USER_GROUPS' => JText::_('AVAILABLE_USER_GROUPS'),
	'SELECTED_USER_GROUPS'  => JText::_('SELECTED_USER_GROUPS'),
	'CANCEL'                => JText::_('CANCEL'),
	'TEMPLATE' => JText::_('TEMPLATE'),

);
$jsonTranslations = json_encode($translations);

?>
<script type="module">
    window.tinymceLic = "<?php echo $this->tinymce_lic; ?>";
    window.globalTranslations = <?php echo $jsonTranslations; ?>;
</script>

<div id="loadingSpinner" class="loading-spinner"></div>
<div id="app">Loading...</div>


<?php if (isset($this->js_bundle) && $this->js_bundle): ?>
    <script type="module" src="<?php echo $rootUrl . $smtca_assets . $this->js_bundle; ?>"></script>
<?php endif; ?>




