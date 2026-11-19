# CLAUDE.md — CRUD Soccer (Times Brasileiros)

Site acadêmico (matéria de Programação Web) de cadastro de clubes do futebol brasileiro. Interface, comentários e mensagens em **português (pt-BR)**. Projeto **simples por design**: não adicionar complexidade, padrões ou regras além do necessário.

- Repositório: `DevMuriloSantos/Times_Brasileiros` (branch `master`). Publicado na InfinityFree: https://crudsoccer.infinityfreeapp.com/
- Roteiro das próximas etapas, riscos, proposta de banco e pendências: **`docs/PLANO_IMPLEMENTACAO.md`** — leia antes de iniciar qualquer etapa.

## Tecnologias

PHP procedural (sem classes próprias/framework/Composer) · MySQL via **MySQLi** · HTML/CSS/JS puro · Bootstrap **5.3.8 local** (`assets/css`, `assets/js`) · Bootstrap Icons via CDN · XAMPP local em `/Times_Brasileiros/`. Sem testes automatizados, build ou `.htaccess`. **Não adicionar frameworks nem dependências.**

## Organização

```
index.php            home (estatísticas, consulta direta ao banco)
excluir.php          endpoint POST de exclusão de time (chamado por fetch em assets/js/util.js)
config.php           constantes de banco, BASEURL, ABSPATH, DBAPI, HEADER_TEMPLATE (LOCAL, no .gitignore)
config.example.php   modelo do config.php, sem credenciais (não é carregado pela aplicação)
tabelaTime.sql       script SQL (banco + tabelas + dados iniciais)
inc/database.php     acesso a dados (prepared statements): open/close_database, find, find_all, save, update, remove;
                     config_tabela() = lista permitida de tabelas/colunas
inc/sessao.php       iniciar_sessao(), definir_msg(), obter_msg() (sessão e mensagens temporárias);
                     e() / js_str() (escape de saída); csrf_token(), csrf_campo(), csrf_exigir() (CSRF)
inc/header.php       navbar compartilhada (HEADER_TEMPLATE)
times/               CRUD de times: function.php (controller), times.php (lista+busca),
                     cadastrar.php, editar.php, visualizar.php
assets/              css/style.css, js/{util,formulario,data}.js, img/ (miniaturas, placeholder, uploads)
docs/                documentação de planejamento
```

## Como a aplicação funciona

- Sem roteador: cada `.php` é uma página acessada direto. Páginas de `times/` incluem `function.php`, que faz `ob_start()` (necessário por causa dos `header("location: ...")`) e inclui `config.php` e `inc/database.php`.
- Formulários de cadastro/edição enviam POST para a própria página. A exclusão é um `fetch` POST (`id` em base64) para `excluir.php`.
- IDs na URL usam `base64_encode` — é só ofuscação, **não é segurança**.
- Sessão: `times/function.php` chama `iniciar_sessao()` (inc/sessao.php); por isso as páginas de `times/` incluem `function.php` **na primeira linha, antes do `<!doctype>`** (o cookie de sessão exige que nada tenha sido enviado). `index.php` não usa sessão.
- Mensagens de feedback (flash): registrar com `definir_msg($texto, $tipo)` (`$tipo` = classe de alerta Bootstrap) — `inc/database.php` grava as mesmas chaves `$_SESSION['message']`/`['type']`; exibir com `$msg = obter_msg()`, que **remove** a mensagem da sessão (aparece uma única vez). Toda página que exibe alertas deve consumir a mensagem; quem não exibe e pode receber mensagem de `find/save/update/remove` deve descartá-la com `obter_msg()`.
- Acesso a dados: usar **somente** as funções de `inc/database.php`, nunca `->query()` com valores externos. Tabela nova ou coluna nova só funciona depois de ser liberada em `config_tabela()` (listas `inserir`/`atualizar`; `id` nunca entra). Os controladores filtram o formulário (ex.: `campos_time()`), e `save/update/remove` retornam `true/false` e registram mensagem genérica (o detalhe do erro vai para `error_log`).
- Tabela em uso: **`tabela_time`** (`id`, `nome`, `dataFundacao`, `estado`, `divisao` char(1) A–D, `dataCadastro`, `foto`). `foto` guarda só o nome do arquivo em `assets/img/`; vazio → `placeholder-time.png`. Não há chaves estrangeiras hoje.
- `divisao`: os formulários mostram "Série X", mas o banco guarda só a letra.

## Estado atual vs. planejado

**Existe:** CRUD completo de times, busca por nome, upload de miniatura, home com estatísticas.
**NÃO existe (planejado):** autenticação, níveis de acesso, CRUD de usuários, CRUD de jogadores, upload seguro, validação no servidor. Hoje **qualquer pessoa** escreve no sistema. A tabela `usuarios` em `tabelaTime.sql` (alteração local) é só rascunho descartável — não usar.

## Regras de negócio aprovadas

| Ação | Visitante | Usuário comum | Administrador |
|---|:-:|:-:|:-:|
| Listar/visualizar times e jogadores | Sim | Sim | Sim |
| Cadastrar/editar/excluir times e jogadores | Não | Sim | Sim |
| Listar/visualizar/cadastrar/editar/excluir usuários | Não | Não | Sim |

