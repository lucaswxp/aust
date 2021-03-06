<?php
/*
 * Somente webmasters tem acesso a esta página
 */

if(User::getInstance()->LeRegistro('group') == 'Webmaster'):
?>
	<span class="root_user_only">Apenas desenvolvedores acessam esta tela.</span>
<?php
/*
 * MIGRATIONS
 *
 * Verificações de Migrations de módulos
 */

    /*
     * Migration Status
     */
    $migrationsStatus = MigrationsMods::getInstance()->status();

    $modulesStatus = ModulesManager::getInstance()->getModuleInformation( array_keys($migrationsStatus) );

    /*
     * Module's JS
     */
    if(!empty($_GET['aust_node'])){
        $modulo = Aust::getInstance()->structureModule($_GET['aust_node']);
        if(is_file(MODULES_DIR.$modulo.'/js/jsloader.php')){
            $include_baseurl = MODULES_DIR.$modulo;
            include_once(MODULES_DIR.$modulo.'/js/jsloader.php');
        }
    } elseif($_GET['action'] == 'configurar_modulo' AND !empty($_GET['modulo'])){
        if(is_file($_GET['modulo'].'/js/jsloader.php')){
            $include_baseurl = $_GET['modulo'];
            include_once($_GET['modulo'].'/js/jsloader.php');
        }
    }

    /*
     * Configure a structure
     */
    if($_GET['action'] == 'configurar'){
        $diretorio = MODULES_DIR.Aust::getInstance()->structureModule($_GET['aust_node']); // pega o endereço do diretório
        foreach (glob($diretorio."*", GLOB_ONLYDIR) as $pastas) {
            if(is_file($pastas.'/configurar_estrutura.php')){
				$module = ModulesManager::getInstance()->modelInstance($_GET["aust_node"]);
				include($pastas.'/configurar_estrutura.php');
            }
        }
    }
    /*
     * Configure a module
     */
    else if($_GET['action'] == 'configurar_modulo'){
        $pastas = $_GET['modulo'];
        if(is_file($pastas.'/configurar_modulo.php')){
            include($pastas.'/configurar_modulo.php');
        }
    }

    /*
     * INSTALAR/CRIAR ESTRUTURA COM SETUP PRÓPRIO
     *
     * Se instalar uma estrutura a partir de um módulo com setup próprio, faz
     * includes neste arquivo para configuração.
     */
    elseif( !empty($_POST['inserirestrutura'])
            AND (
                ( is_file( MODULES_DIR.$_POST['modulo'].'/'.MOD_SETUP_CONTROLLER ) )
                OR ( is_file( MODULES_DIR.$_POST['modulo'].'/setup.php' ) )
            )
    ){
		# on the controlller
    }

    /*
     * NENHUMA DAS OPÇÕES ACIMA, CARREGAR A PÁGINA NORMALMENTE
     *
     * Carrega toda a interface normalmente
     */
    else {
        ?>
        <h2>Configuração: Módulos e Estruturas</h2>
        <?php
        if( !empty($status) AND is_array($status)){
            EscreveBoxMensagem($status);
        }
        ?>

        <div class="widget_group">
            <?php
            /*
             * LISTAGEM DAS ESTRUTURAS CRIADAS
             */
            ?>
            <div class="widget">
                <div class="titulo">
                    <h3>Estruturas instaladas</h3>
                </div>
                <div class="content">
                    <p>Abaixo, as estruturas instaladas.</p>
                    <ul>
                    <?php
					foreach( $sites as $site ){
						
						foreach( $site['Structures'] as $structure ){
							?>
							<li>
								<strong><?php echo $structure["name"] ?></strong>
								(módulo <?php echo $structure["type"] ?>)
								<a href="adm_main.php?section=<?php echo CONTROL_PANEL_DISPATCHER ?>&aust_node=<?php echo $structure["id"] ?>&action=structure_configuration">Configurar</a>
							</li>
							<?php
						}
					}

                    ?>
                    </ul>
                </div>
                <div class="footer"></div>
            </div>


            <?php
            /*
             * FORM INSTALAR NOVAS ESTRUTURAS
             */
            ?>
            <div class="widget">
                <div class="titulo">
                    <h3>Instalar Estrutura</h3>
                </div>
                <div class="content">
                    <p>
                        Selecione abaixo o site, o módulo adequado e o nome da estrutura (ex.: Notícias, Artigos, Arquivos).
                    </p>
                    <form action="adm_main.php?section=<?php echo $_GET['section'];?>" method="post" class="simples pequeno">
                        <input type="hidden" value="1" name="publico" />
                        <div class="campo">
                            <label>Categoria-chefe: </label>
                                <select name="categoria_chefe">
                                    <?php
                                    Aust::getInstance()->getSite(Array('id', 'name'), '<option value="&%id">&%name</option>', '', '');
                                    ?>
                                </select>
                        </div>
                        <br />
                        <div class="campo">
                            <label>Módulo: </label>
                                <?php
                                $modulesList = ModulesManager::getInstance()->LeModulos();
                                ?>
                                <select name="modulo">
                                    <?php
									if( !empty($modulesList) ){
	                                    foreach( $modulesList as $moduloDB ){

	                                        ?>
	                                        <option value="<?php echo $moduloDB["value"] ?>">
	                                            <?php echo $moduloDB["name"] ?>
	                                        </option>
	                                        <?php
	                                    }
										
									}
                                    //unset($moduloDB);
                                    ?>
                                </select>
                        </div>
                        <br />
                        <div class="campo">
                            <label>Nome da estrutura:</label>
                            <div class="input">
                                <input type="text" name="nome" class="input" />
                                <p class="explanation">Ex.: Notícias, Artigos</p>
                            </div>
                        </div>
                        <div class="campo">
                            <input type="submit" name="inserirestrutura" value="Enviar!" class="submit" />
                        </div>

                    </form>
                </div>
                <div class="footer"></div>
            </div>
        </div>



        <div class="widget_group">

            <?php
            /*
             * INSTALAÇÃO DE MÓDULOS
             */
            ?>
            <div class="widget">
                <div class="titulo">
                    <h3>Módulos disponíveis</h3>
                </div>
                <div class="content">

                    <div style="margin-bottom: 10px;">
                    <?php
                    foreach( $modulesStatus as $modulo) {
                        $path = $modulo['path'];
                        ?>
                        <div>

                        <strong>
                        <?php
                        /*
                         * Tem configurador?
                         */
                        if(is_file(MODULES_DIR.$modulo['path'].'/configurar_modulo.php')){
                            echo '<a href="adm_main.php?section=control_panel&action=configurar_modulo&modulo='.$modulo['path'].'" style="text-decoration: none;">'.$modulo['config']['name'].'</a>';
                        } else {
                            echo $modulo['config']['name'];
                        }
                        ?>
                        </strong>
                        <br />
                        <?php echo $modulo['config']['description'];
                        /*
                         * Totalmente Atualizado.
                         */
                        if( MigrationsMods::getInstance()->isActualVersion($path)
                            AND ModulesManager::getInstance()->verificaInstalacaoRegistro(array("pasta"=>$path)) )
                        {
                            echo '<br /><span class="green">Instalado</span><br />';
                        } elseif( MigrationsMods::getInstance()->isActualVersion($path)
                            AND !ModulesManager::getInstance()->verificaInstalacaoRegistro(array("pasta"=>$path)) )
                        {
                            echo '<div style="color: orange;">Migration atualizado, mas não há registro do módulo no DB.<br />';
                            echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&instalar_modulo='.$path.'">Tentar registrar agora</a></div>';
                        }
                        /*
                         * Não atualizado,
                         * contém alguma versão no DB.
                         */
                        elseif( MigrationsMods::getInstance()->hasSomeVersion($path)
                                AND ModulesManager::getInstance()->verificaInstalacaoRegistro(array("pasta"=>$path)) )
                        {
                            echo '<div style="color: orange;">Tabela instalada, mas requer atualização.<br />';
                            echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&instalar_modulo='.$path.'">Rodar Migration</a></div>';
                        } elseif( MigrationsMods::getInstance()->hasSomeVersion($path)
                                AND !ModulesManager::getInstance()->verificaInstalacaoRegistro(array("pasta"=>$path)) )
                        {
                            echo '<div style="color: orange;">Tabela instalada, mas requer atualização e registro do módulo no DB.<br />';
                            echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&instalar_modulo='.$path.'">Rodar Migration</a></div>';
                        } else {
                            echo '<br /><span class="red">Não Instalado,</span> ';
                            echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&instalar_modulo='.$path.'">instalar agora</a><br />';
                        }
                        /*
                        if( $module->verificaInstalacaoTabelas()
                            AND $module->verificaInstalacaoRegistro(array("pasta"=>$pastas)) )
                        {
                            $conteudo.= '<div style="color: green;">Instalado</div>';

                        } else if( $module->verificaInstalacaoTabelas() ){
                            $conteudo.= '<div style="color: orange;">Tabela instalada, registro no DB não.<br />';
                            $conteudo.= '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&instalar_modulo='.$pastas.'">Tentar instalar</a></div>';
                        } else if( $module->verificaInstalacaoRegistro(array("pasta"=>$pastas)) ){
                            $conteudo.= '<div style="color: orange;">Tabela não instalada, registro no DB sim.</div>';
                        } else {
                            $conteudo.= '<div style="color: red;">Não Instalado, ';
                            $conteudo.= '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&instalar_modulo='.$pastas.'">clique para instalar</a></div>';
                        }
                         *
                         */
                        ?>

                        <br />
                        </div>
                        <?php
                    }
                    ?>
                    </div>

                </div>
                <div class="footer"></div>
            </div>

        </div>


        <?php

    }

endif;

?>