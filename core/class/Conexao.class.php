<?php
/**
 * Arquivo responsável pela Conexão com Bases de Dados
 *
 * Integra PDO (se presente) ou conexão normal.
 *
 * ATENÇÃO:
 *      - Se você encontrar erro de 'mysql unbuffered', erro 2014, isto significa
 *        que você precisa liberar a memória após as querys que você fez.
 *        Faça isto através do método PDOStatement::closeCursor();
 *
 * @package DB
 * @name Conexao
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.1
 * @since v0.1.5, 30/05/2009
 */
class Conexao extends SQLObject {
    /**
     *
     * @var bool Se a base de dados existe. Serve para verificação simples.
     */
	public $DBExiste;
    /**
     *
     * @var Resource Possui a conexão com o DB
     */
	public $conn;
    /**
     *
     * @var <type> ????????????????????????
     */
    private $db;

    /**
     *
     * @var <type> Contém toda a configuração de acesso à base de dados
     */
    protected $dbConfig;

    private $debugLevel = 0;


    /**
     * Cria conexão com o DB. Faz integração de conexões se PDO existe ou não.
     *
     * @param array $conexao Contém parâmetros de conexão ao DB
     */
	function __construct($dbConfig){
            
        $this->dbConfig = $dbConfig;

        /**
         * Se a extensão PDO, usada para conexão com vários tipos de bases de dados
         */
        if($this->PdoExtension()){
            $this->PdoInit($dbConfig);
            if($this->debugLevel == 1){
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            }
        }
        /**
         * Conexão comum
         */
        else {
            $this->DbConnect($dbConfig);
        }
	}

    /**
     * Efetua conexão via PDO.
     * 
     * Esta função é executada somente se a extensão 'PDO' estiver ativada.
     *
     * @param array $dbConfig Possui dados para conexão no DB
     *      driver: tipo de driver/db (mysql, postgresql, mssql, oracle, etc)
     *      database: nome da base de dados
     *      server: endereço da base de dados
     *      username: nome de usuário para acesso ao db
     *      password: senha para acesso ao db
     */
    protected function PdoInit($dbConfig){

        $dbConfig['driver'] = (empty($dbConfig['driver'])) ? 'mysql' : $dbonfig['driver'];

        if( $this->conn = new PDO(
                                $dbConfig['driver'].':host='.$dbConfig['server'].';'
                                .   'dbname='.$dbConfig['database'],
                                $dbConfig['username'], $dbConfig['password'],
                                array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
                            )

        ){
            $this->DBExiste = true;
        }

        if( !empty($dbConfig["encoding"]) ){
            $this->conn->exec("SET NAMES '".$dbConfig["encoding"]."'");
            $this->conn->exec("SET character_set_connection=".$dbConfig["encoding"]);
            $this->conn->exec("SET character_set_client=".$dbConfig["encoding"]);
            $this->conn->exec("SET character_set_results=".$dbConfig["encoding"]);
        }

        $this->pdo = $this->conn;

        //$this->con = $dbConfig[]':host=localhost;dbname=test';
    }
    /**
     * Conexão comum ao DB. Padrão MySQL.
     *
     * @param array $dbConfig Possui dados para conexão no DB
     *      driver: tipo de driver/db (mysql, postgresql, mssql, oracle, etc)
     *      database: nome da base de dados
     *      server: endereço da base de dados
     *      username: nome de usuário para acesso ao db
     *      password: senha para acesso ao db
     */
    protected function DbConnect($dbConfig){
        $conexao = $dbConfig;
		$conn = mysql_connect($conexao['server'], $conexao['username'], $conexao['password']) or die('Erro ao encontrar servidor');
		if(mysql_select_db($conexao['database'], $conn)){
			$this->DBExiste = TRUE;
            $this->db = $db;
            $this->conn = $conn;
		} else {
			$this->DBExiste = FALSE;
		}
    }

    /**
     * CRUD
     */
    /**
     * Função integradora para Query's
     *
     * @param string $sql
     * @return array Resultado em formato array
     */
    public function query($sql, $type = ""){
        /**
         * Se a extensão PDO está ativada
         */
        if($this->PdoExtension()){
            /**
             * Roda o SQL e trás os resultados para um array
             */

            /**
             * Se o resultado deve ser num formato diferente
             */
            if ( !empty( $type ) ){
                /**
                 * Se "ASSOC", usa por padrão PDO::FETCH_ASSOC.
                 */
                if( $type == "ASSOC" ){
                    $query = $this->conn->query( $sql, PDO::FETCH_ASSOC );
                }
                /**
                 * Se for um tipo específico de resultado desejado aceito pelo
                 * PDO, carrega automaticamente o tipo selecionado em
                 * PDO::query().
                 */
                else if (
                    in_array( 
                        $type,
                        array(
                            PDO::FETCH_ASSOC,
                            PDO::FETCH_BOTH,
                            PDO::FETCH_BOUND,
                            PDO::FETCH_CLASS,
                            PDO::FETCH_INTO,
                            PDO::FETCH_LAZY,
                            PDO::FETCH_NUM,
                            PDO::FETCH_OBJ,
                        )
                    )
                ){
                    $query = $this->conn->query( $sql, $type );
                }
            } else {
                $query = $this->conn->query($sql);
            }

            if( !empty($query) ){

                foreach($query as $chave=>$valor){
                    $result[] = $valor;
                }
                $query->closeCursor();
            }
            
        } else {
            $mysql = mysql_query($sql);
            
            while($dados = mysql_fetch_array($mysql)){
                $result[] = $dados;
            }
        }
        
        /**
         * Se não houverem resultados, instancia variável para evitar erros
         */
        if(empty($result)){
            $result = array();
        }
        return $result;
    }

