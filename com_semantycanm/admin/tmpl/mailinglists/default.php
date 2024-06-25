<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('form.csrf', '_csrf');
HTMLHelper::_('jquery.framework');

$smtca_assets = "administrator/components/com_semantycanm/assets/";
$rootUrl      = JUri::root();

$translations     = array(
	'CREATE' => Text::_('CREATE'),
	'SAVE'   => Text::_('SAVE'),
	'DELETE' => Text::_('DELETE'),
);
$jsonTranslations = json_encode($translations);

?>
<script type="module">
    window.globalTranslations = <?php echo $jsonTranslations; ?>;
</script>

<div id="app" data-menu-id="mailing_lists">Loading...</div>

<?php foreach ($this->js_bundles as $js_bundle): ?>
    <script type="module" src="<?php echo $rootUrl . $smtca_assets . $js_bundle; ?>"></script>
<?php endforeach; ?>