- **Não há cadastro público**: só administradores criam usuários.
- Permissões **sempre verificadas no PHP (servidor)**; esconder botões na interface não basta.
- Futura tabela `jogadores`: `id`, `nome` (obrig.), `time_id` (obrig., **FK → `tabela_time.id`**), `numero_camisa` (opc.), `data_nascimento` (opc.), `foto` (opc., aceita `NULL`, só o nome do arquivo; imagem no servidor). **Sem posição e sem nacionalidade.** Cada jogador pertence a um único time.
- Decisões ainda pendentes (camisa repetida, exclusão de time com jogadores, estrutura final de `usuarios`, primeiro admin, etc.) estão em `docs/PLANO_IMPLEMENTACAO.md` — **não assumir; perguntar ao responsável.**

## Padrões do código existente (seguir)

- PHP procedural, funções globais; estilo do arquivo que estiver editando (não há formatação única).
- Páginas por pasta, no padrão `times/`: `function.php` + listagem + `cadastrar.php` + `editar.php` + `visualizar.php`.
- Usar `BASEURL`/`ABSPATH`/`__DIR__` para caminhos; `HEADER_TEMPLATE` para a navbar.
- Tabelas `tabela_*` em snake_case; colunas existentes em camelCase (`dataFundacao`); colunas novas aprovadas em snake_case (`time_id`).
- CSS: componentes próprios em português (`.btn-verde`, `.form-card`, `.tabela-card`…) sobre Bootstrap; variáveis em `:root`. Ao mudar CSS/JS, incrementar `?v=` nos `<link>/<script>` das páginas afetadas.
- Commits em Conventional Commits, em português (`feat:`, `fix:`).

## Segurança — regras para todo código novo

- **Prepared statements MySQLi** (`prepare`/`bind_param`/`execute`) para qualquer dado externo. Nunca interpolar entrada em SQL; nome de tabela/coluna só de lista fixa.
- Senhas: `password_hash()` / `password_verify()`; nunca texto puro nem logs com senha/hash.
- Sessão iniciada de forma central, cookie `httponly`/`samesite`, `session_regenerate_id(true)` no login, logout destrói a sessão.
- Autorização no servidor em toda página/endpoint protegido, antes de qualquer saída.
- Escritas só por **POST** com **token CSRF**: `<?= csrf_campo() ?>` em todo `<form method="post">` que grava; `csrf_exigir()` no servidor **antes** de upload/banco (403 se inválido); no `fetch`, enviar o token no cabeçalho `X-CSRF-Token` (ver `excluir.php` e `assets/js/util.js`). Nunca na URL. Buscas (POST só de leitura) não precisam.
- Escapar **toda** saída dinâmica (banco, formulário, URL, mensagens, atributos `value`/`src`/`data-*`) com `e($v)` (`htmlspecialchars` com `ENT_QUOTES|ENT_SUBSTITUTE`, UTF-8); em `<script>`, `js_str($v)`; `src` de foto com `e(foto_time($f))`. Sem `onclick` inline com dados: usar `data-*` + `addEventListener`. O banco guarda o texto original (nunca escapado).
- Validar tudo no servidor (tipos, tamanhos, `divisao` ∈ A–D, existência de `time_id`); não aceitar `$_POST` inteiro como colunas.
- Upload: validar tipo real (`finfo`/`getimagesize`), tamanho e gerar nome novo no servidor; nunca usar o nome enviado.
- Não exibir erros do MySQL ao usuário. **Nunca escrever credenciais ou hashes** em código versionado, docs, commits ou respostas.
- **Não reproduzir** os problemas ainda existentes (upload pelo nome original, falta de validação no servidor); a correção está planejada na Etapa 1 do plano (progresso registrado lá).

## Instruções para alterações

1. Leia o arquivo antes de editar; preserve o CRUD de times (URLs, campos de formulário, assinaturas de `inc/database.php` usadas por `times/`).
2. Faça apenas o que a etapa/tarefa pedir; não refatore por conta própria.
3. Teste manualmente no XAMPP: listar, buscar, cadastrar (com e sem imagem), visualizar, editar, excluir.
4. **Não fazer commit, push nem deploy sem pedido explícito.** Nunca descartar alterações locais (`git checkout`/`reset`/`stash` sem autorização).
5. Alterações locais não commitadas (decisão do responsável): `assets/css/style.css` (variáveis CSS renomeadas, hero `.jpg`) e `tabelaTime.sql` (rascunho de `usuarios`). Usar os nomes **novos** das variáveis CSS; `times/editar.php:121` ainda usa `var(--dourado)` (inexistente).
6. Schema novo: adicionar em `tabelaTime.sql` com `CREATE TABLE IF NOT EXISTS`, charset `utf8`, tabelas **InnoDB** (necessário para a FK).

## Configuração (`config.php`) e hospedagem (InfinityFree)

- `config.php` guarda as credenciais e **não é versionado** (`.gitignore`; nunca commitar). Ambiente novo: copiar `config.example.php` para `config.php` e trocar os valores de exemplo (banco, usuário, senha, host e `BASEURL`).
- `BASEURL` local é `/Times_Brasileiros/`; em produção deve ser `"/"`. O `config.php` de produção (banco/host/credenciais) **não está no repositório e nunca deve ser sobrescrito** ao publicar.
- Versões de PHP/MySQL, extensões, limites de upload e permissões de escrita em `assets/img/` **não foram verificadas** — checklist em `docs/PLANO_IMPLEMENTACAO.md` (seção 6). Não presumir recursos além do que está comprovado; sem Composer/CLI/cron.
- Servidor Linux é sensível a maiúsculas/minúsculas em nomes de arquivo. Uploads de produção não estão no Git.
- `create database`/`use` do `tabelaTime.sql` não funcionam em hospedagem compartilhada: em produção, importar só as tabelas.
