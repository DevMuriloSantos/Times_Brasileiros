<?php
ob_start(); //output buffer aberto, para não dar erro de header location

include(__DIR__ . '/../config.php');
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
	if (!empty($_POST['time'])) {

		$time = $_POST['time'];

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
		$time['divisao'] = str_replace("Série ", "", $_POST['time']['divisao']);
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
			update('tabela_time', $id, $_POST['time']);
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
	remove('tabela_time', $id);

	header("location: index.php");
}

?>