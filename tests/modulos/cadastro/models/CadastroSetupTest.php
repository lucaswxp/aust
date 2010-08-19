<?php
require_once 'PHPUnit/Framework.php';

#####################################
require_once 'tests/config/auto_include.php';
require_once 'core/class/SQLObject.class.php';
require_once 'core/config/variables.php';
#####################################

class CadastroSetupTest extends PHPUnit_Framework_TestCase
{

    function setUp(){
        $modelName = 'CadastroSetup';
        $mod = 'Cadastro';
        require_once 'modulos/'.$mod.'/'.MOD_MODELS_DIR.$modelName.'.php';
        
        $this->obj = new $modelName;

    }

	/**
	 * @dataProvider fieldTypesList
	 */
	function testIsAllowedFieldType($allowed = false, $fieldType){
		$this->assertSame( $allowed, $this->obj->isAllowedFieldType($fieldType) );
	}
	
		public function fieldTypesList(){
			return array(
				array(true, 'string', 					'varchar(250)'),
				array(true, 'text', 					'text'),
				array(true, 'date', 					'date'),
				array(true, 'pw', 						'varchar(250)'),
				array(true, 'file', 					'text'),
				array(true, 'relacional_umparaum', 		'int'),
				array(true, 'relacional_umparamuitos', 	'int'),
				array(false, 'uihiaehurg', 				''),
				array(false, 'uihiaehurg', 				''),
			);
		}

		/**
		 * @dataProvider decodedStrings
		 */
		function testEncodeStrings($final, $initial){
			$this->assertEquals( $final, $this->obj->encodeString($initial) );
		}

			function decodedStrings(){
				return array(
					array('minha_tabela', 'Minha Tabela'),
					array('tabela_com_c_cedilha', 'tábéla cOm ç ÇedilhA'),
					array('aaaaa_eeee_iiii_oooo_uuuu', 'áâäãà éêëè íîïì óôöò úûüù'),
					array('nnteste23um_dois', 'ñÑ#!@$teste23um dois'),
				);
			}

		/**
		 * @dataProvider possibleTableNames
		 */
		function testEncodeTableName($final, $initial){
			$this->assertEquals( $final, $this->obj->encodeTableName($initial) );
		}

			function possibleTableNames(){
				return array(
					array('minha_tabela', 'Minha Tabela'),
					array('tabela_com_c_cedilha', 'tábéla cOm ç ÇedilhA'),
					array('aaaaa_eeee_iiii_oooo_uuuu', 'áâäãà éêëè íîïì óôöò úûüù'),
				);
			}

		// Loop por Cada campo
			function testSanitizeString(){
				$this->assertEquals("há\\\"\\'\\\\teste", $this->obj->sanitizeString("há\"'\\teste"));
			}

			/**
			 * @dataProvider fieldTypesList
			 */
			function testGetFieldPhysicalType($allowed = false, $fieldType, $fieldPhysicalType){
				if( $allowed ){
					$this->assertEquals($fieldPhysicalType, $this->obj->getFieldPhysicalType($fieldType));
				}
			}

			/**
			 * @dataProvider possibleTableNames
			 */
			function testEncodeFieldName($final, $initial){
				$this->assertEquals( $final, $this->obj->encodeFieldName($initial) );
			}

			function testSetCommentForSql(){
				$this->assertEquals( "COMMENT 'Meu Comentário com \'(aspas)'", $this->obj->setCommentForSql("Meu Comentário com '(aspas)") );
			}


		/*
		 * FIELD SQLs
		 */
			function testGetFieldOrder(){
				$this->assertEquals('1', $this->obj->getFieldOrder());
				$this->assertEquals('2', $this->obj->getFieldOrder());
				$this->obj->getFieldOrder();
				$this->obj->getFieldOrder();
				$this->obj->getFieldOrder();
				$this->obj->getFieldOrder();
				$this->obj->getFieldOrder();
				$this->obj->getFieldOrder();
				$this->assertEquals('9', $this->obj->getFieldOrder());
				$this->assertEquals('10', $this->obj->getFieldOrder());
			}

