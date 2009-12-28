<?php
/**
 * Conteúdos Bootstrap
 *
 *
 * Este é o arquivo responsável por carregar os devidos formulários dos módulos.
 * Ele verifica cada ação requisitada e cada arquivo encontrado nos formulários
 * e mostra a página adequada.
 *
 * @package
 * @name Conteúdo
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.2
 * @since v0.1, 01/01/2009
 */
/*
 *  Se $_GET['action'] está setado, alguma ação foi requisitada
 */
if(!empty($_GET['action'])){

	/**
     * A seguir, o código de automação dos módulos (CRUD). São carregados os
     * formulários de cada módulo.
     *
     * $_POST|$aust_node é passado para sabermos qual estrutura deve ser
     * carregada. Aust_node é o ID da estrutura na tabela 'categorias' da
     * base de dados.
 	 */

    /**
     * AUST_NODE
     *
     * Ajusta $aust_node
     */
    if( !empty($_POST['aust_node']) ){
        $aust_node = $_POST['aust_node'];
    } else if( !empty($aust_node) ){
        $aust_node = $aust_node;
    } else if( !empty($_GET["aust_node"]) ){
        $aust_node = $_GET['aust_node'];
    }
    // @todo - Módulos devem procurar por $aust_node, não $_GET['aust_node']
    $_GET["aust_node"] = $aust_node;
    /**
     * Identifica qual é a pasta do módulo responsável por esta
     * estrutura/categoria
     */
    $modDir = $aust->LeModuloDaEstrutura($aust_node).'/';

    /**
     * Carrega arquivos principal do módulo requerido
     */
	include(MODULOS_DIR.$modDir.'index.php');

    /**
     * MVC?
     *
     * Se o módulo possui arquitetura MVC
     */
    if( !empty($modInfo['mvc']) AND $modInfo['mvc'] == true ){

        /**
         * ModController é o controller principal do módulo
         */
        include(MODULOS_DIR.$modDir.MOD_CONTROLLER);

        /*
         * JS DO MÓDULO
         *
         * Carrega Javascript de algum módulo se existir
         */
        if(!empty($aust_node)){
            //$modulo = $aust->LeModuloDaEstrutura($aust_node);
            if(is_file('modulos/'.$modDir.'js/jsloader.php')){
                $include_baseurl = WEBROOT.'modulos/'. substr($modDir, 0, strlen($modDir)-1); // necessário para o arquivo jsloader.php saber onde está fisicamente
                include_once('modulos/'.$modDir.'js/jsloader.php');
            }
        }


        /**
         * Prepara os argumentos para instanciar a classe e depois
         * chama o Controller que cuidará de toda a arquitetura MVC do módulo
         */
        
        $param = array(
            'conexao' => $conexao,
            'modulo' => $modulo,
            'administrador' => $administrador,
            'aust' => $aust,
            'action' => $_GET['action'],
            'modDir' => $modDir,
            'austNode' => $aust_node,
            'model' => $model,
        );
        $modController = new ModController($param);
    }
    /**
     * Não possui estrutura MVC
     */
    else {

        /**
         * Dependendo da ação requisitada, carrega o devido form
         */
        switch($_GET['action']){
            /**
             * NOVO conteúdo
             */
            case 'criar' :
                include( $modulos->loadInterface( $aust_node, 'new' ) );
                break;
            /**
             * EDITAR conteúdo
             */
            case 'editar' :
                /**
                 * O arquivo carregado 'inc/conteudo.inc/editar.inc.php' (arquivo do core)
                 * verfica se o módulo tem arquivo formulário específico para edição e o
                 * carrega.
                 */
                include( $modulos->loadInterface( $aust_node, 'edit' ) );
                break;
            /**
             * LISTAR conteúdo
             */
            case 'listar' :
                //include($aust->AustListar($aust_node).'/view/list_data.php'); // verifica se o módulo tem uma listagem padrão.
                include( $modulos->loadInterface( $aust_node, 'list' ) ); // verifica se o módulo tem uma listagem padrão.
                break;

            /**
             * AÇÕES ESPECIAIS
             */
            /**
             * ACTIONS
             *
             * Acima da listagem dos conteúdo, há um local com ações como excluir, por exemplo.
             * Estas são as ações especiais tratadas aqui.
             */
            case 'actions' :
                /**
                 * Inclui os actions
                 */
                include('conteudo.inc/actions.php');
                /**
                 * Após ter incluido os actions, lista o conteúdo normalmente.
                 */
                include( $modulos->loadInterface( $aust_node, 'list' ) );
                break;
            /**
             * GRAVAR
             *
             * Toma dados enviados via $_POST e carrega arquivo responsável pela gravação
             * de dados na base de dados.
             */
            case 'save' :
                /**
                 * 'gravar.php' retornou as seguintes variáveis:
                 *      - $status['classe']: contém a classe CSS referente ao resultado da operação (.sucesso|.insucesso)
                 *      - $status['mensagem']: contém a mensagem de sucesso ou erro.
                 *
                 * Ao final do código, escreve BoxMensagem
                 */
                include( $modulos->loadInterface( $aust_node, 'save' ) ); // abre o arquivo do módulo responsável pela gravação

                /**
                 * Após ter concluído a gravaçao, lista o conteúdo normalmente.
                 */
                include( $modulos->loadInterface( $aust_node, 'list' ) ); // após salvar informações, lista.
                break;
            /**
             * ELSE
             *
             * Se nenhuma ação corresponde, verifica se "$_GET['action'].'.php'"
             * existe e o carrega, senão dá erro.
             */
            default :
                $diretorio = 'modulos/'.$aust->LeModuloDaEstrutura($aust_node); // pega o endereço do diretório
                if(count(glob($diretorio.'/'.$_GET['action'].'.php')) == 1){
                    include($diretorio.'/'.$_GET['action'].'.php');
                } else {
                    echo '<h1>Ops... Erro nesta ação!</h1>';
                    echo '<p>O arquivo requisitado não existe. Entre em contato com o responsável pelo sistema.</p>';
                    echo '<p><a href="adm_main.php?section='.$_GET['section'].'"><img src="img/layoutv1/voltar.gif" border="0" /></a></p>';
                }

                break;

        }
    }
}

