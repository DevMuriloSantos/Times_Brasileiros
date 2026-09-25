<!DOCTYPE html>
<html lang="pt-BR">

<?php
include("function.php");
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Visualizar Time | Times Brasileiros</title>
  <meta name="description" content="Visualize as informações de cadastro de um time brasileiro de futebol." />

  <link href="<?= BASEURL ?>assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= BASEURL ?>assets/css/style.css?v=2" rel="stylesheet" />
</head>

<body>
  <?php
  try {

    if (isset($_GET['id']) && is_numeric(base64_decode($_GET['id']))) {
      $id = base64_decode($_GET['id']);
    } else {
      header("Location: index.php");
    }

    view($id);

    if ($time != null) {

      $nome = $time->nome;
      $dataFormatFundacao = formatData($time->dataFundacao, "d/m/Y");
      $dataFormatCadastro = formatData($time->dataCadastro, "d/m/Y");
      $estado = $time->estado;
      $divisao = $time->divisao;

      if (empty($time->foto)) {
        $foto = "placeholder-time.png";
      } else {
        $foto = $time->foto;
      }
    } else {
      throw new Exception("Time não encontrado!");
    }
  } catch (Exception $e) {
    $_SESSION["message"] = "Erro ao buscar o time: " . $e->getMessage();
    $_SESSION["type"] = "danger";
  }

  include HEADER_TEMPLATE;
  ?>

  <main class="container py-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASEURL ?>index.php"><i class="bi bi-house-door me-1"></i>Início</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Visualizar Time</li>
      </ol>
    </nav>

    <?php
    if (isset($e) && ($e->getMessage() != "")) {
      echo <<<HTML
          <div class="col-lg-8 mx-auto">
            <div class="alert alert-danger rounded-3 text-center">
              <i class="bi bi-x-circle-fill me-2"></i>Time não encontrado.
              <a href="index.php" class="alert-link">Voltar à listagem</a>.
            </div>
          </div>
        HTML;
      return;
    } else {
      echo "
        <div id=\"conteudo\">
            <div class=\"row g-4 justify-content-center\">
              <div class=\"col-lg-5\">
                <img src=\"../assets/img/$foto\" alt=\"Foto do elenco do $nome\"
                  class=\"visualizar-img\" />
              </div>
              <div class=\"col-lg-6\">
                <div class=\"form-card h-100\">
                  <div class=\"form-header\">
                    <h4><i class=\"bi bi-shield-fill me-2\"></i>$nome</h4>
                    <p class=\"mb-0 small opacity-75\">Informações de cadastro</p>
                  </div>
                  <div class=\"form-body\">
                    <div class=\"info-item d-flex justify-content-between\">
                      <div>
                        <div class=\"info-label\">Identificador</div>
                        <div class=\"info-valor\"># $id</div>
                      </div>
                      <span class=\"badge-divisao badge-serie-$divisao align-self-center\">
                        Série $divisao
                      </span>
                    </div>
                    <div class=\"info-item\">
                      <div class=\"info-label\">Nome do Time</div>
                      <div class=\"info-valor\">$nome</div>
                    </div>
                    <div class=\"info-item\">
                      <div class=\"info-label\">Ano de Fundação</div>
                      <div class=\"info-valor\">$dataFormatFundacao</div>
                    </div>
                    <div class=\"info-item\">
                      <div class=\"info-label\">Estado</div>
                      <div class=\"info-valor\">
                        <i class=\"bi bi-geo-alt-fill text-success me-1\"></i>
                        $estado
                      </div>
                    </div>
                    <div class=\"info-item\">
                      <div class=\"info-label\">Data de Cadastro</div>
                      <div class=\"info-valor\">$dataFormatCadastro</div>
                    </div>

                    <div class=\"d-flex gap-2 mt-4 pt-2 border-top\">
                      <a href=\"../index.php\" class=\"btn btn-light rounded-3 flex-fill\"><i
                          class=\"bi bi-arrow-left me-1\"></i>Voltar</a>
                      <a href=\"editar.php?id=" . base64_encode($id) . "\" class=\"btn btn-incluir rounded-3 flex-fill\"><i
                          class=\"bi bi-pencil-fill me-1\"></i>Editar</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      ";
    }
    ?>
  </main>

  <footer class="footer-institucional">
    <div class="container text-center">
      <div class="footer-logo"><i class="bi bi-trophy-fill me-2"></i>Times Brasileiros</div>
      <p class="small mt-2 mb-0">&copy; <span id="ano"></span> Cadastro de Times Brasileiros. Todos os direitos
        reservados.</p>
    </div>
  </footer>

  <script src="<?= BASEURL ?>assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>