<?php

if( empty($_GET['section']) )
    $_GET['section'] = 'conteudo';

/*
 * PRECISA CONFIRMAÇÃO?
 * 
 * Verifica se há a necessidade de aprovação de cadastro e se há alguém necessitando aprovação
 */
$precisa_aprovacao = $modulo->pegaConfig(Array('estrutura'=>$austNode, 'chave'=>'aprovacao'));
if($precisa_aprovacao['valor'] == '1'){
    $sql = "SELECT id FROM ".$modulo->LeTabelaDeDados($austNode)." WHERE approved=0 or approved IS NULL";
    $result = $modulo->connection->query($sql);
    if( count($result) > 0 ){
        //echo '<p>Há cadastros para serem aprovados.</p>';
    }
}
?>

<table class="listagem">
    <?php
    /*
     * Título dos campos
     */
    ?>
    <tr class="titulo">

        <?php
        $total_td = 0;
        if( !empty($resultado) ){
            $cabecalhos = $resultado[0];
            foreach($cabecalhos as $campo=>$valor){

                if( strpos($campo, 'des_') === 0 ){

                } else {
                    $total_td++;
                    ?>
                    <td bgcolor="#333333" class="<? echo $campo; ?>">
                        <?php
                        echo $campo;
                        ?>
                    </td>
                    <?php
                }

            }
            /*
             * Necessita aprovação?
             */
            ?>
            <td bgcolor="#333333" width="80" align="center">
                Opções
            </td>
            <?php
        }
        ?>
    </tr>
<?
/**
 * LISTAGEM DO CONTÉUDO EM SI
 */
if(count($resultado) > 0){
    foreach($resultado as $dados){
        /*
         * Valor dos campos
         */
        ?>
        <tr class="conteudo">
            <?php
            $total_td = 0;
            foreach($dados as $campo=>$valor) {
                //$campo = 'teste';
                if(strpos($campo, 'des_') === 0){
                    //echo $campo;
                } else {
                    ?>
                    <td>
                        <?php
                        $total_td++;
                        //echo $total_td;
                        if($total_td == 1){

                            if( $permissoes->canEdit($austNode) )
                                echo '<a href="adm_main.php?section='.$_GET['section'].'&action=edit&aust_node='.$austNode.'&w='.$dados["id"].'">';

                            echo $dados[$campo];
                            if( $permissoes->canEdit($austNode) )
                                echo '</a>';
                            if( $precisa_aprovacao['valor'] == '1'
                                 AND (
                                     $dados['des_approved'] == 0
                                     OR empty($dados['des_approved']) )
                                )
                            {
                                echo '<span style="font-size: 10px;"> (necessita aprovação)</span>';
                            }

                        } else {

                            /**
                             * Nas duas primeiras colunas, coloca um link
                             * para edição
                             */
                            if( $total_td <= 2 ){
                                if( $permissoes->canEdit($austNode) ){
                                    ?>
                                    <a href="adm_main.php?section=<?php echo $_GET['section'];?>&action=edit&aust_node=<?php echo $austNode;?>&w=<?php echo $dados["id"];?>">
                                    <?php
                                }
                            }

                            echo $dados[$campo];
                            if( $total_td <= 2 ){
                                if( $permissoes->canEdit($austNode) ){
                                    ?>
                                    </a>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </td>
               <?php
                }
            }
            ?>
            <td align="center">
                <?php
                if( $permissoes->canDelete($austNode) ){
                    ?>
                    <input type='checkbox' name='itens[]' value='<?php echo $dados['id'];?>'>
                    <?php
                }
                ?>
            </td>
        </tr>
    <?php
    } // fim do loop
} else {
    ?>
    <tr>
        <td colspan="<?php echo $total_td?>"><strong>Não há registros encontrados.</strong></td>
    </tr>
    <?php
}


?>
</table>