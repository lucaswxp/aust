<?php

/*
 * FORMULÁRIO
 */
/*
 * Carrega configurações automáticas do DB
 */

    $params = array(
        "aust_node" => $_GET["aust_node"],
    );

    $moduloConfig = $modulo->loadModConf($params);


/*
 * Ajusta variáveis iniciais
 */
    $austNode = (!empty($_GET['aust_node'])) ? $_GET['aust_node'] : '';

/*
 * [Se novo conteúdo]
 */
    if($_GET['action'] == 'create'){
        $tagh2 = "Criar: ". $this->aust->leNomeDaEstrutura($_GET['aust_node']);
        $tagp = 'Crie uma nova galeria de fotos a seguir. Comece configurando as '.
                'informações básicas da galeria e depois suas fotos.';
        $dados = array('id' => '');
    }
/*
 * [Se modo edição]
 */
    else if($_GET['action'] == 'edit'){

        $tagh2 = "Editar: ". $this->aust->leNomeDaEstrutura($_GET['aust_node']);
        $tagp = 'Edite o conteúdo abaixo.';

    }
?>

<h2><?php echo $tagh2;?></h2>
<p><?php echo $tagp;?></p>



<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>?section=<?php echo $_GET["section"] ?>&action=save" enctype="multipart/form-data" >
<input type="hidden" name="metodo" value="<?php echo $_GET['action'];?>">
<?php if($_GET['action'] == 'create'){ ?>
    <input type="hidden" name="frmadddate" value="<?php echo date("Y-m-d H:i:s"); ?>">
    <input type="hidden" name="frmautor" value="<?php echo $_SESSION['loginid'];?>">
<?php } else { ?>

<?php }?>
<input type="hidden" name="w" value="<?php ifisset( $dados['id'] );?>">
<input type="hidden" name="aust_node" value="<?php echo $austNode; ?>">
<table border=0 cellpadding=0 cellspacing=0 class="form">

	<?php
	if( (
			!empty($_GET['related_master']) &&
			!empty($_GET['related_w'])
		)
		||
		(
			!empty($dados['ref_id'])
		)
	)
	{
		
		if( empty($_GET['related_master']) ||
			empty($_GET['related_w']) )
		{
			$master = Aust::getInstance()->getRelatedMasters($_GET['aust_node']);
			$master = reset(reset($master));
			$master = $master['master_id'];
			$relW = $dados['ref_id'];
			$relMaster = $master;
			
		} else {
			$relMaster = $_GET['related_master'];
			$relW = $_GET['related_w'];
		}
		
		?>
	    <tr>
	        <td valign="top" class="first"><label>Opções:</label></td>
	        <td class="second">
	            <a href="adm_main.php?section=conteudo&action=edit&aust_node=<?php echo $relMaster?>&w=<?php echo $relW ?>">
				Conteúdo principal
				</a>
				<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />
	        </td>
	    </tr>

		<?php
	}
	?>
    <?php
    /*
     * Mostra imagem preview
     */
    if( !empty($dados["bytes"]) && $dados["bytes"] > 0 ){
        ?>
        <tr>
            <td valign="top" class="first">Imagem atual:</td>
            <td class="second">
                <img src="core/libs/imageviewer/visualiza_foto.php?table=imagens&thumbs=yes&myid=<?php echo $dados["id"]; ?>&maxxsize=450&maxysize=400" />
                <p class="explanation">
                Imagem cadastrada atualmente. Para alterá-la, envie uma nova no formulário abaixo.
                </p>
            </td>
        </tr>
        <?php
    }
    ?>

    <tr>
        <td valign="top" class="first"><label>Título da galeria:</label></td>
        <td class="second">
            <INPUT TYPE='text' NAME='frmtitulo' class='text' value='<?php if( !empty($dados['titulo']) ) echo $dados['titulo'];?>' />
            <p class="explanation">
            Exemplo: Fotos do Segundo Encontro Nacional
            </p>
        </td>
    </tr>

    <?php
    /*
     * RESUMO
     */
    if( $modulo->getStructureConfig('resumo') ){
    ?>
    <tr>
        <td valign="top"><label>Resumo:</label></td>
        <td>
            <INPUT TYPE='text' NAME='frmresumo' class='text' value='<?php if( !empty($dados['resumo']) ) echo $dados['resumo'];?>' />
            <p class="explanation">

            </p>
        </td>
    </tr>
    <?php
    }
    ?>

    <?php
    /*
     * ORDEM
     */
    if( $modulo->getStructureConfig('ordenate') ){
    ?>
    <tr>
        <td valign="top"><label>Ordem:</label></td>
        <td>
            <select name="frmordem" class="select">
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '10'); ?> value="10">10</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '9'); ?> value="9">9</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '8'); ?> value="8">8</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '7'); ?> value="7">7</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '6'); ?> value="6">6</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '5'); ?> value="5">5</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '4'); ?> value="4">4</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '3'); ?> value="3">3</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '2'); ?> value="2">2</option>
                <option <?php if( !empty($dados['ordem']) ) makeselected($dados['ordem'], '1'); ?> value="1">1</option>
            </select>
            <p class="explanation">
                Selecione um número que representa a importância deste item.
                Quanto maior o número, maior a prioridade.
            </p>
        </td>
    </tr>
    <?php
    }
    ?>
    <?php
    /*
     * DESCRIÇÃO
     */
 	if( $modulo->getStructureConfig('descricao') ){
        ?>
        <tr>
            <td valign="top"><label>Descrição da galeria: </label>
            </td>
            <td>
                <textarea name="frmtexto" id="jseditor" rows="8" style="width: 400px"><?php if( !empty($dados['texto']) ) echo $dados['texto'];?></textarea>
            <br />
            </td>
        </tr>
        <?php
    }
    ?>

    <tr>
        <td colspan="2">
            <h3>Fotos da Galeria</h3>
        </td>
    </tr>
    <tr>
        <td colspan="2">
                <?php
                if( !empty($_GET["delete"]) AND $_GET["delete"] > 0 ){
                    $sql = "DELETE FROM galeria_fotos_imagens
                            WHERE id='".$_GET["delete"]."'";
                    if( $modulo->connection->exec($sql) ){
                        echo "<div style='color: green;'>";
                        echo "<p>Imagem excluída com sucesso</p>";
                        echo "</div>";
                    }

                }


                if( $_GET["action"] != "create" ){


                    $columns = 4;
                    echo '<table width="99%" style="margin-bottom: 15px;">';


                    $sql = "SELECT id, nome, texto FROM galeria_fotos_imagens
                            WHERE galeria_foto_id='".$w."'";
                    $query = $modulo->connection->query($sql, "ASSOC");
                    $c = 1;
                    foreach($query as $dados){
                        if($c == 1){
                            echo '<tr>';
                        }
                        ?>
                        <td valign="top" <?php echo $params['inline']?>>
                            <center>
                                <img src="core/libs/imageviewer/visualiza_foto.php?table=galeria_fotos_imagens&thumbs=yes&myid=<?php echo $dados["id"]; ?>&maxxsize=160&maxysize=100" />
                                <br clear="all" />
                                <?php
                                /*
                                 * Editar comentário
                                 */
                                $commentedImages = false;
                                if( !empty($moduloConfig["commented_images"]) ){
                                    if( $moduloConfig["commented_images"]["valor"] == "1" )
                                        $commentedImages = true;
                                }
                                if( $commentedImages ){
                                    ?>
                                    <div id="image_comment_text_<?php echo $dados["id"]; ?>">
                                        <?php echo $dados["texto"]; ?>
                                    </div>
                                    <a href="javascript: void(0);" id="image_comment_icon_<?php echo $dados["id"]; ?>" onclick="$('#image_comment_input_<?php echo $dados["id"]; ?>').show(); $('#comment_<?php echo $dados["id"]; ?>').focus(); $(this).hide()">
                                        <img src="core/user_interface/img/icons/comment_15x15.png" alt="Comentar" border="0" />
                                    </a>
                                    <div class="image_comment_input" style="display: none;" id="image_comment_input_<?php echo $dados["id"]; ?>">
                                        <textarea style="margin-top: 3px; width: 130px; display: block;" class="comment_textareas" id="comment_<?php echo $dados["id"]; ?>" name="<?php echo $dados["id"]; ?>" ><?php echo $dados["texto"]; ?></textarea>
                                        <input type="button" id="image_comment_button_<?php echo $dados["id"]; ?>" value="Salvar comentário" onclick="javascript: addCommentInImage(<?php echo $dados["id"]; ?>)" />
                                    </div>

                                    <?php
                                }
                                ?>
                                <a href="javascript: void();" onclick="if( confirm('Você tem certeza que deseja excluir esta imagem?') ) window.open('adm_main.php?section=<?php echo $_GET["section"]; ?>&action=<?php echo $_GET["action"]; ?>&aust_node=<?php echo $_GET["aust_node"]; ?>&w=<?php echo $w;?>&delete=<?php echo $dados["id"]; ?>','_top');">
                                    <img src="core/user_interface/img/icons/delete_15x15.png" alt="Excluir" border="0" />
                                </a>

                            </center>
                        </td>
                        <?php

                        if($c >= $columns){
                            echo '</tr>';
                            $c = 1;
                        } else {
                            $c++;
                        }
                    }
                    ?>
                    <script type="text/javascript">
                    $('.comment_textareas').bind('keypress', function(e) {
                            if(e.keyCode==13){
                                addCommentInImage( $(this).attr('name') );
                            }
                    });
                    </script>
                    <?php

                    // se ficou faltando TDs
                    if($c <= $columns AND $c > 0){
                        for($o = 0; $o < (($columns+1)-$c); $o++){ ?>
                            <td></td>
                            <?php
                        }
                        echo '</tr>';

                    }
                    echo "</table>";
                }
                ?>

	</tr>
    <tr>
        <td colspan="3">
            <h3>Inserir Imagens</h3>
        </td>
    </tr>
	<tr>
		<td colspan="3">
           <p>Para inserir novas imagens, selecione-as abaixo.</p>
		</td>
	</tr>
    <tr>
        <td valign="top" class="first"><label for="file">Arquivo:</label></td>
        <td class="second">
            <input type="file" id="file" name="frmarquivo[]" multiple="multiple" />
        </td>
    </tr>
	<?php if( $modulo->getStructureConfig('commented_images') ){ ?>
	    <tr>
	        <td valign="top" class="first"><label for="comment">Comentário:</label></td>
	        <td class="second">
	            <textarea rows="2" id="comment" name="images_comment"></textarea>
				<br />
				<p class="explanation">Todas as imagens selecionadas terão o mesmo comentário.</p>
	        </td>
	    </tr>
	<?php } ?>

    <tr>
        <td colspan="2" style="padding-top: 10px;"><center><INPUT TYPE="submit" VALUE="Enviar!" name="submit" class="submit"></center></td>
    </tr>
</table>

</form>

<br />
<p>
    <a href="adm_main.php?section=<?php echo $_GET['section']?>"><img src="<?php echo IMG_DIR?>layoutv1/voltar.gif" border="0" /></a>
</p>
