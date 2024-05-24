<?php
defined('_JEXEC') or die('Restricted access');

class Com_SemantycanmInstallerScript
{
	public function install($parent)
	{
		$this->logMessage('Install: Cleaning bundle directory...');
		$this->cleanBundleDirectory();
	}

	public function update($parent)
	{
		$this->logMessage('Update: Cleaning bundle directory...');
		$this->cleanBundleDirectory();
	}

	public function uninstall($parent)
	{
		$this->logMessage('Uninstall: No actions taken.');
	}

	public function preflight($type, $parent)
	{
		$this->logMessage("Preflight ({$type}): No actions taken.");
	}

	public function postflight($type, $parent)
	{
		$this->logMessage("Postflight ({$type}): No actions taken.");
	}

	private function cleanBundleDirectory()
	{
		$bundleDir = JPATH_ADMINISTRATOR . '/components/com_semantycanm/assets/bundle';
		if (is_dir($bundleDir))
		{
			$this->logMessage("Cleaning directory: {$bundleDir}");
			$this->deleteDirectoryContents($bundleDir);
		}
		else
		{
			$this->logMessage("Directory does not exist: {$bundleDir}");
		}
	}

	private function deleteDirectoryContents($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file)
		{
			$path = $dir . '/' . $file;
			if (is_dir($path))
			{
				$this->deleteDirectoryContents($path);
				rmdir($path);
				$this->logMessage("Removed directory: {$path}");
			}
			else
			{
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
