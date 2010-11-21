<?php
/**
 * Variável de conexão com banco de dados
 *
 * Contém $dbConn
 */
//require_once "config/database.php";
class DATABASE_CONFIG
{
    static $dbConn = array(
        'server' => '127.0.0.1',
        'database' => 'aust',
        'username' => 'root',
        'password' => '', 
        'encoding' => 'utf8',
		// 'port' => '8888', // if you ever need special port access
    );
}

?>