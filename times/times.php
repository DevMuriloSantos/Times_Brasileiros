<!doctype html>
<html lang="pt-BR">

<?php
include "function.php";
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cadastro de Times Brasileiros</title>
    <meta name="description" content="Sistema administrativo para cadastro e gestão de times brasileiros de futebol." />

    <link href="<?= BASEURL ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="<?= BASEURL ?>assets/css/style.css?v=2" rel="stylesheet" />
</head>

<body class="position-relative">
    <?php

    include HEADER_TEMPLATE;

    try {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            index($_POST["filtro"]);
        } else {
            index();
        }
    } catch (Exception $e) {
        $_SESSION["message"] = "Erro ao buscar os times: " . $e->getMessage();
        $_SESSION["type"] = "danger";
    }

    if (!empty($_SESSION['message'])):
        ?>
        <div class="alert alert-<?= $_SESSION['type'] ?> container alertBox alert-dismissible fade show" role="alert">
            <h2><?= $_SESSION['message'] ?></h2>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <main class="container mt-5">
        <div class="search-card">
            <form class="row g-2 align-items-center" id="formPesquisa" role="search" method="post">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="bi bi-search text-success"></i></span>
                        <input type="text" id="campoPesquisa" name="filtro" class="form-control border-start-0"
                            placeholder="Pesquisar time pelo nome" aria-label="Pesquisar time pelo nome" />
                    </div>
                </div>
                <div class="col-6 col-md-auto">
                    <button type="submit" class="btn btn-verde w-100 px-4">
                        <i class="bi bi-search me-1"></i>Pesquisar
                    </button>
                </div>
                <div class="col-6 col-md-auto">
                    <a href="<?= BASEURL ?>times/cadastrar.php" class="btn btn-incluir w-100 px-4"><i
                            class="bi bi-plus-lg me-1"></i>Incluir Time</a>
                </div>
            </form>
        </div>

        <?php
        echo <<<HTML
          <section class="tabela-card mt-3">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nome do Time</th>
                    <th scope="col">Ano de Fundação</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Divisão</th>
                    <th scope="col">Data de Cadastro</th>
                    <th scope="col" class="text-center">Miniatura</th>
                    <th scope="col" class="text-center">Ações</th>
                  </tr>
                </thead>
                <tbody id="corpoTabela">              
        HTML;

        if ((isset($e) && $e->getMessage() != "") || $times === null) {
            echo <<<HTML
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <i class="bi bi-search"></i>
                  <p class="mt-2 mb-0">Nenhum time encontrado!</p>
                </div>
              </td>
            </tr>
          HTML;
        }

        if ($times !== null) {
            while ($time = array_shift($times)) {
                $dataFormatCadastro = formatData($time->dataCadastro, 'd/m/Y');
                $dataFormatFundacao = formatData($time->dataFundacao, 'd/m/Y');

                if (empty($time->foto)) {
                    $foto = 'placeholder-time.png';
                } else {
                    $foto = $time->foto;
                }

                $id = base64_encode($time->id);

                echo "<tr>\n
                  <td><span class=\"fw-bold text-success\">#$time->id</span></td>\n
                  <td class=\"fw-semibold\">$time->nome</td>\n
                  <td>$dataFormatFundacao</td>\n
                  <td>$time->estado</td>\n
                  <td><span class=\"badge-divisao badge-serie-$time->divisao\">Série $time->divisao</span></td>\n
                  <td>$dataFormatCadastro</td>\n
                  <td class=\"text-center\">\n
                    <img src=\"../assets/img/$foto\" alt=\"Miniatura do time\" class=\"miniatura\"/>
                  </td>
                  <td class=\"text-center text-nowrap\">
                    <a href=\"visualizar.php?id=$id\" class=\"btn-acao btn-ver me-1\" data-bs-toggle=\"tooltip\" title=\"Visualizar\" aria-label=\"Visualizar $time->nome\"><i class=\"bi bi-eye-fill\"></i></a>
                    <a href=\"editar.php?id=$id\" class=\"btn-acao btn-editar me-1\" data-bs-toggle=\"tooltip\" title=\"Editar\" aria-label=\"Editar $time->nome\"><i class=\"bi bi-pencil-fill\"></i></a>
                    <button type=\"button\" class=\"btn-acao btn-apagar\" data-bs-toggle=\"tooltip\" title=\"Apagar\" aria-label=\"Apagar $time->nome\" onclick=\"abrirModalExcluir('$time->nome', '$id')\"><i class=\"bi bi-trash-fill\"></i></button>
                  </td>
                </tr>";
            }
        }

        echo <<<HTML
                </tbody>
              </table>
            </div>
          </section>
        HTML;
        ?>

        <nav class="d-flex justify-content-center mt-4" aria-label="Paginação">
            <ul class="pagination" id="paginacao"></ul>
        </nav>
    </main>

    <div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 18px; border: none">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="modalExcluirLabel">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja apagar o time
                    <strong id="nomeExcluir"></strong>? Esta ação não poderá ser
                    desfeita.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger rounded-3" id="btnConfirmarExclusao">
                        <i class="bi bi-trash me-1"></i>Apagar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-institucional">
        <div class="container">
            <div class="row gy-3">
                <div class="col-md-6">
                    <div class="footer-logo">
                        <i class="bi bi-trophy-fill me-2"></i>Times Brasileiros
                    </div>
                    <p class="mt-2 mb-0 small">
                        Sistema administrativo de cadastro de clubes do futebol
                        brasileiro. Desenvolvido por Murilo Santos e Matheus Barros.
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white">Navegação</h6>
                    <ul class="list-unstyled small">
                        <li><a href="../index.php">Início</a></li>
                        <li><a href="times.php">Listagem de Times</a></li>
                        <li><a href="cadastrar.php">Cadastrar Time</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white">Sobre</h6>
                    <p class="small mb-0">
                        Sistema desenvolvido com PHP, MySQL e Bootstrap.
                    </p>
                </div>
            </div>
            <hr class="border-light opacity-25 my-3" />
            <p class="text-center small mb-0">
                &copy; <span id="ano"></span> Cadastro de Times Brasileiros. Todos os
                direitos reservados.
            </p>
        </div>
    </footer>

    <script src="<?= BASEURL ?>assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASEURL ?>assets/js/util.js?v=2"></script>
</body>

</html>