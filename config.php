<?php

/** O nome do banco de dados*/
// define('DB_NAME', 'wda_crud');
const DB_NAME = "banco";

/** Usuário do banco de dados MySQL */
const DB_USER = "root";

/** Senha do banco de dados MySQL */
const DB_PASSWORD =  "";

/** nome do host do MySQL */
const DB_HOST = "localhost";

/** caminho absoluto para a pasta do sistema **/
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/'); // não pode const aqui dentro
	
/** caminho no server para o sistema **/
if ( !defined('BASEURL') )
	define('BASEURL', '/Times_Brasileiros/'); //raiz do projeto. trocar para "/" quando hospedado
	
/** caminho do arquivo de banco de dados **/
if ( !defined('DBAPI') )
	define("DBAPI", ABSPATH . 'inc/database.php');

const HEADER_TEMPLATE = ABSPATH . "inc/header.php";
const FOOTER_TEMPLATE = ABSPATH . "inc/footer.php";