<?php
 /**
  * Webkul Software
  *
  * @category Webkul
  * @package Webkul_MagentoChatSystem
  * @author Webkul
  * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
  * @license https://store.webkul.com/license.html
  */

namespace Webkul\MagentoChatSystem\Ui\Component\MassAction\Chat;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\CollectionFactory;
 
/**
 * Class AssignOptions
 */
class AssignOptions implements JsonSerializable
{
    /**
     * @var array
     */
    protected $options;
 
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
 
    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;
 
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
 
    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;
 
    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;
 
    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];
 
    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }
 
    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $counter=0;
        if ($this->options === null) {
            // get the massaction data from the database table
            $agents = $this->collectionFactory->create();
             
            if (!$agents->getSize()) {
                return $this->options;
            }
            //make a array of massaction
            foreach ($agents as $agent) {
                $options[$counter]['value']=$agent->getEntityId();
                $options[$counter]['label']=$agent->getAgentName();
                $counter++;
            }
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'agent_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];
 
                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }
 
                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }
             
            // return the massaction data
            $this->options = array_values($this->options);
        }
        return $this->options;
    }
 
    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
          
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
