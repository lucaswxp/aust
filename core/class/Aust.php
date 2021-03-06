<?php
/**
 * AUST
 *
 * Controls the structures and its taxonomies.
 *
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.2
 * @since v0.1.5, 30/05/2009
 */

class Aust {

    static $austTable = 'taxonomy';
    protected $AustCategorias = Array();
    public $connection;
    protected $conexao;

    public $_recursiveLimit = 50;
    public $_recursiveCurrent = 1;
	public $_structureModuleCache = array();
	public $_structureCache = array();

    function __construct(){
        $this->conexao = Connection::getInstance();
        $this->connection = Connection::getInstance();
        unset($this->AustCategorias);
    }

    static function getInstance(){
        static $instance;

        if( !$instance ){
            $instance[0] = new Aust;
        }

        return $instance[0];
    }

	function getStructureIdByName($string){
		$string = strtolower($string);
		$sql = "SELECT id
				FROM taxonomy
				WHERE
					lower(name) LIKE '$string' AND
					class = 'estrutura'
				";
		$query = Connection::getInstance()->query($sql);
		if( empty($query) )
			return false;
		
		$result = array();
		if( count($query) == 1){
			$result = reset($query);
			$result = array($result["id"]);
		} else {
			foreach( $query as $record ){
				$result[] = $record['id'];
			}
		}
		return $result;
	}

	/**
	 * getStructureInstance()
	 *
	 * Return the instance of a structure's model.
	 *
	 * @param $austNode (int)
	 */
	function getStructureInstance($austNode){
	    $modDir = $this->structureModule($austNode).'/';

        include(MODULES_DIR.$modDir.MOD_CONFIG);
        $module = (empty($modInfo['className'])) ? 'Classe' : $modInfo['className'];
        include_once(MODULES_DIR.$modDir.$module.'.php');

        $param = array(
            'config' => $modInfo,
            'user' => User::getInstance(),
        );
		
        $object = new $module($param);
		$object->setAustNode($austNode);
		
		return $object;
	}

    /**
     * gravaEstrutura()
     *
     * Creates a new structure.
     *
     * @param array $param Contém os seguintes índices:
     *      string  [nome]              structure's name;
     *      int     [categoriaChefe]    structure's father id
     *      bool    [publico]           1, everyone has access; 0, only root has access
     *      string  [modulo]            modules' name
     *      string  [autor]             struture's author id
     */
    function gravaEstrutura($params) {

        $nome = $params['nome'];
        $nome_encoded = (empty($params['nome_encoded'])) ? encodeText($nome) : $params['nome_encoded'] ;
        $categoria_chefe = $params['categoriaChefe'];
        $estrutura = (empty($params['estrutura'])) ? 'estrutura' : $params['estrutura'] ;
        $publico = (empty($params['publico'])) ? '1' : $params['publico'] ;
        $modulo = $params['moduloPasta'];
        $autor = $params['autor'];


        if(is_file(MODULES_DIR.$modulo.'/config.php')) {
            include(MODULES_DIR.$modulo.'/config.php');
            $tipo_legivel = $modInfo['name'];
        } else {
            $tipo_legivel = NULL;
        }

        $sql = "INSERT INTO
	            taxonomy
	            (
		            name,father_id,class,type,public,admin_id,
		            name_encoded
	            )
				VALUES
	            (
		            '$nome','$categoria_chefe','estrutura','$modulo',$publico,'$autor',
		            '$nome_encoded'
	            )
                ";
        /**
         * Retorna o id do registro feito
         */
        if (Connection::getInstance()->exec($sql)) {
            return Connection::getInstance()->conn->lastInsertId();
        } else {
            return FALSE;
        }
    }
	
