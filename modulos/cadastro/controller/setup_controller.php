<?php
/**
 * Controller principal deste módulo
 *
 * @package ModController
 * @name nome
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.6 06/07/2009
 */

class SetupController extends ModsSetup
{

    function beforeFilter(){
        $_SESSION['exPOST'] = $_POST;
        $this->set('exPOST', $_SESSION['exPOST']);
    }

    /**
     * setuppronto()
     *
     * Cria cadastro
     *
     * Campos especificados, agora começa a criar tabelas e configurações.
     *
     * @global array $aust_charset Contém o charset global do sistema
     */
    function setuppronto(){

        //pr($_POST);

        global $aust_charset;

        /**
         * Parâmetros para gravar uma nova estrutura no DB.
         */
        $params = array(
            'nome' => $_POST['nome'],
            'categoriaChefe' => $_POST['categoria_chefe'],
            'estrutura' => 'estrutura',
            'moduloPasta' => $_POST['modulo'],
            'autor' => $this->administrador->LeRegistro('id')
        );
        /**
         * CRIA ESTRUTURA (Aust)
         *
         * Verifica se consegue gravar a estrutura (provavelmente na tabela
         * 'categorias').
         */
        if( $status_insert = $this->aust->gravaEstrutura( $params ) ){
            
            $status_setup[] = "Categoria criada com sucesso.";

            /**
             * Cria string com o charset geral do projeto
             */
            $cur_charset = 'CHARACTER SET '.$aust_charset['db'].' COLLATE '.$aust_charset['db_collate'];

            /**
             * Trata o nome da tabela para poder criar no db
             */
            $tabela = RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome'])));

            /**
             * TRATAMENTO DE CAMPOS
             *
             * Gera o SQL dos campos para salvar em 'cadastros_conf'
             */
            /*
             * Loop por cada campo para geração de SQL para salvar suas
             * configurações em 'cadastros_conf'.
             */
            $ordem = 0; // A ordem do campo
            for($i = 0; $i < count($_POST['campo']); $i++) {
                $ordem++;

                $valor = ''; // Por segurança
                $_POST['campo_descricao'][$i] = addslashes( $_POST['campo_descricao'][$i] );

                /**
                 * Verifica se o atual campo analisado está especificado.
                 *
                 * Se...
                 *      - sim: faz os devidos tratamentos;
                 *      - não: não faz nada.
                 */
                if( !empty($_POST['campo'][$i]) ){

                    /**
                     * !!!ATENÇÃO!!!
                     *
                     * Altere condições abaixo para modificações do $_POST['campo_tipo']
                     */

                    /**
                     * TIPAGEM FÍSICA DOS CAMPOS
                     *
                     * Define os tipos físicos dos dados.
                     *
                     * A tabela criada para o cadastro terá campos especificados
                     * na instalação do mesmo, e estes campos devem receber um
                     * formato adequado. Se é campo texto, será varchar, e assim
                     * por diante.
                     */
                    /**
                     * Tipo Password
                     * Se o tipo de campo for pw, $campo_tipo=varchar(180)
                     */

                    $tipagemFisicaDosCampos = array(
                        "pw" => "varchar(180)",
                        "arquivo" => "varchar(240)",
                        "relacional_umparaum" => array(
                            "tipo" => "int",
                        )
                    );

                    /**
                     * Se o tipo físico foi configurado anteriormente, salva de
                     * acordo, senão o tipo é aquele especificado no formulário
                     * de configuração.
                     */
                    if ( array_key_exists( $_POST['campo_tipo'][$i], $tipagemFisicaDosCampos ) ){
                        if( is_array( $tipagemFisicaDosCampos[ $_POST['campo_tipo'][$i] ] ) ){
                            $campo_tipo = $tipagemFisicaDosCampos[ $_POST['campo_tipo'][$i] ]["tipo"];
                        } else {
                            $campo_tipo = $tipagemFisicaDosCampos[ $_POST['campo_tipo'][$i] ];
                        }
                    } else {
                        $campo_tipo = $_POST['campo_tipo'][$i];
                    }

                    /*
                    if($_POST['campo_tipo'][$i] == 'pw'){
                        $campo_tipo = 'varchar(180)';
                    }
                    /**
                     * Se o tipo de campo for arquivo, $campo_tipo=varchar(240)
                     *
                    elseif($_POST['campo_tipo'][$i] == 'arquivo'){
                        $campo_tipo = 'varchar(240)';
                    } elseif($_POST['campo_tipo'][$i] == 'relacional_umparaum'){
                        $campo_tipo = 'int';
                    } else {
                        $campo_tipo = $_POST['campo_tipo'][$i];
                    }
                     * 
                     */

                    /**
                     * Retira acentuação e caracteres indesejados para criar
                     * campos nas tabelas
                     */
                    $valor = RetiraAcentos(strtolower(str_replace(' ', '_', $_POST['campo'][$i]))).' '. $campo_tipo;

                    /**
                     * Se for data ou relacional, não tem charset
                     */
                    if($campo_tipo <> 'date' AND $campo_tipo <> 'int')
                        $valor .= ' '. $cur_charset.' NOT NULL';

                    /**
                     * Descrição: ajusta comentário do campo
                     */
                    if(!empty($_POST['campo_descricao'][$i]))
                        $valor .=  ' COMMENT \''. $_POST['campo_descricao'][$i] .'\'';

                    /**
                     * Ajusta vírgulas (se for o primeiro campo, não tem vírgula)
                     */
                    if($i == 0){
                        $campos = $valor;
                    } else {
                        $campos .= ', '.$valor;
                    }
                    
                    $campo = RetiraAcentos(strtolower(str_replace(' ', '_', $_POST['campo'][$i])));

                    /**
                     * CONFIGURAÇÃO DE CAMPOS
                     *
                     * Analisa campo por campo e grava informações diferenciadas
                     * sobre campos especiais (exemplo: password, arquivos)
                     */
                    /**
                     * Password. tipo=campopw
                     */
                    if($_POST['campo_tipo'][$i] == 'pw'){
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1,'password',".$ordem.")";
                    }
                    /**
                     * Arquivos
                     */
                    elseif($_POST['campo_tipo'][$i] == 'arquivo'){
                        $cria_tabela_arquivos = TRUE;
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1,'arquivo',".$ordem.")";
                    }
                    /**
                     * Campo relacional um-para-um
                     */
                    elseif($_POST['campo_tipo'][$i] == 'relacional_umparaum'){
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem,ref_tabela,ref_campo)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1, 'relacional_umparaum',".$ordem.", '".$_POST['relacionado_tabela_'.($i+1)]."', '".$_POST['relacionado_campo_'.($i+1)]."')";
                    }
                    /**
                     * Campo normal, grava suas informações
                     */
                    else {
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1,'string',".$ordem.")";
                    }
                }
            }
            //pr($sql_campos);
            /**
             * SQL
             *
             * Cria tabela
             */
            $sql = 'CREATE TABLE '.$tabela.'(
                        id int auto_increment,
                        '.$campos.',
                        bloqueado varchar(120) '.$cur_charset.',
                        aprovado int,
                        adddate datetime,
                        PRIMARY KEY (id), UNIQUE id (id)

                    ) '.$cur_charset;
            //echo $sql;

            /**
             * Se o tipo de campo é arquivo, cria outra tabela para os arquivos
             */
            if( !empty( $cria_tabela_arquivos )
                AND $cria_tabela_arquivos == TRUE ){
                $sql_arquivos =
                    "CREATE TABLE ".$tabela."_arquivos(
                    id int auto_increment,
                    titulo varchar(120) {$cur_charset},
                    descricao text {$cur_charset},
                    local varchar(80) {$cur_charset},
                    url text {$cur_charset},
                    arquivo_nome varchar(250) {$cur_charset},
                    arquivo_tipo varchar(250) {$cur_charset},
                    arquivo_tamanho varchar(250) {$cur_charset},
                    arquivo_extensao varchar(10) {$cur_charset},
                    tipo varchar(80) {$cur_charset},
                    referencia varchar(120) {$cur_charset},
                    categorias_id int,
                    adddate datetime,
                    autor int,
                    PRIMARY KEY (id),
                    UNIQUE id (id)
                ) ".$cur_charset;
            }
            //echo '<br><br><br>'.$sql_arquivos;

            /**
             * TABELA FÍSICA
             */
            /*
             * Executa QUERY na base de dados
             *
             * Se retornar sucesso, salva configurações gerais sobre o cadastro na tabela cadastros_conf
             */
            //pr( addslashes( $sql) );
            if( $this->conexao->exec( $sql, 'CREATE_TABLE') ){
                $status_setup[] = "Tabela '".$tabela."' criada com sucesso!";

                /**
                 * Se há SQL para criação de tabela para arquivos
                 */
                if( !empty($sql_arquivos) AND $cria_tabela_arquivos == TRUE ){
                    if($this->conexao->exec($sql_arquivos, 'CREATE_TABLE')){
                        $status_setup[] = 'Criação da tabela \''.$tabela.'_arquivos\' efetuada com sucesso!';
                    } else {
                        $status_setup[] = 'Erro ao criar tabela \''.$tabela.'_arquivos\'.';
                    }

                    $sql_conf_arquivos =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('estrutura','tabela_arquivos','".$tabela."_arquivos',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_arquivos)){
                        $status_setup[] = 'Configuração da estrutura \''.$tabela.'_arquivos\' salva com sucesso!';
                    } else {
                        $status_setup[] = 'Erro ao criar tabela \''.$tabela.'_arquivos\'.';
                    }


                }

                /*
                 * CONFIGURAÇÃO
                 *
                 * Aqui, guardamos as principais configurações de cadastro
                 */
                // salva configuração sobre aprovação quanto ao cadastro
                    $sql_conf_2 =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,nome,especie,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('config','aprovacao','".$_SESSION['exPOST']['aprovacao']."','Aprovação','bool',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_2)){
                        $status_setup[] = 'Configuração de aprovação salva com sucesso!';
                    } else {
                        $status_setup[] = 'Configuração de aprovação não foi salva com sucesso.';
                    }

                // DESCRIÇÃO: salva o parágrafo introdutório ao formulário
                    $sql_conf_2 =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,nome,especie,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('config','descricao','".$_SESSION['exPOST']['descricao']."','Descrição','blob',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_2)){
                        $status_setup[] = 'Configuração de aprovação salva com sucesso!';
                    } else {
                        $status_setup[] = 'Configuração de aprovação não foi salva com sucesso.';
                    }

                // salva configuração sobre pré-senha para o cadastro
                    $sql_conf_2 =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,nome,especie,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('config','pre_senha','".$_SESSION['exPOST']['pre_senha']."','Pré-senha','string',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_2)){
                        $status_setup[] = 'Configuração de pré-senha salva com sucesso!';
                    } else {
                        $status_setup[] = 'Configuração de pré-senha não foi salva com sucesso.';
                    }




                // configurações sobre a estrutura, como tabela a ser usada
                $sql_conf =
                            "INSERT INTO
                                cadastros_conf
                                (tipo,chave,valor,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                            VALUES
                                ('estrutura','tabela','".RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome'])))."',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                            ";
                if($this->conexao->exec($sql_conf)){
                    $status_setup[] = 'Configuração da estrutura \''.RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome']))).'\' salva com sucesso!';

                    // número de erros encontrados
                    $status_campos = 0;
                    foreach ($sql_campos as $valor) {
                        if(!$this->conexao->exec($valor)){
                            $status_campos++;
                        }
                    }
                    if($status_campos == 0){
                        $status_setup[] = 'Campos criados com sucesso!';
                    } else {
                        $status_setup[] = 'Erro ao criar campos';
                    }
                } else {
                    $status_setup[] = 'Erro ao salvar configuração da estrutura \''.RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome']))).'\'.';
                }
            } else {
                $status_setup[] = 'Erro ao criar tabela \''.RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome']))).'\'.';
            }

        }

        echo '<ul>';
        foreach ($status_setup as $valor){
            echo '<li>'.$valor.'</li>';
        }
        echo '</ul>';


        $this->autoRender = false;
    }

}
?>