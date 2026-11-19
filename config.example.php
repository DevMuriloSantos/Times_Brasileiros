<?php

/**
 * MODELO de configuração — NÃO é carregado pela aplicação.
 *
 * Para preparar um ambiente novo (local ou produção):
 *   1. Copie este arquivo para "config.php" (na mesma pasta).
 *   2. Troque os valores de exemplo abaixo pelos do seu ambiente.
 *   3. Nunca versione o config.php (ele está no .gitignore) e nunca coloque
 *      credenciais reais neste arquivo de exemplo.
 */

/** O nome do banco de dados */
const DB_NAME = "nome_do_banco";

/** Usuário do banco de dados MySQL */
const DB_USER = "usuario_do_banco";

/** Senha do banco de dados MySQL */
const DB_PASSWORD = "senha_do_banco";

/** nome do host do MySQL (local: normalmente "localhost"; em hospedagem, use o host informado no painel) */
const DB_HOST = "localhost";

/** caminho absoluto para a pasta do sistema **/
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/'); // não pode const aqui dentro

/** caminho no server para o sistema **/
if ( !defined('BASEURL') )
	define('BASEURL', '/Times_Brasileiros/'); // raiz do projeto. trocar para "/" quando hospedado

/** caminho do arquivo de banco de dados **/
if ( !defined('DBAPI') )
	define("DBAPI", ABSPATH . 'inc/database.php');

const HEADER_TEMPLATE = ABSPATH . "inc/header.php";
const FOOTER_TEMPLATE = ABSPATH . "inc/footer.php";