	/**
	 * create()
	 * 
	 * Creates a new structure's category.
	 *
	 * @return int id criado
	 */
    public function create($params) {

        $catName = (empty($params['name'])) ? false : addslashes( str_replace("\n", "", $params['name']) );
        $father = (empty($params['father'])) ? false : $params['father'];
        $descricao = (empty($params['description'])) ? false : addslashes( $params['description'] );
        $autor = (empty($params['author'])) ? false : $params['author'];
        $permissao = (empty($params['permission'])) ? false : $params['permission'];
        $classe = (empty($params['class'])) ? 'categoria' : $params['class'];

        $tipo = (empty($params['type'])) ? '' : $params['type'];
        $tipoLegivel = (empty($params['type_name'])) ? '' : $params['type_name'];

        if( !$catName ) return false;
        if( !$father ) return false;

        /**
         * Loops to find out who's the Patriarch of this new category
         */
        $i = 0;
        $father_idtmp = $father;

        while( $i < 1 ) {
            $sql = "SELECT
                        id, name, father_id, class, name_encoded, type
                    FROM
                        taxonomy
                    WHERE
                        id='$father'
                    ";
            $query = Connection::getInstance()->query($sql);

			if( empty($query[0]) )
				return false;
				
            $dados = $query[0];

            if( empty($tipo) ) {
                $tipo = $dados['type'];
            }

            /*
             * Patriarch
             */
            if( !empty($dados['name']) )
                $patriarca = $dados['name'];
            else
                $patriarca = $this->getPatriarch($dados['id']);

            $catNameEncoded = encodeText( $catName );
            $patriarcaEncoded = encodeText( $patriarca );
            $subordinadoNomeEncoded = encodeText( $dados['name'] );

            if($dados['class'] == "estrutura") {
                $tipo = $this->structureModule($dados['id']);
                $i++;
            } else {
                $father_idtmp = $dados['father_id'];
                $i++;
            }

        }

        $sql = "INSERT INTO
                    taxonomy
                    (
                        name, name_encoded,
                        description,
                        structure_name, structure_name_encoded,
                        father_id, father_name_encoded,
                        class, type,
                        admin_id
                    )
                VALUES
                    (
                        '{$catName}','{$catNameEncoded}',
                        '$descricao',
                        '$patriarca', '$patriarcaEncoded',
                        '$father', '$subordinadoNomeEncoded',
                        '$classe','$tipo',
                        '".$autor."'
                    )";

        if( Connection::getInstance()->exec($sql) ) {
            return (int) Connection::getInstance()->lastInsertId();
        }

        return false;
    }

    public function getPatriarch($id) {

        if( $this->_recursiveCurrent > $this->_recursiveLimit )
            return false;

        $sql = "SELECT
                    id, name, structure_name, father_id, class
                FROM
                    taxonomy
                WHERE
                    id='$id'";
		$query = Connection::getInstance()->query($sql);
        $query = reset( $query );

        /**
         * Category without patriarch, go for its father's patriarch
         */
        if( empty($query['father_id'])
                AND $query['class'] == 'categoria' ) {
            $this->_recursiveCurrent++;
            return $this->getPatriarch($query['father_id']);
        }
        /*
         * No patriarch, but it's an structure, so it is itself
         */
        elseif( empty($query['father_id'])
                AND $query['class'] == 'estrutura' ) {
            return $query['name'];
        }
        /*
         * A patriarch is already defined
         */
        else {
            $this->_recursiveCurrent = 1;
            return $query['father_id'];
        }
    }

	/**
	 * deleteNodeImages( $node_id )
	 * 
	 * Deletes images from a aust node (structure or category).
	 * 
	 * @param int $node_id node_id da categoria
	 * @return bool
	 */
	function deleteNodeImages( $node_id ){
		
		$sql = "SELECT
					id, systempath
				FROM
					austnode_images
				WHERE
					node_id='".$node_id."'
				";
		
		$query = Connection::getInstance()->query($sql);
		foreach( $query as $key=>$value ){
			if( file_exists($value['systempath']) )
				unlink( $value['systempath'] );
			$sqlDelete = "DELETE FROM austnode_images WHERE id='".$value['id']."'";
			Connection::getInstance()->exec($sqlDelete);
		}
		
		return true;
	}

