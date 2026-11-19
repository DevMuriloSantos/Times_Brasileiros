<?php

$mysqli = new mysqli_driver();
$mysqli->report_mode = MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR;
//report_mode -> é um atributo;

function open_database()
{
	try {
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$conn->set_charset("utf8"); // metodo;
		return $conn;
	} catch (Exception $e) {
		// O detalhe técnico vai só para o log do servidor; a página recebe uma mensagem genérica.
		error_log("Erro ao conectar no banco de dados: " . $e->getMessage());
		throw new Exception("Não foi possível conectar ao banco de dados.");
	}
}

function close_database($conn)
{
	try {
		$conn->close();
	} catch (Exception $e) {
		error_log("Erro ao encerrar conexão com o banco de dados: " . $e->getMessage());
		throw new Exception("Erro ao encerrar conexão com o banco de dados.");
	}
}

/**
 *  Tabelas que a aplicação pode acessar e colunas liberadas em cada operação.
 *
 *  Nomes de tabela e de coluna NUNCA vêm do usuário: só entram na SQL se estiverem nesta lista.
 *  'id' não aparece em nenhuma lista, e 'dataCadastro' (controlada pelo servidor) só pode ser
 *  definida no INSERT. Para liberar uma nova tabela/coluna, edite apenas este array.
 */
function config_tabela($table)
{
	static $tabelas = [
		'tabela_time' => [
			'inserir' => ['nome', 'dataFundacao', 'estado', 'divisao', 'dataCadastro', 'foto'],
			'atualizar' => ['nome', 'dataFundacao', 'estado', 'divisao', 'foto'],
		],
	];

	if (!is_string($table) || !isset($tabelas[$table])) {
		throw new Exception("Tabela não permitida.");
	}

	return $tabelas[$table];
}

/**
 *  Mantém de $data somente as colunas da lista $permitidas (o resto é ignorado).
 *  Valores que não sejam texto/número (ex.: arrays enviados pelo formulário) são rejeitados.
 */
function filtrar_campos($data, $permitidas)
{
	$dados = [];

	if (!is_array($data)) {
		return $dados;
	}

	foreach ($permitidas as $coluna) {
		if (!array_key_exists($coluna, $data)) {
			continue;
		}

		if ($data[$coluna] !== null && !is_scalar($data[$coluna])) {
			throw new Exception("Valor inválido para o campo $coluna.");
		}

		$dados[$coluna] = $data[$coluna];
	}

	return $dados;
}

/**
 *  Retorna o id como inteiro positivo, ou null se não for um inteiro válido.
 */
function id_valido($id)
{
	$id = filter_var($id, FILTER_VALIDATE_INT);

	return ($id !== false && $id > 0) ? $id : null;
}

/**
 *  Monta o INSERT preparado: [sql, tipos, valores]. Os valores nunca entram no texto da SQL.
 */
function montar_insert($table, $data)
{
	$cfg = config_tabela($table);
	$dados = filtrar_campos($data, $cfg['inserir']);

	if (empty($dados)) {
		throw new Exception("Nenhum campo válido para gravar.");
	}

	$colunas = [];
	foreach (array_keys($dados) as $coluna) {
		$colunas[] = "`$coluna`";
	}

	$sql = "INSERT INTO `$table` (" . implode(', ', $colunas) . ") VALUES ("
		. implode(', ', array_fill(0, count($dados), '?')) . ")";

	return [$sql, str_repeat('s', count($dados)), array_values($dados)];
}

/**
 *  Monta o UPDATE preparado por id: [sql, tipos, valores]. O id é o último parâmetro.
 */
function montar_update($table, $id, $data)
{
	$cfg = config_tabela($table);
	$id = id_valido($id);

	if ($id === null) {
		throw new Exception("Identificador inválido.");
	}

	$dados = filtrar_campos($data, $cfg['atualizar']);

	if (empty($dados)) {
		throw new Exception("Nenhum campo válido para atualizar.");
	}

	$sets = [];
	foreach (array_keys($dados) as $coluna) {
		$sets[] = "`$coluna` = ?";
	}

	$sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = ?";
	$valores = array_values($dados);
	$valores[] = $id;

	return [$sql, str_repeat('s', count($dados)) . 'i', $valores];
}

/**
 *  Lê todas as linhas de um statement SELECT e devolve um array de objetos
 *  (equivale a get_result() + fetch_object(), mas não depende do mysqlnd).
 */
function buscar_objetos($stmt)
{
	$linhas = [];
	$meta = $stmt->result_metadata();

	if (!$meta) {
		return $linhas;
	}

	$atual = [];
	$refs = [];
	while ($campo = $meta->fetch_field()) {
		$atual[$campo->name] = null;
		$refs[] = &$atual[$campo->name];
	}
	$meta->free();

	call_user_func_array([$stmt, 'bind_result'], $refs);

	while ($stmt->fetch()) {
		$linha = new stdClass();
		foreach ($atual as $nome => $valor) {
			$linha->$nome = $valor;
		}
		$linhas[] = $linha;
	}

	return $linhas;
}

