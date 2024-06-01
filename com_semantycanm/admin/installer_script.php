<?php
defined('_JEXEC') or die('Restricted access');

class Com_SemantycanmInstallerScript
{
	public function preflight($type, $parent)
	{
		if ($type === 'install' || $type === 'update') {
			$this->cleanBundleDirectory();
		}
	}

	public function install($parent)
	{
	}

	public function update($parent)
	{
	}

	public function uninstall($parent)
	{
	}

	public function postflight($type, $parent)
	{
	}

	private function cleanBundleDirectory()
	{
		$bundleDir = JPATH_ADMINISTRATOR . '/components/com_semantycanm/assets/bundle';
		if (is_dir($bundleDir)) {
			$this->logMessage("Cleaning directory: {$bundleDir}");
			$this->deleteFiles($bundleDir);
		} else {
			$this->logMessage("Directory does not exist: {$bundleDir}");
		}
	}

	private function deleteFiles($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			$path = $dir . '/' . $file;
			if (is_file($path)) {
				unlink($path);
				$this->logMessage("Removed file: {$path}");
			}
		}
	}

	private function logMessage($message)
	{
		JLog::add($message, JLog::INFO, 'com_semantycanm');
	}
}
