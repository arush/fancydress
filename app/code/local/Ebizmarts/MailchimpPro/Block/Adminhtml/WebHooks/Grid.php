  <?php

  class Ebizmarts_MailchimpPro_Block_Adminhtml_WebHooks_Grid extends Mage_Adminhtml_Block_Widget_Grid
  {
      public function __construct(){

          parent::__construct();
          $this->setId('webHooksGrid');
          $this->setDefaultSort('name');
          $this->setDefaultDir('ASC');
          $this->setSaveParametersInSession(true);
      }

	protected function _prepareCollection(){

		$collection = Mage::getModel('mailchimpPro/mysql4_webHooks_collection');

		$currentStore = Mage::app()->getStore()->getStoreId();

		$avlists = explode(',',Mage::helper('mailchimpPro')->getGeneralConfig('listid',$currentStore));
		$newList = array();

		if(is_array($avlists) && count($avlists)){
			foreach($avlists as $item){
				$newList[] = substr($item,0,strpos($item,'['));
			}
			$newList[] = Mage::helper('mailchimpPro')->getGeneralConfig('general',$currentStore);
		}

        $lists = Mage::getModel('mailchimpPro/source_listIds')->toOptionArray(true);
        if(is_array($lists) && count($lists)){
        	foreach($lists as $list){
	        	$id = substr($list['value'],0,strpos($list['value'],'['));
	          	if(in_array($id,$newList)){
		        	$return = Mage::getModel('mailchimpPro/webHooks')->mainAction($id);
					$item = new Varien_Object;
		          	$item->setListId($id)
		          		 ->setName($list['label'])
		          		 ->setSubscribe((bool)$return['actions']['subscribe'])
		          		 ->setUnsubscribe((bool)$return['actions']['unsubscribe'])
		          		 ->setProfile((bool)$return['actions']['profile'])
		          		 ->setCleaned((bool)$return['actions']['cleaned'])
		          		 ->setUpemail((bool)$return['actions']['upemail'])
		          		 ->setUser((bool)$return['sources']['user'])
		          		 ->setAdmin((bool)$return['sources']['admin'])
		          		 ->setApi((bool)$return['sources']['api']);
		          	$collection->addItem($item);
	          	}
			}
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}

       protected function _prepareColumns() {

           $this->addColumn('list_id', array(
               'header'    => Mage::helper('mailchimpPro')->__('List Id'),
               'align'     =>'left',
               'width'     => '100px',
               'index'     => 'list_id'
           ));

           $this->addColumn('name', array(
               'header'    => Mage::helper('mailchimpPro')->__('List Name'),
               'align'     =>'left',
               'width'     => '300px',
               'index'     => 'name'
           ));

           $this->addColumn('subscribe', array(
               'header'    => Mage::helper('mailchimpPro')->__('Actions: Subscribe'),
               'field_name'=> 'subscribe',
               'align'     => 'center',
               'index'     => 'subscribe',
			   'type'      => 'checkbox',
               'values'   => array(
                  1 => 'Yes',
                  0 => 'No'
               )
           ));

           $this->addColumn('unsubscribe', array(
               'header'    => Mage::helper('mailchimpPro')->__('Actions: Unsubscribe'),
               'align'     => 'center',
               'field_name'=> 'unsubscribe',
               'index'     => 'unsubscribe',
			   'type'      => 'checkbox',
               'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
           ));

          $this->addColumn('profile', array(
              'header'    => Mage::helper('mailchimpPro')->__('Actions: Update Profile'),
              'field_name'=> 'profile',
              'align'     => 'center',
              'index'     => 'profile',
			  'type'      => 'checkbox',
              'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
          ));

		$this->addColumn('cleaned', array(
              'header'    => Mage::helper('mailchimpPro')->__('Actions: Cleaned'),
              'field_name'=> 'cleaned',
              'align'     => 'center',
              'index'     => 'cleaned',
			  'type'      => 'checkbox',
              'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
          ));

          $this->addColumn('upemail', array(
              'header'    => Mage::helper('mailchimpPro')->__('Actions: Update Email'),
              'field_name'=> 'upemail',
              'align'     => 'center',
              'index'     => 'upemail',
			  'type'      => 'checkbox',
              'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
          ));

          $this->addColumn('user', array(
              'header'    => Mage::helper('mailchimpPro')->__('Source: User'),
              'field_name'=> 'user',
              'align'     => 'center',
              'index'     => 'user',
			  'type'      => 'checkbox',
              'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
          ));

        $this->addColumn('admin', array(
              'header'    => Mage::helper('mailchimpPro')->__('Source: Admin'),
              'field_name'=> 'admin',
              'align'     => 'center',
              'index'     => 'admin',
			  'type'      => 'checkbox',
              'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
          ));

        	$this->addColumn('api', array(
              'header'    => Mage::helper('mailchimpPro')->__('Source: API'),
              'field_name'=> 'api',
              'align'     => 'center',
              'index'     => 'api',
			  'type'      => 'checkbox',
              'values'   => array(
                  1 => 'Checked',
                  0 => 'Disabled'
               )
          ));
          return parent::_prepareColumns();
      }
  }