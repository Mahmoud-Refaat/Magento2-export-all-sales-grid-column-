<?php 
		namespace Alham\SalesGrid\Plugins;
		use Magento\Framework\Message\ManagerInterface as MessageManager;
		use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
		class AddColumnsSalesOrderGridCollection
		{
		private $messageManager;
		private $collection;
		public function __construct(MessageManager $messageManager,
			SalesOrderGridCollection $collection
			) {
			$this->messageManager = $messageManager;
			$this->collection = $collection;
		}
			public function aroundGetReport(
				\Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
				\Closure $proceed,
				$requestName
			) {
        
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof $this->collection
            ) {
                
                $select = $this->collection->getSelect();
                $select-> join(
                    ["soa" => "sales_order_address"],
                    'main_table.entity_id = soa.parent_id AND soa.address_type="billing"',
                    array('telephone' ,'region')
                )->joinLeft(
                    ["sosh" => "sales_order_status_history"],   
                    'main_table.entity_id = sosh.parent_id',
                     ['comment' => 'GROUP_CONCAT(DISTINCT sosh.comment)'
                ]
                )->join(
                    ["soi" => "sales_order_item"],   
                    'main_table.entity_id = soi.order_id' ,
                     ['sku' => 'GROUP_CONCAT(DISTINCT soi.sku)'
                ]
                )->group("main_table.entity_id");
                
            }
            return $this->collection->addFilterToMap('created_at', 'main_table.created_at')->addFilterToMap('status', 'main_table.status');
        }else{
            
            return $result;
        }
        
    }
}
