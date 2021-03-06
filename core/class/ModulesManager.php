<?php
/**
 * Manages modules and its information.
 *
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.5, 30/05/2009
 */
class ModulesManager
{

    protected $db_tabelas;
    protected $sql_das_tabelas;
    protected $sql_registros;
    public $tabela_criar;

    protected $modDbSchema;
    /**
     * VARIÁVEIS DE AMBIENTE
     *
     * Conexão com banco de dados, sistema Aust, entre outros
     */
    /**
     *
     * @var class Classe responsável pela conexão com o banco de dados
     */
    public $conexao;
    /**
     *
     * @var class Classe responsável pela conexão com o banco de dados
     */
    public $aust;
    /**
     *
     * @var class Configurações do módulo
     */
    public $config;

    /**
     *
     * @var array parametros do __construct
     */
    public $params;

    /**
     *
     * @var string Diretório onde estão os módulos
     */
    const MOD_DIR = MODULES_DIR;
    /**
     *
     * @param array $param:
     *      'conexao': Contém a conexão universal
     */
    function __construct($param = array()) {

        $this->aust = Aust::getInstance();
        $this->conexao = Connection::getInstance();
        $this->user = User::getInstance();
        $this->config = Config::getInstance();

        if( !empty($param['modDbSchema']) ) {
            $this->modDbSchema = $param['modDbSchema'];

        }
    }

    /**
     * getInstance()
     *
     * Para Singleton
     *
     * @staticvar <object> $instance
     * @return <Conexao object>
     */
    static function getInstance(){
        static $instance;

        if( !$instance ){
            $instance[0] = new ModulesManager;
        }

        return $instance[0];

    }

	public function modelInstance($austNode = ""){
		if( empty($austNode) )
			return false;
		
		if( !is_numeric($austNode) && is_string($austNode) ){
			if( !$this->exists($austNode) )
				return false;
		}
		
		include_once($this->modelClassFile($austNode));
		$modelClassName = $this->modelClassName($austNode);
		return new $modelClassName($austNode);
	}

	public function modelClassFile($austNode){
		if( is_numeric($austNode) )
			$directory = $this->directory($austNode);
		elseif( is_string($austNode) )
			$directory = $austNode."/";

		return MODULES_DIR.$directory.$this->modelClassName($austNode).".php";
	}

	public function modelClassName($austNode){
		if( is_numeric($austNode) )
			$directory = $this->directory($austNode);
		elseif( is_string($austNode) )
			$directory = $austNode."/";
		
		if( !is_file(MODULES_DIR.$directory.MOD_CONFIG) )
			return false;
		
		include(MODULES_DIR.$directory.MOD_CONFIG);
		return $modInfo["className"];
	}

	public function directory($austNode){
		if( is_numeric($austNode) )
			return Aust::getInstance()->structureModule($austNode).'/';
		elseif( is_string($austNode) )
			return $austNode.'/';
		
		return false;
	}
	
	public function exists($moduleName){
		return is_dir(MODULES_DIR.$moduleName);
	}

    /*
     *
     * MÈTODOS DE SUPORTE
     * 
     */

    /**
     * getModuleInformation()
     *
     * Retorna informações gerais sobre um módulo.
     *
     * @param <array> $params
     * 
     *      O formato é o que segue:
     * 
     *          array(
     *              'modulo_1', 'modulo_2', 'modulo_3'
     *          );
     *
     * @return <array>
     */
    public function getModuleInformation($params){
        /*
         * Load Migrations
         */
        $migrationsMods = MigrationsMods::getInstance();
        //$migrationsStatus = MigrationsMods::getInstance()->status();
		$result = array();
        if( is_array($params) ){
            
            foreach( $params as $modName ){
                $pastas = MODULES_DIR.$modName;

                /**
                 * Carrega arquivos do módulo atual
                 */
                if( !is_file($pastas.'/'.MOD_CONFIG) )
                    continue; // cai fora se não tem config
                
                include($pastas.'/'.MOD_CONFIG);

                $result[$modName]['version'] = MigrationsMods::getInstance()->isActualVersion($modName);
                $result[$modName]['path'] = $modName;
                $result[$modName]['config'] = $modInfo;

            }
        }

        return $result;
    }