    public function createSite($name, $description = '') {
        $sql = "INSERT INTO
                    taxonomy
                        (name, description, class, type, father_id)
                VALUES
                    ('$name','$description','categoria-chefe','','0')";
        return Connection::getInstance()->exec($sql);
    }

    /*
     * READING
     */
    /**
     * returns a site's information
     */
    public function getSite($columns, $formato, $chardivisor = '', $charend = '', $order = '') {
		
        $sql = "SELECT
                    *
                FROM
                    taxonomy
                WHERE
                    class='categoria-chefe'
                ";
        $query = Connection::getInstance()->query($sql);
        $t = count($query);
        $c = 0;
        foreach($query as $menu) {
            $str = $formato;
            for($i = 0; $i < count($columns); $i++) {
                $str = str_replace("&%" . $columns[$i], $menu[$columns[$i]], $str);
            }
            echo $str;
            if($c < $t-1) {
                echo $chardivisor;
            } else {
                echo $charend;
            }
            $c++;
        }
    }

    /**
     * getStructures()
     *
     * Get all sites and its substructures.
     *
     * @return <array> $params
     */
    public function getStructures($params = array()) {
	
		$where = '';
		if( !empty($params['site']) && is_numeric($params['site']) )
			$where = "AND c.id='".$params['site']."'";
        /**
         * SITES
         */
        $sql = "SELECT
                    c.*, c.name as name
                FROM
                    taxonomy AS c
                WHERE
                    c.father_id='0'
					$where
                ";

        $query = Connection::getInstance()->query($sql);
        $result = array();
		$stIds = array();
		
		$invisibleStructures = $this->getInvisibleStructures();
        /*
         * Each site
        */
        foreach( $query as $key=>$sites) {
            $result[$key]['Site'] = $sites;

            /*
             * Get Structures of each site
            */
            $structures = $this->getStructuresByFather($sites['id']);
            if( is_array($structures) ) {

				foreach( $structures as $stKey => $sts ){
					/*
					 * RELATED AND VISIBLE?
					 *
					 * Clear invisible Structures
					 */
					if( in_array($sts['id'], $invisibleStructures) )
						unset($structures[$stKey]);
					else
						$stIds[] = $sts['id'];
				}
                $result[$key]['Structures'] = $structures;
            }
        }

		$slaves = $this->getRelatedSlaves($stIds);
		$masters = $this->getRelatedMasters($stIds);
		if( !empty($slaves) ){
			// loop through
			foreach( $result as $siteKey=>$site ){
				// loop through structures
				foreach( $site['Structures'] as $stKey=>$st ){
					
					if( array_key_exists($st['id'], $slaves) ){
						
						$result[$siteKey]['Structures'][$stKey]['slaves'] = $slaves[$st['id']];
					}
				}
			}
		}
		if( !empty($masters) ){
			// loop through sites
			foreach( $result as $siteKey=>$site ){
				// loop through structures
				foreach( $site['Structures'] as $stKey=>$st ){
					
					if( array_key_exists($st['id'], $masters) ){
						$result[$siteKey]['Structures'][$stKey]['masters'] = $masters[$st['id']];
					}
				}
			}
		}
        return $result;

    }

	function getInvisibleStructures(){
        $sql = "SELECT
                    local
                FROM
                    config
                WHERE
                    tipo='mod_conf' AND
					propriedade='related_and_visible' AND
					valor='0'
                ";
        $query = Connection::getInstance()->query($sql);
		$result = array();
		foreach( $query as $value ){
			$result[] = $value["local"];
		}
		return $result;
	}

