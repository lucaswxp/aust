<?php
/*
 *
 * Categorias
 *
 */
//    pr($_POST);
//    pr($_GET);
//echo $_GET['lib'];

$urlAN = $_POST['urlAN'];
$givenAN = $_POST['givenAN'];
$catName = $_POST['catName'];

if( $_GET['lib'] == 'new_category'
    AND $urlAN == $givenAN )
{

    $params = array(
        'father' => $givenAN,
        'catName' => $catName,
        'autor' => $administrador->getId(),
    );

    echo $aust->newCategory($params);


}

?>