    /**
     * verificaInstalacaoTabelas()
     *
     * @return <bool>
     */
    public function verificaInstalacaoTabelas() {

        if( empty($this->modDbSchema) )
            return false;
        
        $schema = $this->modDbSchema;
        foreach( $schema as $tabela=>$valor ) {
            $sql = "DESCRIBE ". $tabela;
            $query = Connection::getInstance()->query($sql);
            if(!$query) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * verificaInstalacaoRegistro()
     *
     * @return <bool>
     */
    public function verificaInstalacaoRegistro($options = array()) {

        if( !empty($options["pasta"]) ){
            $where = "directory='".MODULES_DIR.$options["pasta"]."'";
        }

        $sql = "SELECT id from modules_installed WHERE ".$where;
        $query = Connection::getInstance()->query($sql);
		
        if( empty($query[0]['id']) ){
            return false;
        } else {
            return true;
        }
    }

    /**
     * saveModConf()
     *
     * Salva configurações de um módulo no banco de dados automaticamente.
     *
     * Para exemplo de como usar, veja o código de configuração do módulo textos
     *
     * @param array $params
     * @return bool
     */
    public function saveModConf($params) {

        global $administrador;

        /*
         * Se for para configurar e tiver dados enviados
         */
        if( !empty($params['conf_type'])
            AND $params['conf_type'] == "mod_conf"
            AND !empty($params['data'])
            AND !empty($params['aust_node']) ) {

            $data = $params["data"];
            Connection::getInstance()->exec("DELETE FROM config WHERE tipo='mod_conf' AND local='".$params["aust_node"]."'");
            foreach( $data as $propriedade=>$valor ) {

                $paramsToSave = array(
                    "table" => "config",
                    "data" => array(
                    "tipo" => "mod_conf",
                    "local" => $params["aust_node"],
                    "autor" => User::getInstance()->LeRegistro("id"),
                    "propriedade" => $propriedade,
                    "valor" => $valor
                    )
                );
                Connection::getInstance()->exec(Connection::getInstance()->saveSql($paramsToSave));
            }
        }
        return true;
    }

    function loadModConf($params) {
        $sql = "SELECT * FROM config WHERE tipo='mod_conf' AND local='".$params["aust_node"]."' LIMIT 200";

        $queryTmp = Connection::getInstance()->query($sql, "ASSOC");

        foreach($queryTmp as $valor) {
            $query[$valor["propriedade"]] = $valor;
        }
        return $query;
    }

    
    /**
     *
     * RESPONSER
     *
     */
    /**
     * Carrega conteúdo para leitura externa. Retorna, geralmente, em array.
     *
     * @global Aust $aust
     * @return array
     */
    public function retornaResumo() {
        global $aust;

        /**
         * Configurações específicas deste módulo
         */
        $moduloConf = $this->config['arquitetura'];

        /**
         * Pega as estruturas deste módulo através do método a seguir. Em
         * $param['where'] tem-se uma parte do código SQL necessário para tal.
         */
        $param = array(
            "where" => "tipo='textos' AND class='estrutura'"
        );

        $estruturas = Aust::getInstance()->LeEstruturasParaArray($param);
        /**
         * Se há estruturas instaladas, rodará uma por uma tomando os conteúdos
         */
        if(!empty($estruturas)) {

        /**
         * Se o retorno estiver configurado para array ou vazio, retorna array.
         * Alguns módulos retornam textos diretamente
         */
            if( empty($moduloConf['returnTipo'])
                OR $moduloConf['returnTipo'] == 'array' ) {

            /**
             * Cada estrutura possui várias categorias.
             *
             * Vamos:
             *      - um loop por cada estrutura
             *      - um loop por cada categoria de cada estrutura
             *
             * O resultado será todo guardado em $conteudo
             */
                foreach($estruturas as $chave=>$valor) {

                    $response['intro'] = 'A seguir, os últimos conteúdos.';
                    $categorias = Aust::getInstance()->categoriasFilhas( array( 'pai' => $valor['id'] ) );

                    if(!empty($categorias)) {
                    /**
                     * Pega cada índice contendo id das categorias da respectiva estrutura
                     */
                        foreach($categorias as $cChave=>$cValor) {
                            $tempCategorias[] = $cChave;
                        }

                        /**
                         * Monta SQL
                         *
                         * Monta cláusula WHERE com as categorias selecionadas e
                         * desmancha $tempCategorias
                         */
                        $sql = "SELECT
                                    id, titulo
                                FROM
                                    ".$moduloConf['table']."
                                WHERE ".$moduloConf['foreignKey'] . " IN ('" . implode("','", $tempCategorias) ."')
                                ORDER BY id DESC
                                LIMIT 0,4
                                ";

                        $result = Connection::getInstance()->query($sql);

                        foreach($result as $dados) {
                        /**
                         * Toma os dados do DB e os guarda
                         */
                            $tempResponse[] = $dados;
                        }

                        /**
                         * Organiza array que vai ser retornada nesta função
                         */
                        $response[$valor['id']]['titulo'] = $valor['nome'];
                        $response[$valor['id']]['conteudo'] = (empty($tempResponse)) ? array() : $tempResponse;

                        unset($tempCategorias);
                        unset($tempResponse);
                    }
                }
            }
        }
        return $response = (empty($response)) ? array() : $response;
    }

    /*
     *
     * INTERFACE
     *
     */
    public function loadHtmlEditor(){
        return loadHtmlEditor();
    }

    /**
     * @todo - trataImagem() não deveria estar nesta função.
     */
    /**
     * trataImagem()
     *
     * Trata uma imagem
     *
     * @param array $files O mesmo $_FILE vindo de um formulário
     * @param string $width Valor padrão de largura
     * @param string $height Valor padrão de altura
     * @return array
     */
    function trataImagem($files, $width = "1024", $height = "768") {

        /*
         * Toma dados de $files
         */
        $frmarquivo = $files['tmp_name'];
        $frmarquivo_name = $files['name'];
        $frmarquivo_type = $files['type'];

        /*
         * Abre o arquivo e tomas as informações
         */
        $fppeq = fopen($frmarquivo,"rb");
        $arquivo = fread($fppeq, filesize($frmarquivo));
        fclose($fppeq);

        /*
         * Cria a imagem e toma suas proporções
         */
        $im = imagecreatefromstring($arquivo); //criar uma amostra da imagem original
        $largurao = imagesx($im);// pegar a largura da amostra
        $alturao = imagesy($im);// pegar a altura da amostra

        /*
         * Configura o tamanho da nova imagem
         */
        if($largurao > $width)
            $largurad = $width;
        else
            $largurad = $largurao; // definir a altura da miniatura em px

        $alturad = ($alturao*$largurad)/$largurao; // calcula a largura da imagem a partir da altura da miniatura
        $nova = imagecreatetruecolor($largurad,$alturad); // criar uma imagem em branco
        //imagecopyresized($nova,$im,0,0,0,0,$largurad,$alturad,$largurao,$alturao);
        imagecopyresampled($nova,$im,0,0,0,0,$largurad,$alturad,$largurao,$alturao);

        ob_start();
        imagejpeg($nova, '', 100);
        $mynewimage = ob_get_contents();
        ob_end_clean();

        /*
         * Prepara dados resultados para retornar
         */
        imagedestroy($nova);

        $result["filesize"] = strlen($mynewimage);
        //$result["filedata"] = addslashes($mynewimage);
        $result["filedata"] = $mynewimage;
        $result["filename"] = $frmarquivo_name;
        $result["filetype"] = $frmarquivo_type;

        return $result;

    }











    /**
     * @todo - ajustar código para baixo
     */
    /**
     * Salva dados sobre o módulo na base de dados.
     *
     * Usado após a criação das tabelas do módulo.
     *
     * @param array $param
     * @return bool
     */
    function configuraModulo($param) {

    /**
     * Ajusta cada variável enviada como parâmetro
     */
    /**
     * $tipo:
     */
        $tipo = (empty($param['tipo'])) ? '' : $param['tipo'];
        /**
         * $chave:
         */
        $chave = (empty($param['chave'])) ? '' : $param['chave'];
        /**
         * $valor:
         */
        $valor = (empty($param['valor'])) ? '' : $param['valor'];
        /**
         * $pasta:
         */
        $pasta = (empty($param['pasta'])) ? '' : MODULES_DIR.$param['pasta'];
        /**
         * $modInfo:
         */
        $modInfo = (empty($param['modInfo'])) ? '' : $param['modInfo'];
        /**
         * $autor:
         */
        $autor = (empty($param['autor'])) ? '' : $param['autor'];


        Connection::getInstance()->exec("DELETE from modules_installed WHERE directory='".$pasta."'");

        $sql = "INSERT INTO
                    modules_installed
                        (property,value,directory,name,description,structure_only,admin_id)
                VALUES
                    ('$chave','$valor','$pasta','".$modInfo['name']."','".$modInfo['description']."','".$modInfo['structure_only']."','$autor')
            ";

        if(Connection::getInstance()->exec($sql, 'CREATE_TABLE')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
     *
     *	funções de verificação ou leitura
     *
     */
    function leModulos() {

        $modules = Connection::getInstance()->query("SELECT * FROM modules_installed");
        //pr($modules);
        return $modules;

        $diretorio = MODULES_DIR; // pega o endereço do diretório
        foreach (glob($diretorio."*", GLOB_ONLYDIR) as $pastas) {
            if (is_dir ($pastas)) {
                if( is_file($pastas.'/'.MOD_CONFIG )) {
                    if( include($pastas.'/'.MOD_CONFIG )) {
                        if(!empty($modInfo['name'])) {
                            $str = $result_format;
                            $str = str_replace("&%nome", $modInfo['name'] , $str);
                            $str = str_replace("&%description", $modInfo['description'], $str);
                            $str = str_replace("&%pasta", str_replace($diretorio,"",$pastas), $str);
                            $str = str_replace("&%directory", str_replace($diretorio,"",$pastas), $str);
                            echo $str;
                            if($c < $t-1) {
                                echo $chardivisor;
                            } else {
                                echo $charend;
                            }
                            $c++;
                        }
                        unset($modulo);
                    }

                }
            }
        }
    } // fim leModulos()

    // retorna o nome da tabela da estrutura
    function LeTabelaDaEstrutura($param='') {
        return $this->tabela_criar;
    }

    /*
     * retorna o nome de cada módulo e suas informações em formato array
     */
    function LeModulosParaArray() {
        $sql = "SELECT
                    DISTINCT directory, name, property, value
                FROM
                    modules_installed
                ";
        $query = Connection::getInstance()->query($sql);
        $i = 0;
        foreach($query as $dados) {
            $return[$i]['pasta'] = $dados['directory'];
            $return[$i]['nome'] = $dados['name'];
            $return[$i]['chave'] = $dados['property'];
            $return[$i]['valor'] = $dados['value'];
            $i++;
        }
        return $return;
    }

}
?>