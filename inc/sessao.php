<?php

/**
 * Sessão e mensagens temporárias (flash).
 *
 * Deve ser carregado ANTES de qualquer saída HTML (ver times/function.php),
 * pois session_start() envia o cookie de sessão nos cabeçalhos HTTP.
 */

/**
 * Inicia a sessão uma única vez, com cookie configurado para HTTP ou HTTPS.
 */
function iniciar_sessao()
{
	if (session_status() !== PHP_SESSION_NONE) {
		return; // já iniciada: evita session_start() duplicado
	}

	$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| (($_SERVER['SERVER_PORT'] ?? '') == 443)
		|| (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

	session_name('crudsoccer'); // evita misturar a sessão com outros projetos no mesmo host
	ini_set('session.use_only_cookies', '1');
	ini_set('session.use_strict_mode', '1');
	ini_set('session.cookie_httponly', '1');
	ini_set('session.cookie_samesite', 'Lax');
	ini_set('session.cookie_secure', $https ? '1' : '0'); // 'secure' só quando a requisição é HTTPS

	session_start();
}

/**
 * Registra uma mensagem temporária. $tipo = classe de alerta do Bootstrap
 * (success, danger, info...). Mantém as chaves $_SESSION['message'] e ['type'],
 * as mesmas gravadas por inc/database.php.
 */
function definir_msg($texto, $tipo = 'success')
{
	$_SESSION['message'] = $texto;
	$_SESSION['type'] = $tipo;
}

/**
 * Retorna a mensagem pendente (['texto' => ..., 'tipo' => ...]) e a REMOVE da
 * sessão, para ser exibida uma única vez. Retorna null se não houver mensagem.
 */
function obter_msg()
{
	$msg = null;

	if (!empty($_SESSION['message'])) {
		$msg = [
			'texto' => $_SESSION['message'],
			'tipo' => $_SESSION['type'] ?? 'info',
		];
	}

	unset($_SESSION['message'], $_SESSION['type']);

	return $msg;
}

/**
 * Escape de saída (XSS). Usar em TODO valor dinâmico (banco, formulário, URL, mensagens)
 * impresso em HTML, inclusive dentro de atributos (value, src, data-*, class...).
 * Não usar em dados antes de gravar no banco: o banco guarda o texto original.
 * Valores que não sejam texto/número (array, objeto) viram string vazia.
 */
function e($valor)
{
	if (!is_scalar($valor)) {
		return '';
	}

	return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Valor para usar DENTRO de um <script> (já vem com aspas): json_encode com as flags
 * que impedem fechar a tag (</script>) ou sair da string. Ex.: var x = <?= js_str($v) ?>;
 */
function js_str($valor)
{
	$json = json_encode(is_scalar($valor) ? (string) $valor : '',
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR);

	return ($json === false || $json === 'null') ? '""' : $json;
}

/**
 * Token CSRF da sessão: criado uma vez com random_bytes() e reutilizado durante a sessão.
 * Se não houver fonte criptográfica (ou sessão ativa), interrompe com erro 500 em vez de
 * usar um gerador fraco. Nunca enviar o token pela URL.
 */
function csrf_token()
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		error_log('CSRF: sessão não iniciada ao gerar o token.');
		encerrar_http(500, 'Erro interno. Tente novamente mais tarde.');
	}

	if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
		if (!function_exists('random_bytes')) {
			error_log('CSRF: random_bytes() indisponível neste PHP; proteção não pode ser ativada.');
			encerrar_http(500, 'Erro interno. Tente novamente mais tarde.');
		}

		try {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		} catch (Exception $erro) {
			error_log('CSRF: falha ao gerar bytes aleatórios: ' . $erro->getMessage());
			encerrar_http(500, 'Erro interno. Tente novamente mais tarde.');
		}
	}

	return $_SESSION['csrf_token'];
}

/**
 * Campo oculto com o token, para colocar dentro de todo <form method="post"> que grava dados.
 */
function csrf_campo()
{
	return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Compara o token recebido com o da sessão (hash_equals). Falso se faltar sessão/token,
 * se o recebido não for texto ou se for diferente.
 */
function csrf_valido($recebido)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		return false;
	}

	$esperado = $_SESSION['csrf_token'] ?? null;

	return is_string($esperado) && $esperado !== ''
		&& is_string($recebido) && $recebido !== ''
		&& hash_equals($esperado, $recebido);
}

/**
 * Exige POST + token válido (campo "csrf_token" do formulário ou cabeçalho X-CSRF-Token do fetch).
 * Chamar ANTES de qualquer upload ou acesso ao banco. Se falhar, responde 403 e encerra.
 */
function csrf_exigir()
{
	$recebido = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

	if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_valido($recebido)) {
		error_log('CSRF: requisição rejeitada em ' . basename($_SERVER['SCRIPT_NAME'] ?? ''));
		encerrar_http(403, 'Requisição inválida ou sessão expirada. Volte à página anterior, recarregue-a e tente novamente.');
	}
}

/**
 * Descarta qualquer saída pendente (function.php usa ob_start), responde com o código HTTP
 * e uma mensagem curta em texto puro, e encerra.
 */
function encerrar_http($codigo, $texto)
{
	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	http_response_code($codigo);
	header('Content-Type: text/plain; charset=UTF-8');
	echo $texto;
	exit;
}
