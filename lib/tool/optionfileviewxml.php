<?namespace Intervolga\Migrato\Tool;

use Bitrix\Main\IO\File;

class OptionFileViewXml
{
	/**
	 * @param array $options
	 * @param string $directory
	 * @param string $module
	 */
	public static function write(array $options, $directory, $module)
	{
		$content = '';
		$content .= XmlHelper::xmlHeader();
		$content .= '<options>\n';
		foreach ($options as $option)
		{
			$map = array(
				'name' => $option['NAME'],
				'value' => $option['VALUE'],
				'site' => $option['SITE_ID'],
			);
			$content .= XmlHelper::tagMap('option', $map, 1);
		}
		$content .= '</options>';

		File::putFileContents($directory . $module . '.xml', $content);
	}

	/**
	 * @param string $path
	 * @return array
	 */
	public static function readFromFileSystem($path)
	{
		$options = array();
		$xmlParser = new \CDataXML();
		$xmlParser->Load($path);
		$xmlArray = $xmlParser->getArray();
		foreach ($xmlArray['options']['#']['option'] as $optionArray)
		{
			$options[] = array(
				'NAME' => $optionArray['#']['name'][0]['#'],
				'VALUE' => $optionArray['#']['value'][0]['#'],
				'SITE_ID' => $optionArray['#']['site'][0]['#'],
			);
		}

		return $options;
	}
}