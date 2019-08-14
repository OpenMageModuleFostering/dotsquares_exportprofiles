<?php
class Dotsquares_Exportprofiles_Model_Convert_Parser_Subscribers extends Mage_Dataflow_Model_Convert_Parser_Abstract
{

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var array
     */
    protected $_columns = array(
        'subscriber_id',
        'customer_id',
		'firstname',
        'lastname',
        'subscriber_email',
        'subscriber_status',
        'has_account',
        'subscription_date'
    );
	
	protected $_subscriberStatus = array(
        '1' => 'Subscribed',
        '2' => 'Not Activated',
        '3' => 'Unsubscribed',
        '4' => 'Unconfirmed'
    );
	

    /**
     * @param array $row
     */
    protected function _addDataRow($row)
    {
        $this->getBatchExportModel()->setId(null)->setBatchId($this->getBatchModel()->getId())->setBatchData($row)->setStatus(1)->save();
    }

    /**
     *
     */
    protected function _prepareColumnNames()
    {
        $names = array();
        foreach ($this->_columns as $name) {
            $names[$name] = $name;
        }
        $this->_addDataRow($names);
    }

    /**
     * @return Mage_Core_Model_Store
     * @throws Exception
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            try {
                $store = Mage::app()->getStore($this->getVar('store'));
            }
            catch (Exception $e) {
                $this->addException(Mage::helper('dataflow')->__('Invalid store specified'), Varien_Convert_Exception::FATAL);
                throw $e;
            }
            $this->_store = $store;
        }
        return $this->_store;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->_storeId = $this->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * @return $this
     */
    public function parse()
    {
        return $this;
    }

    /**
     * Unparse (prepare data) loaded products
     *
     * @return Mage_Catalog_Model_Convert_Parser_Product
     */
    public function unparse()
    {
        $entityIds = $this->getData();
        $columnNames = true;
		$export_customer_name = $this->getVar('export_customer_name');
		$export_customer_name = strtolower($export_customer_name);

        foreach ($entityIds as $i => $entityId) {
            $model = Mage::getModel('newsletter/subscriber');

            $model->load($entityId);
            $data = $model->getData();
			//$row = $data;
			$row = array();

			//print_r($customerExist->getData());

            /* if ($columnNames) {
                $columnNames = false;
                $this->_prepareColumnNames();
            } */
		
            foreach ($data as $key => $val) {
                if (in_array($key, $this->_columns)) {
                    $row[$key] = $val;
                }
            }
			if($export_customer_name == 'true' && !empty($data['subscriber_email'])) {
				
				$customerExist = Mage::getModel('customer/customer')
						->getCollection()
						->addAttributeToSelect('firstname')
						->addAttributeToSelect('lastname')
						->addAttributeToFilter('email', $data['subscriber_email'] )
						->getFirstItem();
				if($customerExist) {
					$row['firstname'] = $customerExist->getFirstname();
					$row['lastname'] = $customerExist->getLastname();
				}
				
			}
			$row['has_account'] = ($data['customer_id'])?'Yes':'No';
			$row['subscriber_status'] = $this->_subscriberStatus[$data['subscriber_status']];
			if(!empty($data['change_status_at'])) {
				$row['subscription_date'] = date("d/m/Y", strtotime($data['change_status_at']));
			} else {
				$row['subscription_date'] = '';
			}
			

            $this->_addDataRow($row);
        }
        return $this;
    }
}