/**
 * Abre a página inicial da interface de conteúdos
 */
else {
    ?>


    <h2>Gerenciar conteúdo</h2>
    <p>
        Use esta tela para gerenciar seu conteúdo. Passe o mouse sobre o conteúdo para mais opções.
    </p>
    <?php
    $sites = $aust->getStructures();
    //pr($sites);
    ?>
    <?php // INICIO DO DIV PAINEL GERENCIAR  - É GLOBAL?>
    <div id="painel_gerenciar">

        <?php /* TABS */ ?>
        <div class="tabs_area">
            <!-- the tabs -->
            <ul class="tabs">
                <?php foreach( $sites as $site ): ?>
                <li><a href="#"><?php echo $site['Site']['name'] ?></a></li>
                <?php endforeach; ?>
            </ul>
                
        </div>
        <?php /* PANES */ ?>
        <div class="panes">
            <?php
            /*
             * LOOP POR CADA SITE
             */
            foreach( $sites as $site ): ?>
            <div>
                <table border="0" class="pane_listing">
                <?php if( count($site['Structures']) ): ?>
                    <tr class="header">
                        <td class="secoes">Conteúdos</td>
                        <td class="acao">Opções</td>
                        <td class="tipo">Tipo</td>
                        <td class="acesso">Último acesso por</td>
                    </tr>
                <?php else: ?>
                    <tr class="list">
                        <td class="sem_conteudo">Não há conteúdos nesta área.</td>
                    </tr>
                    </table>
                    <?php
                    continue;
                endif; ?>
                <?php
                /*
                 * LOOP POR CADA ESTRUTURA
                 */
                foreach( $site['Structures'] as $structure ):

                    /*
                     * Use o comando 'continue' para pular o resto do loop atual
                     */
                    unset($modInfo);
                    if(is_file('modulos/'.$structure['tipo'].'/'.MOD_CONFIG)){
                        /*
                         * Pega dados do módulo. $modInfo existe.
                         */
                        include('modulos/'.$structure['tipo'].'/'.MOD_CONFIG);

                        $type = $modInfo['nome'];
                    } else {
                        $type = $structure['tipo'];
                    }
                    ?>
                    <tr class="list">
                        <td class="title">
                            <span><?php echo $structure['nome'] ?></span>
                            <?php //tt('Teste') ?>
                        </td>
                        <td class="options">
                            <ul>
                            <?php
                            $options = (is_array($modInfo['opcoes'])) ? $modInfo['opcoes'] : Array();
                            foreach ($options as $chave=>$valor) {
                                echo '<li><a href="adm_main.php?section='.$_GET['section'].'&action='.$chave.'&aust_node='.$structure['id'].'">'.$valor.'</a></li>';
                            }
                            ?>
                            </ul>
                        </td>
                        <td class="tipo">
                            <?php
                            /*
                             * TIPO
                             */
                            echo $type;
                            ?>
                        </td>
                        <td class="acesso">
                            Alexandre de Oliveira
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="footer">
                        <td colspan="4"></td>
                    </tr>
                    </table>


                </div>
            <?php endforeach; ?>
        </div>

    </div><?php // FIM DO DIV PAINEL GERENCIAR ?>

    <br clear="all" />
	<div class="action_options">
        <?php
        //pr($aust->getStructures());
        //include(INC_DIR.'conteudo.inc/user_menu.php');
        ?>

	</div>
<?php } ?>