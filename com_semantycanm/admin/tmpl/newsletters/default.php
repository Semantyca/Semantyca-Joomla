<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('form.csrf', '_csrf');
HTMLHelper::_('jquery.framework');

$app = Joomla\CMS\Factory::getApplication();
$doc = $app->getDocument();
global $smtca_assets;

$smtca_assets = "administrator/components/com_semantycanm/assets/";
$rootUrl      = JUri::root();

$translations     = array(
	'CREATE'         => Text::_('CREATE'),
	'BACK'           => Text::_('BACK'),
	'SAVE'           => Text::_('SAVE'),
	'SAVE_AND_CLOSE' => Text::_('SAVE_AND_CLOSE'),
	'CLOSE'          => Text::_('CLOSE'),
	'SEND'           => Text::_('SEND'),
	'DELETE'         => Text::_('DELETE'),
	'REVIEW'         => Text::_('REVIEW'),
	'EDIT'           => Text::_('EDIT'),
	'RESET'          => Text::_('RESET'),
	'CANCEL'         => Text::_('CANCEL'),
	'SUBJECT'        => Text::_('SUBJECT'),
	'FETCH_SUBJECT'  => Text::_('FETCH_SUBJECT'),
);
$jsonTranslations = json_encode($translations);

?>
<script type="module">
    window.globalTranslations = <?php echo $jsonTranslations; ?>;
</script>

<div id="app" data-menu-id="newsletters">Loading...</div>

<?php foreach ($this->js_bundles as $js_bundle): ?>
    <script type="module" src="<?php echo $rootUrl . $smtca_assets . $js_bundle; ?>"></script>
<?php endforeach; ?>






