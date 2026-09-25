<!doctype html>
<html lang="pt-BR">

<?php
  include("config.php");
  include("inc/database.php");
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastro de Times Brasileiros</title>
  <meta name="description" content="Sistema administrativo para cadastro e gestão de times brasileiros de futebol." />

  <link rel="shortcut icon" href="<?= BASEURL ?>assets/img/favicon.ico" type="image/x-icon">
  <link href="<?= BASEURL ?>assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= BASEURL ?>assets/css/style.css?v=2" rel="stylesheet" />
</head>

<body class="position-relative">
  <?php
  include HEADER_TEMPLATE;
  ?>

  <header class="hero">
    <div class="container">
      <span class="badge-dourado mb-3 d-inline-block"><i class="bi bi-star-fill me-1"></i>Futebol Brasileiro</span>
      <h1 class="text-balance">Cadastro de Times Brasileiros</h1>
      <p class="text-pretty mt-2">
        Gerencie clubes do futebol nacional com organização e paixão.
        Consulte, cadastre e administre todos os times em um só lugar.
      </p>
    </div>
  </header>

  <?php
  try {
    $database = open_database();

    $sql_qtd = "select count(id) as total, sum(divisao = 'A') as serieA, sum(divisao = 'B') as serieB from tabela_time";
    $sql_ultimo_cadastro = "select nome from tabela_time order by id desc limit 1";

    $query_qtd = $database->query($sql_qtd)->fetch_object();
    $query_ultimo_cadastro = $database->query($sql_ultimo_cadastro)->fetch_object();

    if ($query_qtd->total > 0) {
      $totalTimes = $query_qtd->total;
      $totalSerieA = $query_qtd->serieA;
      $totalSerieB = $query_qtd->serieB;

      $ultimoCadastro = $query_ultimo_cadastro->nome;
    } else {
      $totalTimes = 0;
      $totalSerieA = 0;
      $totalSerieB = 0;

      $ultimoCadastro = "-";
    }
  } catch (Exception $e) {
    echo "
        <div class=\"alert alert-danger alert-dismissible fade show rounded-3 shadow-sm position-fixed errorBox\" role=\"alert\">
          <i class=\"bi bi-x-circle-fill me-2\"></i>
          <strong>Erro ao buscar dados: {$e->getMessage()}</strong>
          <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Fechar\"></button>
        </div>
      ";
  }
  ?>

  <main class="container">
    <section class="row g-3 mt-3" aria-label="Resumo do sistema">
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon verde"><i class="bi bi-people-fill"></i></div>
          <div>
            <div class="stat-valor" id="statTotal"><?php echo $totalTimes ?? "0"; ?></div>
            <p class="stat-label">Total de Times</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon dourado">
            <i class="bi bi-award-fill"></i>
          </div>
          <div>
            <div class="stat-valor" id="statSerieA"><?php echo $totalSerieA ?? "0"; ?></div>
            <p class="stat-label">Times da Série A</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon azul"><i class="bi bi-trophy"></i></div>
          <div>
            <div class="stat-valor" id="statSerieB"><?php echo $totalSerieB ?? "0"; ?></div>
            <p class="stat-label">Times da Série B</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon cinza">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <div class="stat-valor fs-5" id="statUltimo"><?php echo $ultimoCadastro ?? "-"; ?></div>
            <p class="stat-label">Último Cadastrado</p>
          </div>
        </div>
      </div>
    </section>

    <div class="row justify-content-center g-3 my-4">
      <div class="col-12 col-sm-auto">
        <a href="times/times.php" class="btn btn-verde w-100 px-4">
          <i class="bi bi-trophy-fill me-1"></i>Ver Times
        </a>
      </div>
      <div class="col-12 col-sm-auto">
        <a href="times/cadastrar.php" class="btn btn-incluir w-100 px-4">
          <i class="bi bi-plus-lg me-1"></i>Cadastrar Time
        </a>
      </div>
    </div>
  </main>

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
            <li><a href="index.php">Início</a></li>
            <li><a href="times/times.php">Listagem de Times</a></li>
            <li><a href="times/cadastrar.php">Cadastrar Time</a></li>
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