	function getRelatedSlaves($ids = array()){
		if( empty($ids) )
			return array();
		
		if( is_string($ids) )
			$ids = array($ids);
		
		$whereStatement = "master_id IN ('".implode("','", $ids)."')";
		
		$sql = "SELECT * FROM
					aust_relations
				WHERE
					$whereStatement
				";
		$result = Connection::getInstance()->query($sql);
		if( empty($result) )
			return array();
		
		$return = array();
		foreach( $result as $slave ){
			$return[$slave['master_id']][] = $slave;
		}
		
		return $return;
	}

	function getRelatedMasters($ids = array()){
		if( empty($ids) )
			return array();
		
		if( is_string($ids) )
			$ids = array($ids);
		
		$whereStatement = "slave_id IN ('".implode("','", $ids)."')";
		
		$sql = "SELECT * FROM
					aust_relations
				WHERE
					$whereStatement
				";
		
		$result = Connection::getInstance()->query($sql);
		if( empty($result) )
			return array();
		
		$return = array();
		foreach( $result as $master ){
			$return[$master['slave_id']][] = $master;
		}
		
		return $return;
	}	
    /**
     * getStructuresByFather()
     *
     * Fetch all structures of a given father
     *
     * @param <int> $id
     * @return <array>
     */
    public function getStructuresByFather($id='') {
        if( empty($id) )
            return false;

        /*
         * Structures of given site
        */
        $sql = "SELECT
                    lp.*, lp.name as name, lp.type as type,
                    ( SELECT COUNT(*)
                    FROM
                    ".self::$austTable." As clp
                    WHERE
                    clp.father_id=lp.id
                    ) As num_sub_nodes
                FROM
                    ".self::$austTable." AS lp
                WHERE
                    lp.father_id = '".$id."' AND
                    lp.class = 'estrutura'
                ORDER BY
                    lp.type DESC,
                    lp.name ASC
        ";
        $query = Connection::getInstance()->query($sql);

        return $query;
    }

    /**
     * Returns information from the selected structure
     *
     * @param int $austNode
     * @return array
     */
    public function getStructureById( $austNode ) {
		
		if( array_key_exists($austNode, $this->_structureCache) )
			return $this->_structureCache[$austNode];
	
		$sql = "SELECT * FROM ".Aust::$austTable." WHERE id='".$austNode."'";
        $result = Connection::getInstance()->query( $sql );

		if( !empty($result) )
			$result = reset($result);
			
		$this->_structureCache[$austNode] = $result;
        return $result;
    }

    /**
     * Retorna o nome de cada estrutura do sistema (notícias, artigos, etc) no formato ARRAY
     *
     * @param array $param
     * @return array
     */
    function LeEstruturasParaArray($param = '') {
        if(!empty($param['where'])) {
            $where = $param['where'];
        } else {
            $where = "class='estrutura'";
        }

        $orderby = (empty($param['orderby'])) ? '' : $param['orderby'];
        $limit = (empty($param['limit'])) ? '' : $param['limit'];

        $sql = "SELECT
                    *
                FROM
                    taxonomy
                WHERE
                    ".$where."
                ".$orderby." ".$limit;
        $query = Connection::getInstance()->query($sql);

        $estruturas_array = array();
        foreach($query as $chave=>$valor) {
            $estruturas_array[$valor['id']]['nome'] = $valor['name'];
            $estruturas_array[$valor['id']]['tipo'] = $valor['type'];
            $estruturas_array[$valor['id']]['id'] = $valor['id'];
        }
        return $estruturas_array;
    }

    /**
 	 * What module is responsible for a given structure?
	 *
	 * @param $node (int)
	 */
    function structureModule($node) {
	
		if( array_key_exists($node, $this->_structureModuleCache) )
			return $this->_structureModuleCache[$node];
	
        $sql = "SELECT
                	type
                FROM
                	taxonomy
                WHERE
                	id=$node";
        $query = Connection::getInstance()->query($sql);

		$this->_structureModuleCache[$node] = $query[0]['type'];
        return $this->_structureModuleCache[$node];
    }

