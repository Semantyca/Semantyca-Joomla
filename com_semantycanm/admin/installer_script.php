<?php
defined('_JEXEC') or die('Restricted access');

class Com_SemantycanmInstallerScript
{
	public function install($parent)
	{
		$this->cleanBundleDirectory();
	}

	public function update($parent)
	{
		$this->cleanBundleDirectory();
	}

	public function uninstall($parent)
	{

	}

	public function preflight($type, $parent)
	{

	}

	public function postflight($type, $parent)
	{

	}

	private function cleanBundleDirectory()
	{
		$bundleDir = JPATH_ADMINISTRATOR . '/components/com_semantycanm/assets/bundle';
		if (is_dir($bundleDir)) {
			$this->deleteDirectoryContents($bundleDir);
		}
	}

	private function deleteDirectoryContents($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			$path = $dir . '/' . $file;
			if (is_dir($path)) {
				$this->deleteDirectoryContents($path);
				rmdir($path);
			} else {
				unlink($path);
			}
		}
	}
}
