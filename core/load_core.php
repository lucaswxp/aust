<?php
/**
 * Carrega todas as configurações do CORE neste arquivo
 */

/**
 * Se não está definido o endereço deste arquivo até o root
 */
if(!defined('THIS_TO_BASEURL')){
    define('THIS_TO_BASEURL', '../');
}

/**
 * Carrega variáveis constantes contendo comportamentos e Paths
 */
include_once(THIS_TO_BASEURL."core/config/variables.php");

/**
 * Métodos usados pelo sistema
 */
include_once(THIS_TO_BASEURL.CORE_DIR."libs/functions/func.php");
include_once(THIS_TO_BASEURL.CORE_DIR."libs/functions/func_content.php");
include_once(THIS_TO_BASEURL.CORE_DIR."libs/functions/func_text_format.php");
include_once(THIS_TO_BASEURL.CORE_DIR."libs/functions/func_form_manipulation.php");
/**
 * DBSCHEMA
 * Carrega o $dbschema
 */
    require_once(THIS_TO_BASEURL.CORE_DIR.'config/installation/dbschema.php');
    $dbSchema = new dbSchema($dbSchema, $conexao);




?>