			function testDecreaseFieldOrder(){
				$this->obj->getFieldOrder();
				$this->assertEquals('2', $this->obj->getFieldOrder());
				$this->obj->decreaseFieldOrder();
				$this->assertEquals('2', $this->obj->getFieldOrder());
				$this->obj->getFieldOrder();
				$this->assertEquals('4', $this->obj->getFieldOrder());
				$this->obj->decreaseFieldOrder();
				$this->assertEquals('4', $this->obj->getFieldOrder());

			}

			function testCreateFieldConfigurationSql_Password(){
				// data for creating field SQL
				$field = array(
					'name' => 'field_one',
					'label' => 'Field One',
					'comment' => 'This is a comment',
					'austNode' => '777',
					'author' => '777',
					'class' => 'password',
				);

				$expectedSql = "INSERT INTO cadastros_conf ".
	                           "(tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem) ".
	                           "VALUES ".
	                           "('campo','field_one','Field One','This is a comment',777,777,0,0,1,0,1,'password',1)";

				$this->assertEquals(
					$expectedSql,
					$this->obj->createFieldConfigurationSql_Password($field)
				);
			}

			function testCreateFieldConfigurationSql_File(){
				// data for creating field SQL
				$field = array(
					'name' => 'field_one',
					'label' => 'Field One',
					'comment' => 'This is a comment',
					'austNode' => '777',
					'author' => '777',
					'class' => 'arquivo',
				);

				$expectedSql = "INSERT INTO cadastros_conf ".
	                           "(tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem) ".
	                           "VALUES ".
	                           "('campo','field_one','Field One','This is a comment',777,777,0,0,1,0,1,'arquivo',1)";

				$this->assertEquals(
					$expectedSql,
					$this->obj->createFieldConfigurationSql_File($field)
				);
			}
				// Criação da tabela relacional de arquivos: SQL
				function testCreateSqlForFileTable(){
					$this->assertEquals(
						'CREATE TABLE minhatabela_arquivos('.
	                    'id int auto_increment,'.
	                    'titulo varchar(120),'.
	                    'descricao text,'.
	                    'local varchar(80),'.
	                    'url text,'.
	                    'arquivo_nome varchar(250),'.
	                    'arquivo_tipo varchar(250),'.
	                    'arquivo_tamanho varchar(250),'.
	                    'arquivo_extensao varchar(10),'.
	                    'tipo varchar(80),'.
	                    'referencia varchar(120),'.
	                    'categorias_id int,'.
	                    'adddate datetime,'.
	                    'autor int,'.
	                    'PRIMARY KEY (id),'.
	                    'UNIQUE id (id))',
						$this->obj->createSqlForFilesTable('minhatabela')
					);

					$this->assertEquals('minhatabela_arquivos', $this->obj->filesTableName);
				}

				// Configurações sobre a tabela relacional de arquivos: SQL
				function testCreateSqlForConfigurationOfFiles(){
					$this->obj->filesTableName = 'minhatabela_arquivos';
					$this->obj->austNode = '777';
					$sql = 
					    "INSERT INTO ".
	                    "cadastros_conf ".
	                    "(tipo,chave,valor,categorias_id,adddate,desativado,desabilitado,publico,restrito,aprovado) ".
	                    "VALUES ".
	                    "('estrutura','tabela_arquivos','minhatabela_arquivos',777, '".date('Y-m-d H:i:s')."',0,0,1,0,1)";

					$this->assertEquals(
						$sql,
						$this->obj->createSqlForFileConfiguration()
					);
				}

