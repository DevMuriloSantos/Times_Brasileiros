<!DOCTYPE html>
<html lang="pt-BR">

<?php
include "function.php";
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Time | Times Brasileiros</title>
  <meta name="description" content="Edite as informações de um time brasileiro de futebol." />

  <link href="<?= BASEURL ?>assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= BASEURL ?>assets/css/style.css?v=2" rel="stylesheet" />
</head>

<body>
  <?php
  include HEADER_TEMPLATE;

  try {
    if (isset($_GET['id']) && is_numeric(base64_decode($_GET['id']))) {
      $id = base64_decode($_GET['id']);
    } else {
      throw new Exception("Produto não existe!");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      view($id);

      if ($time !== null) {
        $dados = $time;

        $nome = $dados->nome;
        $dtFundacao = new DateTime($dados->dataFundacao, new DateTimeZone("America/Sao_Paulo"));
        $dataFormatFundacao = $dtFundacao->format("Y-m-d");
        $dtCadastro = new DateTime($dados->dataCadastro, new DateTimeZone("America/Sao_Paulo"));
        $dataFormatCadastro = $dtCadastro->format("d/m/Y");
        $estado = $dados->estado;
        $divisao = $dados->divisao;

        if (empty($dados->foto)) {
          $foto = "placeholder-time.png";
        } else {
          $foto = $dados->foto;
        }
      } else {
        throw new Exception("Time não encontrado.");
      }
    } else if (($_SERVER['REQUEST_METHOD'] == 'POST')) {
      view($id);

      if ($time === null) {
        throw new Exception("Time não encontrado.");
      }

      $nome = $_POST['nome'];
      $dataFormatFundacao = $_POST['fundacao'];
      $estado = $_POST['estado'];
      $divisao = str_replace("Série ", "", $_POST['divisao']);
      $arquivo = $time->foto;

      $target_dir = "../assets/img/";
      $temImagemNova = isset($_FILES["imagemNova"]) && $_FILES["imagemNova"]["error"] === UPLOAD_ERR_OK;

      if ($temImagemNova) {
        $target_file = $target_dir . basename($_FILES["imagemNova"]["name"]);
        $arquivo = basename($_FILES["imagemNova"]["name"]);

        if (!move_uploaded_file($_FILES["imagemNova"]["tmp_name"], $target_file)) {
          throw new Exception("Nao foi possivel fazer o upload da imagem. Tente novamente!");
        }
      } elseif (isset($_FILES["imagemNova"]) && $_FILES["imagemNova"]["error"] !== UPLOAD_ERR_NO_FILE) {
        throw new Exception("Nao foi possivel receber a imagem. Tente novamente!");
      }

      update('tabela_time', $id, [
        'nome' => $nome,
        'dataFundacao' => $dataFormatFundacao,
        'estado' => $estado,
        'divisao' => $divisao,
        'foto' => $arquivo
      ]);
      $foto = $arquivo;
      echo <<<ALERT
            <div class="alert alert-info alertBox container alert-dismissible fade show" role="alert">
                <h2>
                    Atualizado com sucesso!
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <a href="../index.php" class="btn btn-primary">Voltar</a>
            </div>\n
        ALERT;
    }
  } catch (Exception $e) {
    echo <<<ALERT
            <div class="alert alert-danger container alert-dismissible fade show" role="alert">
                <h2>Aconteceu um erro:<br>
                    {$e->getMessage()}<br>
                </h2>\n
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <a href="index.php" class="btn btn-primary">Voltar</a>
            </div>\n
        ALERT;
  }
  ?>

  <main class="container py-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../index.php"><i class="bi bi-house-door me-1"></i>Início</a></li>
        <li class="breadcrumb-item active" aria-current="page">Editar Time</li>
      </ol>
    </nav>

    <div id="conteudo" class="row justify-content-center">
      <div class="col-lg-10">
        <div class="form-card">
          <div class="form-header" style="background:linear-gradient(100deg,#8a5b00,var(--dourado));">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Time</h4>
            <p class="mb-0 small opacity-75">Atualize os dados do clube de futebol</p>
          </div>
          <div class="form-body">
            <?php $id = base64_encode($id) ?>
            <form action="editar.php?id=<?php echo $id; ?>" id="formEdicao" novalidate enctype="multipart/form-data"
              method="post" class="needs-validation">
              <div class="row g-4">
                <div class="col-lg-7">
                  <div class="mb-3">
                    <label for="nome" class="form-label"><i class="bi bi-shield-fill text-success me-1"></i>Nome do
                      Time</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $nome; ?>"
                      required />
                    <div class="invalid-feedback">Informe o nome do time.</div>
                  </div>
                  <div class="mb-3">
                    <label for="fundacao" class="form-label"><i class="bi bi-calendar-event text-success me-1"></i>Data
                      de Fundação</label>
                    <input type="date" class="form-control" id="fundacao" name="fundacao" required
                      value="<?php echo $dataFormatFundacao; ?>" min="1900-01-01" max="<?php echo date("Y-m-d"); ?>" />
                    <div class="invalid-feedback">Informe a data de fundação.</div>
                  </div>
                  <div class="mb-3">
                    <label for="estado" class="form-label"><i
                        class="bi bi-geo-alt-fill text-success me-1"></i>Estado</label>
                    <select class="form-select" id="estado" name="estado" required>
                      <option value="" disabled>Selecione o estado</option>
                    </select>
                    <div class="invalid-feedback">Selecione o estado.</div>
                  </div>
                  <div class="mb-3">
                    <label for="divisao" class="form-label"><i
                        class="bi bi-award-fill text-success me-1"></i>Divisão</label>
                    <select class="form-select" id="divisao" name="divisao" required>
                      <option value="" disabled selected>Selecione a divisão</option>
                    </select>
                    <div class="invalid-feedback">Selecione a divisão.</div>
                  </div>
                </div>
                <div class="col-lg-5">
                  <label for="imagem" class="form-label"><i class="bi bi-image-fill text-success me-1"></i>Miniatura do
                    Time</label>
                  <div class="preview-box mb-2">
                    <img src="../assets/img/<?php echo $foto; ?>" alt="Pré-visualização da imagem do time"
                      id="previewImg" />
                  </div>
                  <input type="file" class="form-control" id="imagem" name="imagemNova" accept="image/*" />
                  <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Deixe em branco para
                    manter a imagem atual.</small>
                </div>
              </div>
              <div class="text-center mt-4 pt-2 border-top">
                <a href="../index.php" class="btn btn-light rounded-3 px-4 me-2"><i
                    class="bi bi-x-lg me-1"></i>Cancelar</a>
                <button type="submit" class="btn btn-cadastrar"><i class="bi bi-check-circle-fill me-2"></i>Salvar
                  Alterações</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer-institucional">
    <div class="container text-center">
      <div class="footer-logo"><i class="bi bi-trophy-fill me-2"></i>Times Brasileiros</div>
      <p class="small mt-2 mb-0">&copy; <span id="ano"></span> Cadastro de Times Brasileiros. Todos os direitos
        reservados.</p>
    </div>
  </footer>

  <script src="<?= BASEURL ?>assets/js/bootstrap.bundle.min.js"></script>
  <script type="module" src="<?= BASEURL ?>assets/js/formulario.js"></script>
  <script src="<?= BASEURL ?>assets/js/data.js"></script>
  <script>
    preencherEstados(document.getElementById('estado'), "<?php echo $estado; ?>");
    preencherCategorias(document.getElementById('divisao'), "<?php echo "Série $divisao"; ?>");
  </script>
</body>


</html>