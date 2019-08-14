<?php
class Dotsquares_Exportprofiles_Model_Observer
{
  /**
  * Observer function to update the subscription action date
  * @param Varien_Event_Observer $observer
  */
  public function setUpdateDate(Varien_Event_Observer $observer)
  {
    $subscriber = $observer->getSubscriber();
    $subscriber['change_status_at'] = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
    //$subscriber['change_status_at'] = date("Y-m-d H:i:s", time());
	//date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
	
  }
}