				// Execução da criação da tabela relacional de arquivos
				function testCreateTableForFiles(){

					$this->obj->mainTable = 'minhatabela';
					$this->obj->austNode = '777';
					$this->obj->createTableForFiles();

					$created = $this->obj->connection->hasTable('minhatabela_arquivos');
					$this->obj->connection->exec('DROP TABLE minhatabela_arquivos');
					$deleted = !$this->obj->connection->hasTable('minhatabela_arquivos');

					// verifica se houve as criações
					$this->assertTrue($created, "Table not CREATED.");
					$this->assertTrue($deleted, "Table not DELETED.");

					//novas criações não são permitidas
					$this->assertFalse( $this->obj->createTableForFiles() );
				}

				// Execução da configuração da tabela relacional de arquivos
				function testCreateConfigurationForFiles(){
					$this->obj->filesTableName = 'minhatabela_arquivos';
					$this->obj->austNode = '777';
					$this->obj->createConfigurationForFiles();

					$created = $this->obj->connection->query("SELECT id FROM cadastros_conf WHERE categorias_id='".$this->obj->austNode."' AND valor='minhatabela_arquivos' AND chave='tabela_arquivos'");
					if( !empty($created) ) $created = true;
					else $created = false;

					$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='".$this->obj->austNode."' AND valor='minhatabela_arquivos' AND chave='tabela_arquivos'");
					$deleted = $this->obj->connection->query("SELECT id FROM cadastros_conf WHERE categorias_id='".$this->obj->austNode."' AND valor='minhatabela_arquivos' AND chave='tabela_arquivos'");
					if( empty($deleted) ) $deleted = true;
					else $deleted = false;


					// verifica se houve as criações
					$this->assertTrue($created, "Files' Table, configuration not CREATED.");
					$this->assertTrue($deleted, "Files' Table, configuration not DELETED.");

				}


			function testCreateFieldConfigurationSql_RelationalOneToOne(){
				// data for creating field SQL
				$field = array(
					'name' => 'field_one',
					'label' => 'Field One',
					'comment' => 'This is a comment',
					'austNode' => '777',
					'author' => '777',
					'class' => 'relacional_umparaum',
					'refTable' => 'categorias',
					'refField' => 'nome',
				);

				$expectedSql = "INSERT INTO cadastros_conf ".
	                           "(tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem,ref_tabela,ref_campo) ".
	                           "VALUES ".
	                           "('campo','field_one','Field One','This is a comment',777,777,0,0,1,0,1,'relacional_umparaum',1,'categorias','nome')";

				$this->assertEquals(
					$expectedSql,
					$this->obj->createFieldConfigurationSql_RelationalOneToOne($field)
				);
			}

			function testCreateFieldConfigurationSql_RelationalOneToMany(){
				// data for creating field SQL
				$field = array(
					'name' => 'field_one',
					'label' => 'Field One',
					'comment' => 'This is a comment',
					'austNode' => '777',
					'author' => '777',
					'class' => 'relacional_umparamuitos',
					'refTable' => 'categorias',
					'refField' => 'nome',
					'referenceTable' => 'tabelaum_tabelarelacional_categorias'
				);

				$expectedSql = "INSERT INTO cadastros_conf ".
	                           "(tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem,ref_tabela,ref_campo,referencia) ".
	                           "VALUES ".
	                           "('campo','field_one','Field One','This is a comment',777,777,0,0,1,0,1,'relacional_umparamuitos',1,'categorias','nome','tabelaum_tabelarelacional_categorias')";

				$this->assertEquals(
					$expectedSql,
					$this->obj->createFieldConfigurationSql_RelationalOneToMany($field)
				);
			}
				//testa criação do nome da tabela referencial
				function testCreateReferenceTableName_RelationalOneToMany(){
					$params = array(
						'mainTable' => 'tabelaum',
						'secondaryTable' => 'tabeladois',
						'referenceField' => 'title'
					);
					$this->assertEquals('tabelaum_title_tabeladois', $this->obj->createReferenceTableName_RelationalOneToMany($params));
					// criar mais testes

				}
				// testa Sql para criar tabela referencial
				function testCreateReferenceTableSql_RelationalOneToMany(){
					$params = array(
						'referenceTable' => 'tabelaum_campo_tabeladois',
						'mainTable' => 'tabelaum',
						'secondaryTable' => 'tabeladois',
					);

	            	$sql = 'CREATE TABLE tabelaum_campo_tabeladois('.
	                       'id int auto_increment,'.
	                       'tabelaum_id int,'.
	                       'tabeladois_id int,'.
	                       'blocked varchar(120),'.
	                       'approved int,'.
	                       'created_on datetime,'.
	                       'updated_on datetime,'.
	                       'PRIMARY KEY (id), UNIQUE id (id)'.
	                       ')';
					$this->assertEquals($sql, $this->obj->createReferenceTableSql_RelationalOneToMany($params) );
				}


