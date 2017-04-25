<? namespace Intervolga\Migrato\Data\Module\Sale;

use Bitrix\Sale\Internals\OrderPropsGroupTable;
use Intervolga\Migrato\Data\BaseData;
use Intervolga\Migrato\Data\Link;
use Intervolga\Migrato\Data\Record;
use Intervolga\Migrato\Tool\XmlIdProvider\BaseXmlIdProvider;

class PropertyGroup extends BaseData
{
	public function getFilesSubdir()
	{
		return "/persontype/";
	}

	public function getDependencies()
	{
		return array(
			"PERSON_TYPE_ID" => new Link(PersonType::getInstance()),
		);
	}

	public function getList(array $filter = array())
	{
		$result = array();
		$getList = OrderPropsGroupTable::getList();
		while ($propGroup = $getList->fetch())
		{
			$record = new Record($this);
			$id = $this->createId($propGroup["ID"]);
			$record->setId($id);
			$record->setXmlId(
				$this->getXmlId($id)
			);
			$record->addFieldsRaw(array(
				"NAME" => $propGroup["NAME"],
				"SORT" => $propGroup["SORT"],
			));

			$link = clone $this->getDependency("PERSON_TYPE_ID");
			$personTypeXmlId = PersonType::getInstance()->getXmlId(
				PersonType::getInstance()->createId($propGroup["PERSON_TYPE_ID"])
			);
			$link->setValue($personTypeXmlId);
			$record->setDependency("PERSON_TYPE_ID", $link);

			$result[] = $record;
		}

		return $result;
	}

	public function getXmlId($id)
	{
		$record = OrderPropsGroupTable::getById($id->getValue())->fetch();
		$personTypeXmlId = PersonType::getInstance()->getXmlId(
			PersonType::getInstance()->createId($record["PERSON_TYPE_ID"])
		);
		$md5 = md5(serialize(array(
			$record['NAME'],
			$personTypeXmlId,
		)));

		return BaseXmlIdProvider::formatXmlId($md5);
	}

	public function update(Record $record)
	{
		$update = $this->recordToArray($record);
		$object = new \CSaleOrderPropsGroup();
		$updateResult = $object->update($record->getId()->getValue(), $update);
		if (!$updateResult)
		{
			global $APPLICATION;
			throw new \Exception($APPLICATION->getException()->getString());
		}
	}

	/**
	 * @param \Intervolga\Migrato\Data\Record $record
	 *
	 * @return array
	 */
	protected function recordToArray(Record $record)
	{
		$array = $record->getFieldsRaw();
		if ($depenency = $record->getDependency("PERSON_TYPE_ID"))
		{
			$personTypeXmlId = $depenency->getValue();
			$idObject = PersonType::getInstance()->findRecord($personTypeXmlId);
			if ($idObject)
			{
				$array["PERSON_TYPE_ID"] = $idObject->getValue();
			}
		}

		return $array;
	}

	public function create(Record $record)
	{
		$add = $this->recordToArray($record);
		$object = new \CSaleOrderPropsGroup();
		$id = $object->add($add);
		if ($id)
		{
			return $this->createId($id);
		}
		else
		{
			global $APPLICATION;
			throw new \Exception($APPLICATION->getException()->getString());
		}
	}

	public function delete($xmlId)
	{
		$id = $this->findRecord($xmlId);
		if ($id)
		{
			$object = new \CSaleOrderPropsGroup();
			$result = $object->delete($id->getValue());
			if (!$result)
			{
				global $APPLICATION;
				throw new \Exception($APPLICATION->getException()->getString());
			}
		}
	}
}