    /**
     * Comando específico para uso com PDO. Se PDO não está presente,
     * chama $this->query.
     *
     * @param string $sql
     * @return <type> Retorna resultado da operação
     */
    public function exec($sql, $mode = ''){
        /**
         * Se a extensão PDO está ativada
         */
        if($this->PdoExtension()){
            /**
             * Executa e retorna resultado
             */
            
            $result = $this->conn->exec($sql);
            
            /**
             * Quando executado CREATE TABLE, retorno com sucesso é 0 e
             * insucesso é false, não sendo possível diferenciar entre um e
             * outro. Este hack dá um jeitinho nisto.
             */
            if( in_array( $mode, array('CREATE_TABLE', 'CREATE TABLE') ) ){
                if($result == 0 AND !is_bool($result)){
                    return 1;
                } else {
                    return false;
                }
            }
            return $result;
        } else {
            return mysql_query($sql);
        }
    }

    /**
     * @todo - comentar
     *
     * @param <type> $sql
     * @return <type>
     */
    public function count($sql){
        /**
         * Se a extensão PDO está ativada
         */
        if($this->PdoExtension()){
            /**
             * Executa e retorna resultado
             */
            
            $mysql = $this->conn->prepare($sql);
            $mysql->execute();

            $total = $mysql->rowCount();
            $mysql->closeCursor();

            return $total;
        } else {
            /**
             * @todo Usar $this->query
             */
            $mysql = mysql_query($sql);
            return mysql_num_rows($mysql);
        }
    }

    /**
     * @todo - comentar
     */
    public function lastInsertId(){
        return $this->conn->lastInsertId();
    }

    /**
     * Retorna a lista de tabelas existentes
     *
     * @param string $db Banco de dados
     * @return array Retorna a lista de tabelas
     */
    public function listaTabelasDoDBParaArray($db = '' ){

        /**
         * Ajusta o DB (se nenhum especificado, usa o padrão do sistema)
         */
        $db = ( empty($db) ) ? $this->dbConfig['db'] : $db;

        /**
         * SQL para mostrar as tabelas da base de dados selecionada
         */
        $sql = "SHOW TABLES";

        /**
         * Carrega as tabelas, verifica a quantidade e ajusta uma array com elas
         * para retornar.
         */
        $query = $this->query($sql);
        $qntd_tabelas = count($query);
        if($qntd_tabelas > 0){
            foreach ( $query as $chave=>$valor ){
                $arraytmp[] = $valor[0];
            }
        }
        return $arraytmp;

    }

    /**
     * Retorna array com os campos da tabela selecionada
     *
     * @param string $tabela
     * @return array Array contendo todos os campos da tabela escolhida,
     * juntamente com informações como tipo e chaves.
     */
    public function listaCampos( $tabela ){
        $sql = "DESCRIBE ". $tabela;

        $query = $this->query($sql);

        /**
         * Loop pelos campos ajustando para o português os índices da array
         * de retorno.
         */
        foreach($query as $chave=>$valor){
            $query[$chave]['campo'] = $valor['Field'];
            $query[$chave]['tipo'] = $valor['Type'];
        }
        
        return $query;

    }




    // Verifica se algum WEBMASTER existe cadastrado no sistema. Retorna Falso ou Verdadeiro.
    public function VerificaAdmin(){
            $sql = "SELECT admins.id
                            FROM
                                    admins, admins_tipos
                            WHERE
                                    admins.tipo=admins_tipos.id
                            LIMIT 0,2";
    //echo $this->count($sql);
            //$mysql = $this->query($sql);
            return $this->count($sql);
    }

// retorna quantos registros existém na tabela CONFIG (tabela de configurações)
    public function VerificaConf(){
            $sql = "SELECT id
                            FROM
                                    config
                            LIMIT 0,2";
            $mysql = mysql_query($sql);
            return mysql_num_rows($mysql);
    }

    /**
     * VERIFICAÇÕES INTERNAS
     */
    /**
     *
     * @return bool A extensão PDO está ativa ou não
     */
    protected function PdoExtension(){
        /**
         * Se a extensão PDO está ativada ou não
         */
        //return false;
        return extension_loaded('PDO');
    }


    public function testConexao(){
        return 'Funcionando!';
    }
}

?>