			function testCreateFieldConfigurationSql_String(){
				// data for creating field SQL
				$field = array(
					'name' => 'field_one',
					'label' => 'Field One',
					'comment' => 'This is a comment',
					'austNode' => '777',
					'author' => '777',
					'class' => 'string',
				);

				$expectedSql = "INSERT INTO cadastros_conf ".
	                           "(tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem) ".
	                           "VALUES ".
	                           "('campo','field_one','Field One','This is a comment',777,777,0,0,1,0,1,'string',1)";

				$this->assertEquals(
					$expectedSql,
					$this->obj->createFieldConfigurationSql_String($field)
				);
			}

		// sql para criação da tabela da nova estrutura propriamente dita
		function testCreateMainTableSql(){
			$params = $this->fieldsForCreation();
			$this->obj->mainTable = 'testunit';

	        $sql = 'CREATE TABLE testunit('.
	                   'id int auto_increment,'.
	                   'blocked varchar(120),'.
	                   'approved int,'.
	                   'created_on datetime,'.
	                   'updated_on datetime,'.
	                   'PRIMARY KEY (id), UNIQUE id (id)'.
	                ')';

			$this->assertEquals($sql, $this->obj->createMainTableSql($params));

//			$this->assertFalse($this->obj->createMainTableSql(array()));
		}

		public function fieldsForCreation(){

			return array(
				// field 1
				array(
					'name' => 'Campo 1',
					'type' => 'string',
					'description' => 'Campo 1 descrição',
				),
				// field 2
				array(
					'name' => 'Campo 2',
					'type' => 'text',
					'description' => 'Campo 2 descrição',
				),
				// field 3
				array(
					'name' => 'Campo 3',
					'type' => 'pw',
					'description' => 'Campo 3 descrição',
				),
				// field 4
				array(
					'name' => 'Campo 4',
					'type' => 'date',
					'description' => 'Campo 4 descrição',
				),
				// field 5
				array(
					'name' => 'Campo 5',
					'type' => 'file',
					'description' => 'Campo 5 descrição',
				),
				// field 6
				array(
					'name' => 'Campo 6',
					'type' => 'relacional_umparaum',
					'description' => 'Campo 6 descrição',
					'refTable' => 'reftable',
					'refField' => 'reffield',
				),
				// field 7
				array(
					'name' => 'Campo 7',
					'type' => 'relacional_umparamuitos',
					'description' => 'Campo 7 descrição',
					'refTable' => 'reftable',
					'refField' => 'reffield',
				),

			);
		}

		// salva dados da nova estrutura na tabela 'categorias'
		function testSaveStructureIntoDatabase(){

			$this->obj->connection->query("INSERT INTO categorias (nome,classe) VALUES ('TestePai777','categoria-chefe')");
			$lastInsert = $this->obj->connection->lastInsertId();

			// TEST #1
		    $params = array(
				'father' => $lastInsert,
		        'name' => 'Teste777',
		        'description' => 'Teste777',
		        'class' => 'cadastro',
		        'type' => 'estrutura',
		        'author' => '1',
		    );

			$result = $this->obj->saveStructure($params);
			$saved = reset($this->obj->connection->query("SELECT * FROM categorias WHERE nome='Teste777' AND classe='cadastro'") );

			$this->obj->connection->query("DELETE FROM categorias WHERE nome='Teste777'");
			$this->obj->connection->query("DELETE FROM categorias WHERE id='".$lastInsert."'");

			$this->assertType('int', $result);
			$this->assertArrayHasKey('nome', $saved);
			$this->assertEquals('Teste777', $saved['nome']);
			$this->assertEquals('cadastro', $saved['classe']);
			$this->assertEquals('estrutura', $saved['tipo']);
			$empty = empty($saved['descricao']);
			$this->assertFalse( $empty );

			// TEST #2
			$this->assertFalse( $this->obj->saveStructure( array() ) );

			// TEST #3
			$this->assertType('int', $this->obj->austNode() );
		}
		