    // retorna o nome legível do módulo
    function LeModuloDaEstruturaLegivel($node) {
        $sql = "SELECT
                        type
                FROM
                        taxonomy
                WHERE
                        id=$node";

        $query = Connection::getInstance()->query($sql);
        $tipo = $query[0]['type'];
        if(is_file(MODULES_DIR.$tipo.'/config.php')) {
            include(MODULES_DIR.$tipo.'/config.php');
            return $modInfo['name'];
        } else {
            return NULL;
        }
    }

    public function getField($node, $field = '') {
		if( empty($field) )
			$field = "*";
			
        $sql = "SELECT
                    $field
                FROM
                    taxonomy
                WHERE
                    id=$node";
        $query = Connection::getInstance()->query($sql);
		if( $field == "*" )
	        return $query[0];
		else
        	return $query[0][$field];
    }

    /**
     * Retorna o nome de estrutura/categoria de acordo com seu ID
     *
     * @param int $node É o ID da estrutura/categoria a ser buscada
     * @return string Nome da estrutura/categoria
     */
    public function leNomeDaEstrutura($node) {
        $sql = "SELECT
                    name
                FROM
                    taxonomy
                WHERE
                    id=$node";
        $query = Connection::getInstance()->query($sql);
        return $query[0]['name'];
    }

    function LimpaVariavelCategorias() {
        if(is_array($this->AustCategorias)) {
            foreach($this->AustCategorias as $key=>$valor) {
                array_pop($this->AustCategorias);
            }
        }
    }


    /**
     * Retorna todas as filhas da categoria
     *
     * @author Alexandre de Oliveira (chavedomundo@gmail.com)
     *
     * @param string    $categoriachefe
     * @param int       $parent
     * @param int       $level
     * @param int       $current_node
     * @return array    retorna array com todas as filhas da categoria dita categorias requisitadas
     */
    function categoriasFilhas($params) {

        /**
         * Trata cada variável recebida
         */
        $pai = (empty($params['pai'])) ? 0 : $params['pai'];
        $categoriaChefe = (empty($params['categoriaChefe'])) ? '' : $params['categoriaChefe'];
        $nivel = (empty($params['nivel'])) ? 0 : $params['nivel'];
        $nodeAtual = (empty($params['nodeAtual'])) ? 0 : $params['nodeAtual'];
        /**
         * Precisa-se melhorar esta função. Infelizmente, PHP 5.2 ainda não suporte método dentro de método,
         * portanto precisamos usar um método externo
         */
        $this->LeCategoriasFilhasCopy($categoriaChefe, $pai, $nivel, $nodeAtual); // gambiarra

        if($pai >= 0) {
            $this->AustCategorias[$pai] = '';
        }
        $resultado = $this->AustCategorias;
        $this->LimpaVariavelCategorias();
        return $resultado;
    }

    // gambiarra para que LeCategoriasFilhas possa rodar em loop e retornar $this->AustCategorias e limpando esta variÃ¡vel no final
    function LeCategoriasFilhasCopy($categoriachefe, $parent=0, $level=0, $current_node=-1) {
        /**
         * Guarda qual o id do pai para carregar suas filhas
         */
        $where = "lp.father_id = '$parent'";
        /**
         * Se não for especificada uma estrutura, carrega todas as categorias da categoria chefe
         * especificada
         */
        if($parent == 0) {
            if(is_int($categoriachefe)) {
                $where = $where . " AND lp.id='".$categoriachefe."'";
            } elseif(is_string($categoriachefe)) {
                $where = $where . " AND lp.name='".$categoriachefe."'";
            }
        }
        /**
         * Monta o SQL
         */
        $sql="SELECT
					lp.id, lp.father_id, lp.name, lp.class,
					( SELECT COUNT(*)
						FROM
							".self::$austTable." As clp
						WHERE
							clp.father_id=lp.id
					) As num_sub_nodes
				FROM
					".self::$austTable." AS lp
				WHERE
                $where
                ";

        $query = Connection::getInstance()->query($sql);

        $i = 0;
        $items = '';
        foreach ( $query as $chave=>$myrow ) {

            $this->AustCategorias[$myrow['id']] = $myrow['name'];

            //chamar recursivamente a função
            $items.=$this->LeCategoriasFilhasCopy($categoriachefe, $myrow["id"], $level+1, $current_node);

        }
    }

