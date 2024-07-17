<?php
namespace Concrete\Core\Attribute;

use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Attribute\Value\EmptyRequestAttributeValue;
use Concrete\Core\Cache\Level\RequestCache;
use Concrete\Core\Entity\Attribute\Value\Value as AttributeValue;
use Concrete\Core\Entity\Attribute\Value\Value\Value;
use Concrete\Core\Support\Facade\Application;

trait ObjectTrait
{
    /**
     * @return CategoryInterface
     */
    abstract public function getObjectAttributeCategory();

    /**
     * @param $ak
     * @param bool $createIfNotExists
     *
     * @return AttributeValue
     */
    abstract public function getAttributeValueObject($ak, $createIfNotExists = false);

    public function getAttribute($ak, $mode = false)
    {
        $value = $this->getAttributeValueObject($ak);
        if (is_object($value)) {
            return $value->getValue($mode);
        }
    }

    /**
     * @param $ak
     * @return \Concrete\Core\Entity\Attribute\Value\Value
     */
    public function getAttributeValue($ak)
    {
        $value = $this->getAttributeValueObject($ak);
        if (is_object($value)) {
            return $value;
        }
    }

    /**
     * @param AttributeKeyInterface | string $ak
     * @param bool $doReindexImmediately
     */
    public function clearAttribute($ak, bool $doReindexImmediately = true)
    {
        $value = $this->getAttributeValueObject($ak);
        if (is_object($value)) {
            $controller = $this->getObjectAttributeCategory();
            $controller->deleteValue($value);
            if ($doReindexImmediately) {
                $category = $this->getObjectAttributeCategory();
                $indexer = $category->getSearchIndexer();
                if ($indexer) {
                    $indexer->clearIndexEntry($category, $value, $this);
                }
            }
        }
    }

    /**
     * Sets the attribute of a user info object to the specified value, and saves it in the database.
     *
     * @param AttributeKeyInterface | string $ak
     * @param mixed $value
     * @param bool $doReindexImmediately
     * @return \Concrete\Core\Entity\Attribute\Value\Value
     */
    public function setAttribute($ak, $value, $doReindexImmediately = true)
    {
        $app = Application::getFacadeApplication();
        /** @var RequestCache $cache */
        $cache = $app->make('cache/request');
        if (is_object($ak)) {
            $akHandle = $ak->getAttributeKeyHandle();
        } else {
            $akHandle = $ak;
        }
        $cache->delete('attribute/value/' . $akHandle);

        $orm = \Database::connection()->getEntityManager();

        $this->clearAttribute($ak, $doReindexImmediately);

        // Create the attribute category value.
        $attributeValue = $this->getAttributeValueObject($ak, true);
        $orm->persist($attributeValue);
        $orm->flush();

        // Create the generic value. This gets joined to any specific Attribute value objects later on.
        $genericValue = new Value();
        $genericValue->setAttributeKey($attributeValue->getAttributeKey());
        $orm->persist($genericValue);
        $orm->flush();

        // Set the generic value to the attribute category value.
        $attributeValue->setGenericValue($genericValue);
        $orm->persist($attributeValue);
        $orm->flush();

        $controller = $attributeValue->getAttributeKey()->getController();
        $controller->setAttributeValue($attributeValue);

        if (!($value instanceof AttributeValue\AbstractValue)) {
            if ($value instanceof EmptyRequestAttributeValue) {
                // LEGACY SUPPORT
                // If the passed $value object == EmptyRequestAttributeValue, we know we are dealing
                // with a legacy attribute type that's not using Doctrine. We have not returned an
                // attribute value value object.
                $controller->saveForm($controller->post());
                $value = false;
            } else {
                /**
                 * @var $value AttributeValue\AbstractValue
                 */
                $value = $controller->createAttributeValue($value);
            }
        }

        if ($value) {
            $value->setGenericValue($genericValue);
            $orm->persist($value);
            $orm->flush();
        }

        if ($doReindexImmediately) {
            $category = $this->getObjectAttributeCategory();
            $indexer = $category->getSearchIndexer();
            if ($indexer) {
                $indexer->indexEntry($category, $attributeValue, $this);
            }
        }

        return $attributeValue;
    }

}
