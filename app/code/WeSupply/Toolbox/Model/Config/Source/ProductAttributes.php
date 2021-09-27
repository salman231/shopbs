<?php

namespace WeSupply\Toolbox\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class ProductAttributes implements ArrayInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     * attributes types allowed to set WeSupply delivery estimation logic
     */
    protected $frontendTypes = [
        'text',
        'textarea',
        'select',
        'multiselect',
        'price',
        'boolean'
    ];
    
    /**
     * ProductAttributes constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_visible', 1)
            ->create();
        
        $attributeRepository = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );
    
        $options = [];
        $attributes = $attributeRepository->getItems();
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getFrontendInput(), $this->frontendTypes)) {
                continue;
            }
            $options[strtolower(str_replace(' ', '_', $attribute->getFrontendLabel()))] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel() . ' (' . $attribute->getAttributeCode() . ')'
            ];
        }
    
        ksort($options);
    
        return $options;
    }
}