    /**
     * DEPRECIADO!!!!!
     *
     * Use categoriasFilhas() no lugar desta
     *
     * Retorna todas as filhas da categoria
     *
     * @author Alexandre de Oliveira (chavedomundo@gmail.com)
     *
     * @param string    $categoriachefe
     * @param int       $parent
     * @param int       $level
     * @param int       $current_node
     * @return array    retorna array com todas as filhas da categoria dita categorias requisitadas
     */
    function LeCategoriasFilhas($categoriachefe, $parent=0, $level=0, $current_node=-1) {
        //trigger_error('Use categoriasFilhas() instead', E_USER_NOTICE);

        $this->LeCategoriasFilhasCopy($categoriachefe, $parent, $level, $current_node); // gambiarra

        if($parent >= 0) {
            $this->AustCategorias[$parent] = 'tetesteste';
        }
        $resultado = $this->AustCategorias;
        $this->LimpaVariavelCategorias();
        return $resultado;
    }

    /**
     * DEPRECIADO!!!!!
     *
     * Use categoriasFilhas() no lugar desta
     *
     * Retorna todas as filhas da categoria
     *
     * @author Alexandre de Oliveira (chavedomundo@gmail.com)
     *
     * @param string    $categoriachefe
     * @param int       $parent
     * @param int       $level
     * @param int       $current_node
     * @return array    retorna array com todas as filhas da categoria dita categorias requisitadas
     */
    // LISTAR: funÃ§Ã£o que retorna diretÃ³rio e arquivo para include da listagem do mÃ³dulo da estrutura com id $aust_node
    function AustListar($aust_node = '0') {
        $pasta_do_modulo = $this->structureModule($aust_node);
        if(is_file(MODULES_DIR.$pasta_do_modulo.'/listar.php')) {
            return MODULES_DIR.$pasta_do_modulo.'/listar.php';
        } else {
            return VIEWS_DIR.'content/listar.inc.php';
        }
    }

    // Lê somente estruturas que não devem ter categorias e grava em uma $_SESSION
    function EstruturasSemCategorias() {
	
		if( !empty($_SESSION['structure_only']) )
        	unset( $_SESSION['structure_only']);
		
        $diretorio = MODULES_DIR; // pega o endereço do diretório
        foreach (glob($diretorio."*", GLOB_ONLYDIR) as $pastas) {
            if(is_file($pastas.'/config.php')) {
                include($pastas.'/config.php');
                if($modInfo['structure_only']) {

                    $tmparray = array_reverse( explode("/", $pastas));
                    $_SESSION['structure_only'][] = $tmparray[0];
                }
                //echo 'oi' ;
            }
        }


    }


    /**
     * VERIFICAÇÕES
     */

    // verifica se existe alguma categoria instalada e retorna TRUE ou FALSE
    public function Instalado() {
        $sql = "SELECT
                    id
                FROM
                    taxonomy";
        return Connection::getInstance()->count($sql);
    }

    /*
     *
     * RENDERIZAÇÃO
     *
     */
    /**
     * getCategoryHtmlSelect()
     *
     * Retorna <select> com as categorias atuais
     *
     * @param <type> $austNode
     * @param <type> $currentNode
     * @return <string>
     */
    public function getCategoryHtmlSelect($austNode, $currentNode = ''){
        $tmp = BuildDDList( Registry::read('austTable') ,'frmnode_id', User::getInstance()->tipo ,$austNode, $currentNode, false, true);
        return $tmp;
    }

}


?>