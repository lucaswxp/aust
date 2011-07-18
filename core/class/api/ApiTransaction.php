<?php
/**
 * API
 *
 * Handles the requests, find out what structure was asked for and retrieves.
 *
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.3, 17/06/2011
 */
class ApiTransaction {
	
	var $dataFormat = 'json';
	var $queryParser;

    function __construct() {
		$this->queryParser = new ApiQueryParser();
    }
	
	public function perform($get){
		$result = array();
		
		if( array_key_exists('version', $get) ){
			$result = $this->version();
		} else {
			$result = $this->getData($get);
		}
		
		if( empty($result) )
			$result = '';
		
		$finalResult = array('result' => $result);
		
		if( $this->dataFormat == 'json' )
			$finalResult = $this->json($finalResult);
			
		return $finalResult;
	}
	
	public function version(){
		return '0.0.1';
	}
	
	public function json($value){
		return json_encode($value);
	}
	
	public function queryParser(){
		return $this->queryParser;
	}
	
	public function getData($get){
		
		$structureIds = $this->queryParser()->structureId($get);
		if( !is_array($structureIds) || count($structureIds) == 0 )
			return false;

		$where = $this->queryParser()->where($get);
		$order = $this->queryParser()->order($get);
		$limit = $this->queryParser()->limit($get);
		$fields = $this->queryParser()->fields($get);
		$result = array();
		
		$queryParameters = array(
			'fields' => $fields,
			'where' => $where,
			'order' => $order,
			'limit' => $limit
		);
		
		foreach( $structureIds as $structureId ){
			
			$structureInstance = ModulesManager::getInstance()->modelInstance($structureId);
			$items = $structureInstance->load(
				$queryParameters
			);
			$result = array_merge($result, $items);
		}
		if( empty($result) )
			$result = 0;
		return $result;
		
	}
	
}
?>