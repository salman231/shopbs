<?php
namespace Webkul\MobikulApi\Controller\RMA;

class GetCustomFields extends AbstractRma
{
    public function execute()
    {
        $this->verifyRequest();
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";
            //$reasons=$this->getReasonCollection();
            $customFieldsCollection = $this->RmaHelper->getFields();
            $size=$customFieldsCollection->getSize();
            if($size > 0){
                $customfieldsdata=[];
                foreach($customFieldsCollection as $customField){
                    switch ($customField->getInputType()) {
                        case 'text':
                            $resultdata = $this->getText($customField);
                            break;
                        case 'textarea':
                            $resultdata = $this->getTextarea($customField);
                            break;
                        case 'select':
                            $resultdata = $this->getSelect($customField);
                            break;
                        case 'multiselect':
                            $resultdata = $this->getMultiselect($customField);
                            break;
                        case 'radio':
                            $resultdata = $this->getRadio($customField);
                            break;
                        case 'checkbox':
                            $resultdata = $this->getCheckbox($customField);
                            break;
                    }
                    $customfieldsdata[]=$resultdata;
                }
                $this->returnArray["data"]["customfields"]=$customfieldsdata;
            }
            else{
                $this->returnArray["message"]= "No custom fields found.";
            }
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function getText($data)
    {
        $required =0;
        if ($data->getRequired()==1) {
            $required = 1;
        } else {
            $required = 0;
        }
        $result=[
            "type" => "text",
            "id" => $data->getInputname(),
            "label" => $data->getLabel(),
            "required" => $required
        ];
        // $field = "<div class='field ".$required."'><label class='label'><span>".
        //         $this->escapeHtml($data->getLabel()).":</span></label>";
        // $field = $field."<div class='control'><input type ='text' value='' class='".$required."' name='".
        //         $this->escapeHtml($data->getInputname())."'></div></div>";
        return $result;
    }

    public function getTextarea($data)
    {
        $required =0;
        if ($data->getRequired()==1) {
            $required = 1;
        } else {
            $required = 0;
        }
        $result=[
            "type" => "textarea",
            "id" => $data->getInputname(),
            "label" => $data->getLabel(),
            "required" => $required
        ];
        // $field = "<div class='field ".$required."'><label class='label'><span>".
        //         $this->escapeHtml($data->getLabel()).":</span></label>";
        // $field = $field."<div class='control'><textarea name='".
        //         $this->escapeHtml($data->getInputname())."' class='".$required."'></textarea></div></div>";
        return $result;
    }

    public function getSelect($data)
    {
        $required =0;
        if ($data->getRequired()==1) {
            $required = 1;
        } else {
            $required = 0;
        }
        $options = explode(",", $data->getSelectOption());
        
        //$value ='<option value="">'.__('Select').'</option>';
        $optionsdata=[];
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $optional=[
                    "label" => $tmp[1],
                    "value" => $tmp[0]
                ];
            }
            $optionsdata[]=$optional;
        }
        $result=[
            "type" => "select",
            "id" => $data->getInputname(),
            "label" => $data->getLabel(),
            "options" => $optionsdata 
        ];
        //$result["options"]=$optionsdata;
        
        return $result;
    }

    public function getMultiselect($data)
    {
        $required =0;
        if ($data->getRequired()==1) {
            $required = 1;
        } else {
            $required = 0;
        }
        $options = explode(",", $data->getSelectOption());
        
        //$value ='<option value="">'.__('Select').'</option>';
        $optionsdata=[];
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $optional=[
                    "label" => $tmp[1],
                    "value" => $tmp[0]
                ];
            }
            $optionsdata[]=$optional;
        }
        $result=[
            "type" => "multiselect",
            "id" => $data->getInputname(),
            "label" => $data->getLabel(),
            "options" => $optionsdata 
        ];
        //$result["options"]=$optionsdata;
        
        return $result;
    }

    public function getRadio($data)
    {
        $required =0;
        if ($data->getRequired()==1) {
            $required = 1;
        } else {
            $required = 0;
        }
        $options = explode(",", $data->getSelectOption());
        //$value ='<p></p>';
        $optionsdata=[];
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $optional=[
                    "label" => $tmp[1],
                    "value" => $tmp[0]
                ];
            }
            $optionsdata[]=$optional;
        }
        $result=[
            "type" => "radio",
            "id" => $data->getInputname(),
            "label" => $data->getLabel(),
            "options" => $optionsdata 
        ];
        //$result["options"]=$optionsdata;
        
        return $result;
        // foreach ($options as $key) {
        //     $tmp = explode("=>", $key);
        //     if (count($tmp)==2) {
        //         $value = $value."<input type='radio' class='".$required."' name='".
        //             $this->escapeHtml($data->getInputname())."' value=".$this->escapeHtml($tmp[0])."><span>".
        //             $this->escapeHtml($tmp[1])."</span></br>";
        //     }
        // }
        // $field = "<div class='field ".$required."'><label class='label'><span>".
        //         $this->escapeHtml($data->getLabel())."</span></label>";
        // $field = $field.$value."</div>";
        // return $field;
    }

    public function getCheckbox($data)
    {
        $required =0;
        if ($data->getRequired()==1) {
            $required = 1;
        } else {
            $required = 0;
        }
        $options = explode(",", $data->getSelectOption());
        //$value ='<p></p>';
        $optionsdata=[];
        foreach ($options as $key) {
            $tmp = explode("=>", $key);
            if (count($tmp)==2) {
                $optional=[
                    "label" => $tmp[1],
                    "value" => $tmp[0]
                ];
            }
            $optionsdata[]=$optional;
        }
        $result=[
            "type" => "checkbox",
            "id" => $data->getInputname(),
            "label" => $data->getLabel(),
            "options" => $optionsdata 
        ];
        //$result["options"]=$optionsdata;
        
        return $result;
    }
    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

