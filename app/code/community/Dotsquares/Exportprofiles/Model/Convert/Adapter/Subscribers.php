<?php
class Dotsquares_Exportprofiles_Model_Convert_Adapter_Subscribers extends Mage_Dataflow_Model_Convert_Adapter_Abstract
{
    const SET_NAME_KEY = 0;
    const GROUP_NAME_KEY = 1;

    /**
     * Current store model
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store = null;

    /**
     * @var int
     */
    protected $_entityTypeId = null;
    protected $_orderBy = 'subscriber_id';
    protected $_orderDir = 'ASC';
    protected $_limit = 0;
	protected $_subscriberModel;

    /**
     * @var int
     * @todo add multi-store functionality
     */
    protected $_admin = 0;

    /**
     * @return int
     */

    /**
     * @return int
     * @throws Exception  
     */
    public function getStoreId()
    {
        if (is_null($this->_store)) {
            try {
                $this->_store = Mage::app()->getStore($this->getVar('store'));
            }
            catch (Exception $e) {
                $message = Mage::helper('dataflow')->__('Invalid store specified');
                $this->addException($message, Varien_Convert_Exception::FATAL);
                throw $e;
            }
        }
		
        return $this->_store->getId();
    }
	
	/**
     * Retrieve website model by code
     *
     * @param int $websiteId
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteId($storeId=null)
    {
		try {
			$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
		}
		catch (Exception $e) {
			$message = Mage::helper('dataflow')->__('Invalid website specified');
			$this->addException($message, Varien_Convert_Exception::FATAL);
			throw $e;
		}
        return $websiteId;
		
		//$iDefaultStoreId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
    }
	
	/**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreById($id=NULL)
    {
		if (empty($id)) {
            //$this->_store = Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
            return Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
		} else {
			try {
                $this->_store = Mage::app()->getStore($id);
            }
            catch (Exception $e) {
				$message = Mage::helper('dataflow')->__('Invalid store specified, Invalid store id "%s".', $id);
                Mage::throwException($message);
            }
		}
		return $this->_store->getId();	
	
        /**
         * In single store mode all data should be saved as default
         */
    }

    /**
     * @return $this
     * @throws Exception
     * @throws Varien_Convert_Exception
     */
    public function load()
    {
		$websiteId = $this->getWebsiteId($this->getStoreId());
		$attrFilterArray = array();
		
		/*
         * Fixing date filter from and to
         */
        if ($var = $this->getVar('filter/created_at/from')) {
            $this->setVar('filter/created_at/from', $var . ' 00:00:00');
        }

        if ($var = $this->getVar('filter/created_at/to')) {
            $this->setVar('filter/created_at/to', $var . ' 23:59:59');
        }
		
		$filterQuery = $this->getFilter();
		$orderVars = $this->_parseVars('order',5);
		$_limit = $this->getVar('limit');
		
        $collection = Mage::getModel('newsletter/subscriber')->getCollection()->addFieldToSelect('subscriber_id');
				
				
		if (is_array($filterQuery)) {
			foreach ($filterQuery as $k => $val) {
				$collection->addFieldToFilter($k,$val);
			}
		}
		
		$this->_store = Mage::app()->getStore();
		if (!empty($store = $this->getVar('store'))) {
			$collection->addFieldToFilter('store_id',$store);
		}
		
		if(isset($orderVars['by']) && !empty($orderVars['by'])){
			$this->_orderBy = $orderVars['by'];
		}
		if(isset($orderVars['direction']) && !empty($orderVars['direction'])){
			$this->_orderDir = $orderVars['direction'];
		}
		$collection->getSelect()->order($this->_orderBy.' '.$this->_orderDir);	

		if($_limit) {
		$collection->getSelect()->limit($_limit);
		//$collection->setPageSize(2); 
		//$collection->clear()->setPageSize(2)->setCurPage(1);
		//$collection->setPageSize(2);
		}
		
		//echo $collection->getSelect(); die;	
		//$entityIds = $collection->getAllIds(2,2);
		$entityDatas = $collection->getData();
		$entityIds = $this->getOnlyIds($entityDatas);

		/*	 echo '<pre>';
		print_r($entityIds);
		die; */
		
        try {
           $message = Mage::helper('dataflow')->__("Loaded %d records", count($entityIds));
           $this->addException($message);
        }
        catch (Varien_Convert_Exception $e) {
            throw $e;
        }
        catch (Exception $e) {
            $message = Mage::helper('dataflow')->__('Problem loading the collection, aborting. Error: %s', $e->getMessage());
            $this->addException($message, Varien_Convert_Exception::FATAL);
        }

        $this->setData($entityIds);
        return $this;
    }
	
	public function getFilter()
    {
        $filters = array();
		$filterVars = $this->_parseVars('filter');
		//$now = Mage::getModel('core/date')->timestamp(time());
		//$dateStart = date('m/d/Y' . ' 00:00:00', $now);
		//$dateEnd = date('m/d/Y' . ' 23:59:59', $now);
		
		$dateStart = date('Y-m-d H:i:s', strtotime('-1 year'));
		$dateEnd = date('Y-m-d H:i:s', strtotime(now()));

	
		if (is_array($filterVars)) {
			foreach ($filterVars as $filterVarK => $filterVarV) {
				
				if($filterVarK == "created_at/from"){
					//$filters['change_status_at']['gteq'] = $filterVarV;
					$filters['change_status_at']['from'] = $filterVarV;
				}
				if($filterVarK == "created_at/to"){
					//$filters['change_status_at']['lteq'] = $filterVarV;
					$filters['change_status_at']['to'] = $filterVarV;
				}
				if($filterVarK == "customer_id/from"){
					$filters['customer_id']['from'] = $filterVarV;
				}
				if($filterVarK == "customer_id/to"){
					$filters['customer_id']['to'] = $filterVarV;
				}
				if($filterVarK == "subscriber_id/from"){
					$filters['subscriber_id']['from'] = $filterVarV;
				}
				if($filterVarK == "subscriber_id/to"){
					$filters['subscriber_id']['to'] = $filterVarV;
				}
				
				if($filterVarK == "subscriber_status"){
					$filters['subscriber_status']['eq'] = $filterVarV;
				}
				if($filterVarK == "subscriber_email"){
					$filters['subscriber_email']['eq'] = $filterVarV;
				}
			}
		} 
		return $filters;
    }
	
	protected function _parseVars($type = 'filter', $typeChars =6)
    {
        $varFilters = $this->getVars();
        $filters = array();
        foreach ($varFilters as $key => $val) {
            if (substr($key,0,$typeChars) === $type) {
                $keys = explode('/', $key, 2);
                $filters[$keys[1]] = $val;
            }
        }

		$filters = array_filter($filters); 

        return $filters;
    }
	
	/**
     * @return $this
     */
    public function save()
    {
        return $this;
    }
	
	
	public function parse()
    { 	
		$batchModel = Mage::getSingleton('dataflow/batch');

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

		foreach ($importIds as $importId) {
            //print '<pre>'.memory_get_usage().'</pre>';
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();
            $this->saveRow($importData);
        }
    }
	
	/**
     * Save newsletter subscriber (import)
     *
     * @param  array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
		//$subscriber = $this->getSubscriberModel()->reset();
		$subscriber = $this->getSubscriberModel();
		$dd = $this->getBatchParams();
		Mage::log($dd, null,'Dotsquares_Exportprofiles.log');
		
		$store = $this->getStoreById($this->getBatchParams('store'));	
		
		if (empty($importData['subscriber_email'])) {
            $message = Mage::helper('newsletter')->__('Skipping import row, required field "%s" is not defined.', 'subscriber_email');
            Mage::throwException($message);
			Mage::log(sprintf('Skip import row, required field "subscriber_email" not defined', $message), null,'Dotsquares_Exportprofiles.log');
        } else {
            
			$subscriberUser = $subscriber->loadByEmail($importData['subscriber_email']);
			if ($subscriberUser->getId()) {
				// put your logic here...
				if ($subscriberUser->getSubscriberStatus() == $importData['subscriber_status']) {
				$message = Mage::helper('newsletter')->__('Skipping import row, "%s" already subscribed.', $importData['subscriber_email']);
				Mage::throwException($message);
				Mage::log(sprintf('Skip import row, subscriber_email already subscribed', $message), null,'Dotsquares_Exportprofiles.log');
				}
			} else {
				/* Import */
				
				/* create new subscriber without send an confirmation email */
				$subscriber->setImportMode(true)->subscribe($importData['subscriber_email']);
				
				/* get just generated subscriber */
				$subscriber = $subscriber->loadByEmail($importData['subscriber_email']);

				/* get Newsletter Subscriber whose status is equal to value */
				
				$status = strtolower($importData['subscriber_status']);

				if ($status == "subscribed"){
					// Subscribed
					$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
				}else if($status == "not activated" || $status == "notactivated"){
					// Not Activated
					$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
				}else if($status == "unsubscribed"){
					// Unsubscribed
					$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
				}else if($status == "unconfirmed"){
					// Unconfirmed
					$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
				}
				
				$subscriber->setStoreId($store);
				$subscriber->save();
			}
		}

		return true;
		
	}
	
	/**
     * Retrieve product model cache
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getSubscriberModel()
    {
        if (is_null($this->_subscriberModel)) {
            $subscriberModel = Mage::getModel('newsletter/subscriber');
            $this->_subscriberModel = Mage::objects()->save($subscriberModel);
        }
        return Mage::objects()->load($this->_subscriberModel);
    }
	
	/* public function reset() {
		return new self();
	} */
	public function getOnlyIds($entityDatas){
		$ids = array();
		foreach ($entityDatas as $entityDatasItem) {
			$ids[] = $entityDatasItem['subscriber_id'];
		}
		return $ids;
	}
	
}