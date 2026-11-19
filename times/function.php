<?php
ob_start(); //output buffer aberto, para não dar erro de header location

include(__DIR__ . '/../config.php');

require_once ABSPATH . 'inc/sessao.php';
iniciar_sessao(); // antes de qualquer saída: as páginas incluem este arquivo no topo

include(DBAPI);

$times = null;
$time = null;

/**
 *  Formatar as datas
 */
function formatData($data, $formato)
{
	$dt = new Datetime($data, new DateTimeZone("America/Sao_Paulo"));// "-0300"
	return $dt->format($formato);
}

/**
 *  Campos do formulário de times que o usuário pode enviar. Qualquer outro campo de
 *  $_POST['time'] (id, dataCadastro, foto, colunas inexistentes...) é ignorado:
 *  os campos internos são definidos somente pelo servidor.
 */
function campos_time($dados)
{
	$time = [];

	if (!is_array($dados)) {
		return $time;
	}

	foreach (['nome', 'dataFundacao', 'estado', 'divisao'] as $campo) {
		if (isset($dados[$campo]) && is_scalar($dados[$campo])) {
			$time[$campo] = $dados[$campo];
		}
	}

	return $time;
}

/**
 *  Nome do arquivo da miniatura pronto para usar em src="../assets/img/...":
 *  vazio vira o placeholder e o restante é codificado como URL (um nome com ?, #, % ou /
 *  não consegue mudar o caminho). Ao imprimir, use e(): src="../assets/img/<?= e(foto_time($f)) ?>"
 */
function foto_time($foto)
{
	if (!is_scalar($foto) || (string) $foto === '') {
		$foto = 'placeholder-time.png';
	}

	return rawurlencode((string) $foto);
}

/**
 *  Listagem de Times
 */
function index($filtro = null)
{
	global $times;
	$times = find_all("tabela_time", $filtro);
}

/**
 *  Visualização de um Cliente
 */
function view($id = null)
{
	global $time;
	$time = find('tabela_time', null, $id);
}

/**
 *  Cadastro de Times
 */
function add()
{
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		csrf_exigir(); // antes de qualquer upload ou gravação
	}

	if (!empty($_POST['time']) && is_array($_POST['time'])) {

		$time = campos_time($_POST['time']);

		if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] !== UPLOAD_ERR_NO_FILE) {
			// Se houver erro no upload
			if ($_FILES["imagem"]["error"] !== UPLOAD_ERR_OK) {
				throw new Exception("Nao foi possivel receber a imagem. Tente novamente!");
			}

			// Se não houver erro, faz o upload
			$target_dir = "../assets/img/";
			$target_file = $target_dir . basename($_FILES["imagem"]["name"]);
			$arquivo = basename($_FILES["imagem"]["name"]);

			if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
				echo $_FILES["imagem"]["tmp_name"];
				echo $target_file;
				throw new Exception("Não foi possivel fazer o upload da imagem. Tente novamente!");
			}
		} else {
			$arquivo = "placeholder-time.png"; // Valor padrão se nenhuma imagem for enviada
		}

		$time['dataCadastro'] = date('Y-m-d H:i:s');
		$time['foto'] = $arquivo;
		$time['divisao'] = str_replace("Série ", "", $time['divisao'] ?? '');
		//dataCadastro é uma posição que será adiconada dentro do array $time

		save('tabela_time', $time); // 'tabela_time' -> nome da tabela; $time -> associative array
	}
}

/**
 *	Atualizacao/Edicao de Cliente
 */
function edit()
{
	if (isset($_GET['id'])) {

		$id = $_GET['id'];

		if (isset($_POST['time'])) {
			csrf_exigir(); // antes de gravar
			update('tabela_time', $id, campos_time($_POST['time']));
			header("location: index.php");
		} else {

			global $time;
			$time = find('tabela_time', null, $id);
		}
	} else {
		header("location: index.php");
	}
}

/**
 *  Exclusão de um Cliente
 */
function delete($id = null)
{
	// Sem redirecionamento: excluir.php é chamado por fetch (util.js), que recarrega a página.
	// Um redirect faria o fetch carregar outra página que poderia consumir a mensagem temporária.
	return remove('tabela_time', $id); // true/false, usado por excluir.php para o código HTTP
}

?>