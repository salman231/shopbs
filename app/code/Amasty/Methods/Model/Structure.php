<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Model;

class Structure extends \Magento\Framework\DataObject
{
    const VAR_RESTRICT_METHOD = 'restrict_method';
    protected $_collection;
    protected $_objectCode;
    protected $_objects = [];

    protected $_objectCollectionFactory;
    protected $_objectFactory;
    protected $_delimiter = ',';

    protected $_resourceConfig;
    protected $_helper;

    /** @var \Magento\Framework\App\Cache\TypeListInterface $_cacheTypeList */
    protected $_cacheTypeList;
    /** @var \Magento\Framework\App\Cache\StateInterface $_cacheState */
    protected $_cacheState;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Amasty\Methods\Helper\Data $helper
    ) {
        $this->_resourceConfig = $resourceConfig;
        $this->_helper = $helper;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
    }

    public function getCollection()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_objectCollectionFactory->create()
                ->addFieldToFilter('website_id', $this->getData('website_id'));
        }

        return $this->_collection;
    }

    public function getSize()
    {
        return $this->getCollection()->getSize();
    }

    public function explodeGroupIds($groupIds)
    {
        return $groupIds !== '' ?
            explode($this->_delimiter, $groupIds) :
            [];
    }

    public function validate($groupId, $groupIds)
    {
        if ($groupId === null){
            $groupId = $this->_helper->getDefaultGroupId();
        }
        $valid = false;

        $restrictMode = (int)$this->getData(self::VAR_RESTRICT_METHOD) === 1;

        if ($restrictMode){
            $valid = !in_array($groupId, $this->explodeGroupIds($groupIds));
        } else {
            $valid = in_array($groupId, $this->explodeGroupIds($groupIds));
        }

        return $valid;
    }

    public function load($websiteId)
    {
        $this->setData('website_id', $websiteId);

        foreach($this->getCollection() as $method)
        {
            $key = $this->_objectCode . '_' . $method->getMethod();
            $this->setData($key, $this->explodeGroupIds($method->getGroupIds()));
            $this->_objects[$key] = $method;
        }


        $this->setData(
            self::VAR_RESTRICT_METHOD,
            $this->_helper->getScopeValue($this->_getRestrictMethodCode(), $websiteId)
        );

        return $this;
    }

    public function getId()
    {
        return $this->getData('website_id');
    }

    protected function _getObject($method)
    {
        $object = null;
        if (array_key_exists($this->_objectCode . '_' . $method, $this->_objects)){
            $object = $this->_objects[$this->_objectCode . '_' . $method];
        } else {
            $object = $this->_objectFactory->create()->setData([
                'website_id' => $this->getData('website_id'),
                'method' => $method
            ]);
        }

        return $object;
    }

    public function get($method)
    {
        return $this->_getObject($method);
    }

    public function save(array $data)
    {
        if (array_key_exists('website_id', $data)){
            $this->load($data['website_id']);

            $savedIds = [];

            if (array_key_exists($this->_objectCode, $data)){
                foreach($data[$this->_objectCode] as $method => $groups){
                    if (count($groups) > 0){
                        $savedIds[] = $this->_getObject($method)
                            ->setGroupIds(implode($this->_delimiter, $groups))
                            ->save()
                            ->getId();
                    }
                }
            }

            $deleteCollection = $this->_objectCollectionFactory->create()
                ->addFieldToFilter('website_id', $this->getData('website_id'));

            if (count($savedIds) > 0){
                $deleteCollection->addFieldToFilter('entity_id', [
                    'nin' => $savedIds
                ]);
            }

            foreach($deleteCollection as $object){
                $object->delete();
            }

            if (array_key_exists(\Amasty\Methods\Model\Structure::VAR_RESTRICT_METHOD, $data)){
                $oldValue = $this->_helper->getScopeValue($this->_getRestrictMethodCode(), $data['website_id']);
                $this->_resourceConfig->saveConfig(
                    'amasty_methods/' . $this->_getRestrictMethodCode(),
                    $data[\Amasty\Methods\Model\Structure::VAR_RESTRICT_METHOD],
                    'websites',
                    $data['website_id']
                );
                $cacheType = 'config';
                if ($this->_cacheState->isEnabled($cacheType)
                    && ($oldValue != $data[\Amasty\Methods\Model\Structure::VAR_RESTRICT_METHOD])
                ) {
                    $this->_cacheTypeList->cleanType($cacheType);
                }
            }
        }
    }

    protected function _getRestrictMethodCode()
    {
        return $this->_objectCode . '/' . self::VAR_RESTRICT_METHOD;
    }

    public function getObjectCode()
    {
        return $this->_objectCode;
    }
}
