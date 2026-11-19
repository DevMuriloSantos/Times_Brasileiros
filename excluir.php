<?php
    include 'times/function.php';

    // Exclusão só por POST: GET (ou outro método) nunca apaga nada.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        encerrar_http(405, 'Método não permitido.');
    }

    // Token CSRF (cabeçalho X-CSRF-Token enviado por assets/js/util.js): 403 antes de tocar no banco.
    csrf_exigir();

    $id = base64_decode(is_string($_POST['id'] ?? null) ? $_POST['id'] : '', true);

    if ($id === false || id_valido($id) === null) {
        encerrar_http(400, 'Identificador inválido.');
    }

    if (!delete($id)) {
        obter_msg(); // fetch não exibe a mensagem; descarta para não aparecer em outra página
        encerrar_http(500, 'Não foi possível excluir o time.');
    }
    // sucesso: responde 200 e o util.js recarrega a página (que exibe a mensagem de sucesso)
?>
