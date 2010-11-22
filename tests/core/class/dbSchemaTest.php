<?php
require_once 'PHPUnit/Framework.php';

#####################################

require_once 'tests/config/auto_include.php';
require_once 'core/class/SQLObject.class.php';
require_once 'core/class/dbSchema.class.php';

#####################################

class dbSchemaTest extends PHPUnit_Framework_TestCase
{
    public $dbConfig = array();

    public $conexao;

    public function setUp(){

        /*
         * Informações de conexão com banco de dados
         */

        
        
        $this->conexao = Connection::getInstance();

        require("core/config/installation/dbschema.php");
        $this->dbSchema = new dbSchema($dbSchema, $this->conexao);
    }

    function tearDown(){
        $this->dbSchema = false;
    }

    public function testSchemaIsSet(){
        $this->assertType('array', $this->dbSchema->dbSchema );
    }

    public function testVerificaSchemaExistente(){
        $this->assertType('integer', $this->dbSchema->verificaSchema() );
    }

    function testTabelasAtuais(){
        $this->assertType('array', $this->dbSchema->tabelasAtuais());
    }

    function testIsDbSchemaFormatOk(){
        $this->assertTrue($this->dbSchema->isDbSchemaFormatOk());
        $this->assertFalse($this->dbSchema->isDbSchemaFormatOk('blabla'));
    }


}
?>