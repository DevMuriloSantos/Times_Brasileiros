<nav class="navbar navbar-expand-lg navbar-futebol sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASEURL ?>index.php">
            <span class="navbar-logo"><i class="bi bi-trophy-fill"></i></span>
            <span>
                Times Brasileiros
                <small class="d-block">SISTEMA DE CADASTRO</small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
            aria-controls="navMenu" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <ul class="navbar-nav gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link active text-white" href="<?= BASEURL ?>index.php"><i
                            class="bi bi-house-door me-1"></i>Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASEURL ?>times/cadastrar.php"><i
                            class="bi bi-plus-circle me-1"></i>Cadastrar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASEURL ?>times/times.php"><i
                            class="bi bi-trophy-fill me-1"></i>Times</a>
                </li>
            </ul>
        </div>
    </div>
</nav>