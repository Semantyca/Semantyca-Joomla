<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('form.csrf', '_csrf');
HTMLHelper::_('jquery.framework');

$smtca_assets = "administrator/components/com_semantycanm/assets/";
$rootUrl      = JUri::root();

$translations     = array(
	'SAVE'   => Text::_('SAVE'),
	'DELETE' => Text::_('DELETE'),
);
$jsonTranslations = json_encode($translations);

?>
<script type="module">
    window.globalTranslations = <?php echo $jsonTranslations; ?>;
</script>

<div id="app" data-menu-id="template">Loading...</div>

<?php if (isset($this->js_bundle) && $this->js_bundle): ?>
    <script type="module" src="<?php echo $rootUrl . $smtca_assets . $this->js_bundle; ?>"></script>
<?php endif; ?>