/**
 *  Pesquisa registros de uma tabela:
 *   - $filtro: texto buscado em "nome" (LIKE), retorna array de objetos;
 *   - $id: retorna um objeto;
 *   - sem nenhum dos dois: retorna todos, ordenados por nome.
 *  Retorna null se não houver resultado ou em caso de erro (a mensagem fica na sessão).
 */
function find($table = null, $filtro = null, $id = null)
{
	$found = null;
	$database = null;

	try {
		config_tabela($table); // $table só entra na SQL se estiver na lista permitida

		$filtro = is_scalar($filtro) ? (string) $filtro : '';
		$sql = "SELECT * FROM `$table`";
		$tipos = '';
		$params = [];
		$um_registro = false;

		if ($filtro !== '') {
			// "|" é o caractere de escape do LIKE: %, _ e | digitados pelo usuário valem como texto comum
			$sql .= " WHERE nome LIKE ? ESCAPE '|' ORDER BY nome";
			$tipos = 's';
			$params[] = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $filtro) . '%';
		} else if ($id !== null) {
			$um_registro = true;
			$id = id_valido($id);

			if ($id === null) {
				return null; // identificador inválido: nada a buscar
			}

			$sql .= " WHERE id = ?";
			$tipos = 'i';
			$params[] = $id;
		} else {
			$sql .= " ORDER BY nome";
		}

		$database = open_database();
		$stmt = $database->prepare($sql);

		if ($tipos !== '') {
			$stmt->bind_param($tipos, ...$params);
		}

		$stmt->execute();
		$linhas = buscar_objetos($stmt);
		$stmt->close();

		if (count($linhas) > 0) {
			$found = $um_registro ? $linhas[0] : $linhas;
		}
	} catch (Exception $e) {
		error_log("Erro na consulta ($table): " . $e->getMessage());
		$_SESSION['message'] = "Não foi possível consultar os dados.";
		$_SESSION['type'] = 'danger';
		$found = null;
	}

	if ($database) {
		close_database($database);
	}

	return $found;
}

/**
 *  Pesquisa Todos os Registros de uma Tabela
 */
function find_all($table, $filtro = null)
{
	return find($table, $filtro);
}

/**
 *  Insere um registro no BD. Só as colunas permitidas em config_tabela() são gravadas.
 *  Retorna true/false e registra a mensagem na sessão.
 */
function save($table = null, $data = null)
{
	$database = null;
	$ok = false;

	try {
		list($sql, $tipos, $valores) = montar_insert($table, $data);

		$database = open_database();
		$stmt = $database->prepare($sql);
		$stmt->bind_param($tipos, ...$valores);
		$stmt->execute();
		$stmt->close();

		$_SESSION["message"] = "Registro feito com sucesso.";
		$_SESSION["type"] = "success";
		$ok = true;

	} catch (Exception $e) {
		error_log("Erro ao inserir ($table): " . $e->getMessage());
		$_SESSION["message"] = "Não foi possivel realizar a operacao.";
		$_SESSION["type"] = "danger";
	}

	if ($database) {
		close_database($database);
	}

	return $ok;
}

/**
 *  Atualiza um registro em uma tabela, por ID. Só as colunas permitidas em config_tabela() são alteradas.
 *  Retorna true/false e registra a mensagem na sessão.
 */
function update($table = null, $id = 0, $data = null)
{
	$database = null;
	$ok = false;

	try {
		list($sql, $tipos, $valores) = montar_update($table, $id, $data);

		$database = open_database();
		$stmt = $database->prepare($sql);
		$stmt->bind_param($tipos, ...$valores);
		$stmt->execute();
		$stmt->close();

		$_SESSION["message"] = "Registro atualizado com sucesso.";
		$_SESSION["type"] = "success";
		$ok = true;

	} catch (Exception $e) {
		error_log("Erro ao atualizar ($table): " . $e->getMessage());
		$_SESSION["message"] = "Não foi possível realizar a operação.";
		$_SESSION["type"] = "danger";
	}

	if ($database) {
		close_database($database);
	}

	return $ok;
}

/**
 *  Remove um registro de uma tabela, por ID.
 *  Retorna true/false e registra a mensagem na sessão.
 */
function remove($table = null, $id = null)
{
	$database = null;
	$ok = false;

	try {
		config_tabela($table); // $table só entra na SQL se estiver na lista permitida

		$id = id_valido($id);

		if ($id === null) {
			throw new Exception("Identificador inválido.");
		}

		$database = open_database();
		$stmt = $database->prepare("DELETE FROM `$table` WHERE id = ?");
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->close();

		$_SESSION["message"] = "Registro Removido com Sucesso.";
		$_SESSION["type"] = "success";
		$ok = true;

	} catch (Exception $e) {
		error_log("Erro ao remover ($table): " . $e->getMessage());
		$_SESSION["message"] = "Não foi possível realizar a operação.";
		$_SESSION["type"] = "danger";
	}

	if ($database) {
		close_database($database);
	}

	return $ok;
}
?>
