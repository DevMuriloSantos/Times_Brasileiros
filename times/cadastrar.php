<!DOCTYPE html>
<html lang="pt-BR">
<?php
include "function.php";
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastrar Time | Times Brasileiros</title>
  <meta name="description" content="Cadastre um novo time brasileiro de futebol no sistema." />

  <link rel="stylesheet" href="<?= BASEURL; ?>assets/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= BASEURL; ?>assets/css/style.css?v=2" rel="stylesheet" />
</head>

<?php
include HEADER_TEMPLATE;
try {
  add();
} catch (Exception $e) {
  $_SESSION["message"] = "Erro ao cadastrar o time: " . $e->getMessage();
  $_SESSION["type"] = "danger";
}
if (!empty($_SESSION['message'])):
  ?>

  <body>
    <div class="alert alert-<?= $_SESSION['type'] ?> container alertBox alert-dismissible fade show" role="alert">
      <h2><?= $_SESSION['message'] ?></h2>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      <a href="<?= BASEURL ?>index.php" class="btn btn-primary">Voltar</a>
    </div>
  <?php endif; ?>

  <main class="container py-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASEURL ?>index.php"><i class="bi bi-house-door me-1"></i>Início</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Cadastrar Time</li>
      </ol>
    </nav>

    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="form-card">
          <div class="form-header">
            <h4><i class="bi bi-plus-circle me-2"></i>Cadastrar Novo Time</h4>
            <p class="mb-0 small opacity-75">Preencha os dados do clube de futebol</p>
          </div>
          <div class="form-body">
            <form id="formCadastro" action="cadastrar.php" novalidate enctype="multipart/form-data"
              class="needs-validation" method="post">
              <div class="row g-4">
                <div class="col-lg-7">
                  <div class="mb-3">
                    <label for="nome" class="form-label"><i class="bi bi-shield-fill text-success me-1"></i>Nome do
                      Time</label>
                    <input type="text" class="form-control" id="nome" name="time[nome]" placeholder="Ex.: Flamengo"
                      required />
                    <div class="invalid-feedback">Informe o nome do time.</div>
                  </div>

                  <div class="mb-3">
                    <label for="fundacao" class="form-label"><i class="bi bi-calendar-event text-success me-1"></i>Data
                      de Fundação</label>
                    <input type="date" class="form-control" id="fundacao" name="time[dataFundacao]" required
                      min="1900-01-01" max="<?php echo date("Y-m-d"); ?>" />
                    <div class="invalid-feedback">Informe uma data entre 01/01/1900 e <?= date("d/m/Y"); ?>.</div>
                  </div>

                  <div class="mb-3">
                    <label for="estado" class="form-label"><i
                        class="bi bi-geo-alt-fill text-success me-1"></i>Estado</label>
                    <select class="form-select" id="estado" name="time[estado]" required>
                      <option value="" selected disabled>Selecione o estado</option>
                    </select>
                    <div class="invalid-feedback">Selecione o estado.</div>
                  </div>

                  <div class="mb-3">
                    <label for="divisao" class="form-label"><i
                        class="bi bi-award-fill text-success me-1"></i>Divisão</label>
                    <select class="form-select" id="divisao" name="time[divisao]" required>
                      <option value="" selected disabled>Selecione a divisão</option>
                      <option value="Série A">Série A</option>
                      <option value="Série B">Série B</option>
                      <option value="Série C">Série C</option>
                      <option value="Série D">Série D</option>
                    </select>
                    <div class="invalid-feedback">Selecione a divisão.</div>
                  </div>
                </div>

                <div class="col-lg-5">
                  <label for="imagem" class="form-label"><i class="bi bi-image-fill text-success me-1"></i>Miniatura do
                    Time</label>
                  <div class="preview-box mb-2" id="previewBox">
                    <img src="<?= BASEURL ?>assets/img/placeholder-time.png" alt="Pré-visualização da imagem do time"
                      id="previewImg" />
                  </div>
                  <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*" />
                  <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Selecione uma foto
                    oficial do elenco.</small>
                </div>
              </div>

              <div class="text-center mt-4 pt-2 border-top">
                <a href="<?= BASEURL ?>index.php" class="btn btn-light rounded-3 px-4 me-2"><i
                    class="bi bi-x-lg me-1"></i>Cancelar</a>
                <button type="submit" class="btn btn-cadastrar"><i class="bi bi-check-circle-fill me-2"></i>Cadastrar
                  Time</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="footer-institucional">
    <div class="container text-center">
      <div class="footer-logo"><i class="bi bi-trophy-fill me-2"></i>Times Brasileiros</div>
      <p class="small mt-2 mb-0">&copy; <span id="ano"></span> Cadastro de Times Brasileiros. Todos os direitos
        reservados.</p>
    </div>
  </footer>

  <script src="<?= BASEURL ?>assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASEURL ?>assets/js/data.js"></script>
  <script src="<?= BASEURL ?>assets/js/formulario.js"></script>

  <script>
    preencherEstados(document.getElementById('estado'), "");
  </script>
</body>

</html>