		/*
		 * MÉTODOS CHEFES
		 *
		 * Servem para chamar todos os métodos menores
		 */

		// Função multifuncional, serve tanto para criar novas
		// estruturas como para editar antigas.
		function testCreateMainTable(){
			$this->obj->connection->query("DROP TABLE testunit");
			$this->obj->mainTable = 'testunit';
			$this->obj->austNode = '7777';

			$result = $this->obj->createMainTable();
			
			$saved = $this->obj->connection->hasTable('testunit');

			$this->obj->connection->query("DROP TABLE testunit");
			
			$this->assertTrue($result);
			$this->assertTrue($saved);
		}
		
		/**
		 * @depends testCreateMainTable
		 */
		function testAddField(){
			/*
			 * Testa a criação de cada tipo de campo
			 * 
			 */
			$this->obj->connection->query("DROP TABLE testunit");
			$this->obj->mainTable = 'testunit';
			$this->obj->austNode = '7777';
			$this->obj->createMainTable();
			
			// test STRING
				$params = array(
					'name' => 'Campo 1',
					'type' => 'string',
					'description' => ''
				);
				$result = $this->obj->addField($params);
				
				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN campo_1");
				
			
			// test TEXT
				$params = array(
					0 => array(
						'name' => 'campo1',
						'type' => 'text',
						'description' => ''
					),
				);
				$result = $this->obj->addField($params);
		
				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN 'campo_1'");

			// test PASSWORD
				$params = array(
					0 => array(
						'name' => 'campo1',
						'type' => 'pw',
						'description' => ''
					),
				);
				$result = $this->obj->addField($params);

				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN 'campo_1'");

			// test DATE
				$params = array(
					0 => array(
						'name' => 'campo1',
						'type' => 'date',
						'description' => ''
					),
				);
				$result = $this->obj->addField($params);

				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN 'campo_1'");

			// test FILE
				$params = array(
					0 => array(
						'name' => 'campo1',
						'type' => 'file',
						'description' => ''
					),
				);
				$result = $this->obj->addField($params);

				$this->assertTrue( $this->obj->connection->hasTable('testunit_arquivos') );
				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN 'campo_1'");

			// test RELACIONAL_UMPARAUM
				$params = array(
					0 => array(
						'name' => 'campo1',
						'type' => 'relational_onetoone',
						'description' => ''
					),
				);
				$result = $this->obj->addField($params);

				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN 'campo_1'");

			// test RELACIONAL_UMPARAMUITOS
				$params = array(
					0 => array(
						'name' => 'campo1',
						'type' => 'relational_onetomany',
						'description' => ''
					),
				);
				$result = $this->obj->addField($params);

				$this->assertTrue( $this->obj->connection->tableHasField('testunit', 'campo_1') );
				$this->assertArrayHasKey('0', $this->obj->connection->query("SELECT * FROM cadastros_conf WHERE tipo='campo' AND chave='campo_1' AND categorias_id='7777'") );
				$this->obj->connection->exec("DELETE FROM cadastros_conf WHERE categorias_id='7777'");
				$this->obj->connection->exec("ALTER TABLE testunit DROP COLUMN 'campo_1'");



			$this->obj->connection->query("DROP TABLE testunit");
			
		}
		
		/*

		// Executa todas as funções necessárias para instalar
	    function xtestCreateStructure(){

	    }
		*/
	
}
?>