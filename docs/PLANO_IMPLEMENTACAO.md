# Plano de implementação — CRUD Soccer (Times Brasileiros)

Planejamento simples para as próximas etapas. Mantém PHP procedural, MySQLi, Bootstrap 5.3.8 e a organização atual. **Nada aqui foi implementado ainda.** Regras de negócio e permissões aprovadas estão no `CLAUDE.md`.

Convenção: itens marcados **(aprovar)** dependem de decisão do responsável; a recomendação está na seção 7.

## Progresso

**Etapa 1 — em andamento (Tarefa 03: configurações e sessões).**

| Item | Situação |
|---|---|
| 2.2 Sessão + mensagens temporárias | **Feito e testado localmente.** `inc/sessao.php` (`iniciar_sessao`, `definir_msg`, `obter_msg`), iniciada em `times/function.php`; páginas de `times/` incluem `function.php` na 1ª linha (antes do HTML). |
| 2.1 Credenciais | **Parcial.** Criados `.gitignore` (`/config.php`) e `config.example.php`. **Pendente:** retirar `config.php` do índice (`git rm --cached config.php`) — ver bloqueio abaixo. |
| 2.3 Prepared statements | **Feito (Tarefa 04).** `find`, `save`, `update`, `remove` em `inc/database.php` usam `prepare/bind_param/execute`; identificadores de tabela/coluna só entram na SQL se estiverem em `config_tabela()`. |
| 2.4 Mass assignment | **Feito (Tarefa 04).** Listas explícitas de colunas para `inserir` e `atualizar` em `config_tabela()` (sem `id`; `dataCadastro` só no INSERT) + `campos_time()` em `times/function.php` (só `nome`, `dataFundacao`, `estado`, `divisao` vêm do formulário). |
| 2.5 XSS | **Feito (Tarefa 05).** `e()` e `js_str()` em `inc/sessao.php`; escape em `index.php`, `times/times.php`, `visualizar.php`, `editar.php`, `cadastrar.php` (valores, atributos, mensagens, `<script>`); `foto_time()` codifica o nome da foto na URL; `onclick` inline removido. |
| 2.6 CSRF | **Feito (Tarefa 05).** `csrf_token/csrf_campo/csrf_valido/csrf_exigir` em `inc/sessao.php`; campo oculto em cadastrar/editar; `excluir.php` só POST + cabeçalho `X-CSRF-Token` (403/405/400/500; `util.js` só recarrega com 200). |
| 2.7 a 2.8 | Não iniciados. |

**Testes da Tarefa 05 (sem tocar no banco real):** `php -l`, `node --check`, 18 testes unitários (`e`, `js_str`, token), análise DOM das páginas renderizadas com dados hostis (nenhum atributo `on*`, nenhum `<script>` injetado) e requisições HTTP contra uma **cópia isolada com banco simulado** (o simulado só registra as chamadas de escrita): 13 inválidas (GET, sem token, token errado/array/de outra sessão/só na URL, sem cookie, com upload) → 403/405 e **nenhuma escrita nem arquivo gravado**; 6 com token válido (excluir 200, id inválido 400, falha simulada 500, cadastrar e editar 200) → chamadas registradas como esperado. **Não testado:** gravação real no banco, `fetch` no navegador (modal, reload, aviso de erro) e leitura real com os novos escapes — validar manualmente (lista no relatório da tarefa).

Decisões de implementação (Tarefa 05):
- `inc/sessao.php` concentra `e()`, `js_str()` e o CSRF; `index.php` inclui o arquivo só para usar `e()` (continua sem sessão).
- Token: `random_bytes(32)` em hex, um por sessão, comparado com `hash_equals`; sem `random_bytes` o sistema responde 500 (sem fallback fraco). O token vai no campo oculto do formulário ou no cabeçalho do `fetch` (lido de `data-csrf` no botão do modal), nunca na URL.
- Busca em `times.php` é POST de leitura: sem token e o termo não é reexibido. `editar.php` valida o token no topo (antes do upload); `add()` e `edit()` em `function.php` também.
- `delete()` agora retorna `true/false`; em falha `excluir.php` descarta a mensagem flash (o `fetch` não a exibe) e o modal mostra um aviso.
- Não alterado (fora do escopo): upload pelo nome original (2.7), validação no servidor (2.8; `editar.php` ainda falha se `divisao`/`nome` vierem como array).

**Testes da Tarefa 04:** verificação estática e 44 testes unitários sem banco (listas permitidas, ids, filtro de campos, montagem da SQL); páginas com o banco desligado (erros genéricos, sem vazamento); leitura no banco real (listagem, visualização, busca com 12 payloads de injeção, `%`/`_` literais), com contagem e `CHECKSUM` da tabela idênticos antes e depois; `INSERT/UPDATE/DELETE` apenas **preparados** (nunca executados). **Não executados:** INSERT/UPDATE/DELETE reais e os fluxos cadastrar/editar/excluir com sucesso, por não existir base de testes descartável — repetir quando houver uma (ex.: `banco_teste`), antes da Etapa 5.

Decisões de implementação (Tarefa 04):
- Mensagens de erro do banco passam a ser genéricas ("Não foi possível realizar a operação."); o detalhe técnico vai para `error_log()`. Falha de conexão também é capturada em `save/update/remove` (antes era erro fatal).
- `find()` com id inválido (`abc`, `1 OR 1=1`, `0`) retorna `null` ("não encontrado"); o filtro `LIKE` é parametrizado e `%`, `_` e `|` digitados valem como texto comum (`ESCAPE '|'`).
- `save/update/remove` agora retornam `true/false` (os chamadores atuais ignoram o retorno).
- Leitura de resultados sem `get_result()` (não depende de `mysqlnd`, ainda não verificado na hospedagem). `bind_param(..., ...$array)` exige PHP ≥ 5.6.
- `index.php` mantém suas 2 consultas fixas (sem valores externos); não precisou de alteração.
- Limitação conhecida (item 2.8): o servidor ainda não valida campos obrigatórios; um POST sem `nome` pode gravar registro vazio.

Decisões de implementação (Tarefa 03):
- Cookie `crudsoccer` com `HttpOnly`, `SameSite=Lax`, `Secure` só em HTTPS (inclui `X-Forwarded-Proto`), `use_strict_mode`; configurado via `ini_set` (funciona em PHP < 7.3).
- `times/function.php::delete()` **não redireciona mais**: `excluir.php` é chamado por `fetch` e o `util.js` recarrega a página; o redirect faria o fetch carregar outra página que poderia consumir a mensagem.
- `editar.php` agora consome a mensagem de `update()` e mostra o erro (alerta vermelho) quando a atualização falha; antes mostrava "Atualizado com sucesso!" mesmo com erro. `visualizar.php` descarta mensagens que não exibe.
- `index.php` não usa sessão (não exibe nem grava mensagens).

**Preparar `config.php` em ambiente novo:** copiar `config.example.php` para `config.php`, trocar banco/usuário/senha/host e `BASEURL` (`/` em produção). Em produção, nunca sobrescrever o `config.php` do servidor.

**Atenção ao retirar `config.php` do Git:** depois que o commit com a remoção for aplicado, quem fizer `git pull` (ex.: o outro integrante da dupla) terá o `config.php` local **apagado pelo Git**. Cada pessoa deve guardar uma cópia do seu `config.php` antes do pull e recolocá-la depois. O histórico antigo continua contendo apenas os valores locais de desenvolvimento (sem credenciais reais).

---

## 1. Situação do Git (verificada)

`git status`: `assets/css/style.css` e `tabelaTime.sql` modificados (preservados, não commitados). Remoto: `origin` → GitHub, branch `master`.

| Arquivo | O que mudou | Interfere nas próximas etapas? |
|---|---|---|
| `assets/css/style.css` | (a) Variáveis CSS renomeadas (`--verde`→`--verde-gramado`, `--dourado`→`--dourado-trofeu`, `--cinza-bg`→`--fundo-neutro` etc.; 34 usos) e paleta levemente ajustada. (b) Hero: `hero-futebol.png` → `hero-futebol.jpg`. (c) Dois gradientes reformatados. | **Sim, em dois pontos:** `times/editar.php:121` ainda usa `var(--dourado)` (não existe mais → cabeçalho do formulário de edição perde o fundo); e o último commit renomeou a imagem para `.jpg` enquanto o CSS commitado ainda aponta para `.png` — o item (b) corrige isso. Telas novas devem usar os nomes novos das variáveis. Aviso de fim de linha (LF/CRLF) sem `.gitattributes`, sem impacto funcional. |
| `tabelaTime.sql` | Adiciona rascunho da tabela `usuarios` (`id`, `nome`, `user`, `password varchar(255)`, `foto`) e 3 inserts com "senhas" em texto puro. | **Sim:** não tem e-mail nem nível de acesso, tem `foto` desnecessária, os valores não são hashes (nunca passariam em `password_verify`) e o `CREATE TABLE` não é idempotente. **Não deve ir para produção nem ser commitado como está** — será substituído na Etapa 2. |

**Credenciais no histórico:** os 3 commits foram verificados. `config.php` contém apenas valores locais de desenvolvimento (senha vazia, host local); **nenhuma credencial real de produção foi encontrada no histórico**, então não há o que rotacionar. O risco é futuro: se alguém colocar a senha de produção em `config.php` e commitar. Ver 2.1.

---

## 2. Problemas de segurança → solução simples, por prioridade e dependência

A ordem abaixo é a ordem de execução da Etapa 1 (cada item depende dos anteriores quando indicado).

| # | Problema | Solução simples | Depende de |
|---|---|---|---|
| 2.1 | *(parcial — ver Progresso)* Credenciais podem ir para o Git (`config.php` versionado, sem `.gitignore`) | Criar `config.example.php` (valores fictícios, versionado) e colocar `config.php` no `.gitignore` (`git rm --cached config.php`, sem apagar o arquivo local). Em produção, `config.php` fica só no servidor e **nunca é sobrescrito no upload** **(aprovar)** | — |
| 2.2 | *(feito)* Sessão inexistente: `$_SESSION` usado sem `session_start()`; mensagens perdem-se em redirects | Criar `inc/sessao.php`: `session_start()` com cookie `httponly`, `samesite=Lax` e `secure` quando houver HTTPS; incluir em `config.php`/`function.php`. **Junto**, criar helper de mensagem "flash" (`definir_msg()`/`exibir_msg()`) que **apaga a mensagem após exibir** — sem isso, ativar a sessão faria alertas antigos reaparecerem em outras páginas | — |
| 2.3 | *(feito)* SQL Injection (concatenação em `find`, `save`, `update`, `remove`) | Reescrever `inc/database.php` com `prepare()`/`bind_param()`/`execute()`, **mantendo as assinaturas** das funções atuais para não quebrar times. `id` sempre convertido para `int`. Filtro de busca: `LIKE ?` com `"%$filtro%"` como parâmetro | — |
| 2.4 | *(feito)* Mass assignment (`$_POST['time']` inteiro vira colunas do INSERT/UPDATE) | Em `database.php`, lista fixa de tabelas e colunas permitidas (array simples por tabela, ex.: `tabela_time` → `nome, dataFundacao, estado, divisao, dataCadastro, foto`); colunas fora da lista são ignoradas. Nomes de coluna nunca vêm do usuário | 2.3 |
| 2.5 | XSS (saída sem escape; `editar.php` injeta valores dentro de `<script>`) | Helper `e($v)` = `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` em `inc/`; aplicar em `times.php`, `visualizar.php`, `editar.php` (atributos `value`), `index.php` (último cadastrado) e nos alertas. Em `<script>` usar `json_encode()`. Não exibir erro cru do MySQL ao usuário (registrar com `error_log()` e mostrar mensagem genérica) | 2.2 (mensagens) |
| 2.6 | Sem CSRF | Token na sessão (`csrf_token()`), campo oculto em todo formulário POST e `csrf_verificar()` no início do processamento (cadastrar, editar, excluir, login, usuários, jogadores). `excluir.php` passa a aceitar só POST + token; o token é enviado pelo `fetch` de `util.js` (ex.: `data-csrf` no modal). Responder com código HTTP adequado (hoje responde 200 mesmo em falha) | 2.2 |
| 2.7 | Upload inseguro (nome original, sem validação, sobrescreve) | Função única de upload — ver seção 5. Aplicar em times (novos uploads) e jogadores | — |
| 2.8 | Validação só no cliente | Validar no servidor: `nome` obrigatório e ≤ 50; `dataFundacao` data válida entre 1900-01-01 e hoje; `estado` dentro da lista de estados; `divisao` ∈ {A,B,C,D}; `id` inteiro. Lista de estados/séries duplicada em PHP (hoje só existe em `data.js`) | 2.3 |
| 2.9 | Sem autenticação/autorização | Etapas 2 e 3 | 2.2, 2.3, 2.5, 2.6 |

Observação: o `base64` nos IDs pode permanecer (não quebra links), mas **não é proteção**: sempre validar `is_numeric`/`int` depois de decodificar e checar permissão no servidor.

---

## 3. Banco de dados — proposta

**Estado atual:** `tabela_time` (`id int` AUTO_INCREMENT PK, `nome varchar(50)`, `dataFundacao date`, `estado varchar(40)`, `divisao char(1)`, `dataCadastro datetime`, `foto varchar(50)`), charset `utf8`, sem `ENGINE` explícito e sem chaves estrangeiras. Rascunho de `usuarios` descrito na seção 1.

### `usuarios` (proposta — estrutura definitiva a aprovar)
| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| `id` | `int` AUTO_INCREMENT, PK | Sim | |
| `nome` | `varchar(100)` | Sim | |
| `email` | `varchar(100)` com `UNIQUE` | Sim | identificador de login |
| `senha_hash` | `varchar(255)` | Sim | resultado de `password_hash()`; nunca a senha |
| `nivel` | `enum('admin','comum')` default `'comum'` | Sim | |

Sem campo `foto` e sem campos extras (ativo, último login etc.) — só se forem pedidos depois.

### `jogadores` (somente os campos aprovados)
| Campo | Tipo | Obrigatório |
|---|---|---|
| `id` | `int` AUTO_INCREMENT, PK | Sim |
| `nome` | `varchar(100)` | Sim |
| `time_id` | `int` (mesmo tipo de `tabela_time.id`, com sinal) | Sim |
| `numero_camisa` | `tinyint unsigned` | Não (`NULL`) |
| `data_nascimento` | `date` | Não (`NULL`) |
| `foto` | `varchar(50)` | Não (`NULL`) — só o nome do arquivo |

Chave estrangeira: `FOREIGN KEY (time_id) REFERENCES tabela_time(id)`. Para a FK funcionar, as duas tabelas precisam ser **InnoDB** e a coluna `time_id` ter exatamente o mesmo tipo de `tabela_time.id` (`int` com sinal). Como `tabela_time` não declara o engine, isso precisa ser **verificado** no ambiente local e na hospedagem (seção 6).

**Como jogadores se relacionam com times (texto simples):** cada jogador pertence a exatamente um time, guardado em `time_id`. Um time pode ter vários jogadores. O banco impede salvar um jogador com `time_id` inexistente. Na listagem/visualização do jogador, o nome do time vem de uma consulta com `JOIN` em `tabela_time`; no cadastro/edição, o time é escolhido em um `<select>` preenchido com os times existentes. O comportamento ao excluir um time que tem jogadores depende da regra de `ON DELETE` **(aprovar)**.

Onde guardar o SQL: manter no `tabelaTime.sql` (convenção atual) usando `CREATE TABLE IF NOT EXISTS`. Em produção, executar **apenas** os comandos novos pelo phpMyAdmin (o script atual tem `create database`/`use`, que costuma não funcionar em hospedagem compartilhada).

---

## 4. Autenticação — abordagem proposta (PHP procedural)

Arquivos novos, mínimos: `inc/sessao.php` (sessão, flash, CSRF), `inc/auth.php` (funções de login/permissão), `login.php` e `logout.php` na raiz, pasta `usuarios/` seguindo o padrão de `times/`.

- **Login (`login.php`):** busca o usuário por e-mail com prepared statement; `password_verify()`; em caso de sucesso, `session_regenerate_id(true)` e guarda apenas o `id` do usuário na sessão. Mensagem de erro genérica ("e-mail ou senha inválidos") para os dois casos. Sem recuperação de senha e sem bloqueio por tentativas (fora do escopo; admin redefine a senha).
- **Logout (`logout.php`):** só por POST + token CSRF; limpa `$_SESSION`, expira o cookie e `session_destroy()`.
- **Usuário autenticado:** `usuario_logado()` lê o `id` da sessão e busca nome/nível **no banco a cada requisição protegida** (1 consulta simples). Assim, usuário excluído ou rebaixado perde o acesso imediatamente.
- **Guardas (chamadas no topo da página, antes de qualquer saída):**
  - `exigir_login()` → redireciona para `login.php` se não autenticado (usada em `cadastrar.php`, `editar.php`, `excluir.php` de times e jogadores).
  - `exigir_admin()` → bloqueia (403/redirecionamento) quem não for `admin` (usada em todo `usuarios/`).
  - `eh_admin()` / `esta_logado()` → apenas para a interface (navbar, botões). A segurança real é sempre o guarda no PHP.
- **Páginas públicas:** `index.php`, `times/times.php`, `times/visualizar.php` e equivalentes de jogadores. Botões Editar/Apagar/Incluir só aparecem logado, mas as páginas de escrita se protegem sozinhas.
- **Primeiro administrador:** recomendação — gerar o hash localmente (`php -r "echo password_hash('SENHA', PASSWORD_DEFAULT);"`) e inserir o registro via phpMyAdmin/SQL, **sem script no servidor** e sem senha ou hash no Git **(aprovar)**.
- **Gerenciamento de usuários (Etapa 3):** CRUD em `usuarios/` (apenas admin). Na edição, senha em branco mantém a atual. Sem cadastro público.

---

## 5. Upload de fotografias

**Hoje (times):** `add()` e `editar.php` usam `move_uploaded_file()` com `basename($_FILES[..]['name'])` direto em `assets/img/`: sem validar tipo/tamanho, nome original (sobrescreve, aceita `.php`), sem remoção da imagem antiga. Colunas `foto` guardam só o nome do arquivo (padrão a manter). Sem foto → `placeholder-time.png`.

**Proposta (uma função compartilhada, ex.: `salvar_imagem($campo, $pasta, $prefixo)` em `inc/upload.php`), usada por times e jogadores:**
1. Aceitar só `UPLOAD_ERR_OK`; `UPLOAD_ERR_NO_FILE` = sem imagem (mantém regra atual).
2. Tamanho máximo (ex.: 2 MB, abaixo do limite da hospedagem — verificar).
3. Verificar o tipo **real** com `finfo` (não pela extensão/`$_FILES['type']`) e `getimagesize()`: permitir só JPEG, PNG e WebP (e GIF se desejado). Não depender da extensão GD (está desativada no XAMPP local).
4. Nome novo gerado pelo servidor: prefixo + `bin2hex(random_bytes(8))` + extensão definida pelo tipo detectado (ex.: `jogador_3f9a….jpg`). Nunca usar o nome enviado. Cabe em `varchar(50)`.
5. Salvar jogadores em `assets/img/jogadores/` (pasta própria, a criar e conferir permissão de escrita em produção). Times continuam em `assets/img/`.
6. Validar a foto **antes** de gravar no banco; se o INSERT/UPDATE falhar, apagar o arquivo recém-enviado.
7. **Exclusão/substituição:** ao trocar a foto ou excluir o jogador, apagar o arquivo antigo com `unlink()` somente se não for vazio/placeholder e se estiver na pasta de jogadores (validar com `basename()`) **(aprovar)**. Para times, recomenda-se não apagar imagens antigas automaticamente (os arquivos `miniatura_*` do script inicial não devem ser removidos).
8. Preview no formulário continua com `formulario.js` (já existente).

---

## 6. Compatibilidade com a InfinityFree — a verificar (nada comprovado)

Nenhuma informação do ambiente de produção está no repositório. Verificar antes da implantação e registrar o resultado aqui:

- [ ] Versão do **PHP** (painel/`phpinfo()` temporário — remover depois). Código novo deve usar só recursos disponíveis nela.
- [ ] Versão do **MySQL/MariaDB** (rodapé do phpMyAdmin).
- [ ] **Extensões:** `mysqli` (e se há `mysqlnd`, necessário para `get_result()`; senão usar `bind_result`), `fileinfo` (para `finfo`), `mbstring`.
- [ ] **Engine InnoDB** nas tabelas (`SHOW TABLE STATUS`) — necessário para a chave estrangeira.
- [ ] **Limites de upload:** `upload_max_filesize` e `post_max_size`.
- [ ] **Diretório das imagens:** criar `assets/img/jogadores/` e confirmar permissão de escrita (o upload de times já grava em `assets/img/`).
- [ ] **Credenciais em produção:** host, nome do banco, usuário e senha de produção (diferentes dos locais); `BASEURL` como `"/"`; como `config.php` é mantido no servidor sem ser sobrescrito.
- [ ] **HTTPS** ativo (define se o cookie de sessão pode usar `secure`) e se a sessão PHP grava em diretório válido.
- [ ] Se a proteção anti-bot da hospedagem interfere em `fetch`/POST (`excluir.php`).
- [ ] Se `.htaccess` é respeitado (não depender dele para a segurança dos uploads).
- [ ] Processo de deploy (provavelmente upload manual/FTP) e distinção de maiúsculas/minúsculas em nomes de arquivos (Linux).

---

## 7. Pendências para aprovação (com recomendação)

| # | Decisão | Recomendação simples |
|---|---|---|
| 1 | Criação do primeiro administrador | Hash gerado localmente + `INSERT` manual no phpMyAdmin; sem script no servidor |
| 2 | Identificador de login | **E-mail** (`UNIQUE`), como proposto |
| 3 | Estrutura definitiva de `usuarios` | A da seção 3 (`id`, `nome`, `email`, `senha_hash`, `nivel`). Opcional, para evitar bloqueio total: admin não pode excluir a si mesmo nem rebaixar/excluir o último administrador |
| 4 | Rascunho atual de `usuarios` (senhas em texto puro) | Descartar: substituir no `tabelaTime.sql` na Etapa 2; nunca importar em produção |
| 5 | Número de camisa repetido no mesmo time | Proibir repetição com `UNIQUE (time_id, numero_camisa)` (valores `NULL` repetem à vontade no MySQL). Alternativa: permitir repetição |
| 6 | Excluir time que possui jogadores | **Bloquear** (`ON DELETE RESTRICT`) com mensagem "remova os jogadores antes". Alternativa: `CASCADE` (apaga jogadores e exige apagar as fotos) |
| 7 | Foto na substituição/exclusão do jogador | Apagar o arquivo antigo (seção 5, item 7) |
| 8 | Versões e limites da hospedagem | Executar checklist da seção 6 antes da Etapa 5 (idealmente antes da Etapa 1) |
| 9 | Credenciais de produção | `config.example.php` versionado + `config.php` no `.gitignore` e mantido só no servidor |
| 10 | Alterações locais não commitadas | `style.css`: commitar (junto da correção de `editar.php:121`, na Etapa 1). `tabelaTime.sql`: não commitar o rascunho de `usuarios`; reverter só essa parte quando for substituída (decisão do responsável — nada foi descartado) |

---

## 8. Etapas

### Etapa 1 — Correções de segurança
**Objetivo:** tornar o CRUD de times seguro antes de acrescentar login e jogadores, sem mudar seu comportamento visível.
**Atividades:** resolver o item 10 (alterações locais) e corrigir `var(--dourado)` em `editar.php`; proteger credenciais (2.1); sessão + mensagens flash (2.2); prepared statements e lista de colunas permitidas em `inc/database.php` (2.3, 2.4); helper `e()` e escape em todas as páginas (2.5); CSRF em formulários e na exclusão (2.6); função de upload seguro aplicada a times (2.7); validação no servidor (2.8). Correções de passagem: `exit` após `header()`, links "Voltar" quebrados (`index.php` relativo em `times/`), `inc/footer.php` inexistente.
**Concluída quando:** nenhuma consulta com entrada externa é concatenada; todo dado dinâmico impresso é escapado; POSTs sem token válido são rejeitados; uploads inválidos são recusados e salvos com nome gerado; `config.php` não está mais no Git; todas as operações de times funcionam como antes.

### Etapa 2 — Autenticação
**Objetivo:** identificar quem está usando o sistema.
**Atividades:** tabela `usuarios` definitiva (seção 3) em `tabelaTime.sql`; criar o primeiro administrador (item 1); `login.php`, `logout.php`, `inc/auth.php`; proteger com `exigir_login()` as páginas de escrita de times; navbar com Entrar/Sair e nome do usuário; visitante continua listando e visualizando.
**Concluída quando:** login e logout funcionam; senha só existe como hash; visitante é redirecionado ao tentar `cadastrar.php`, `editar.php` ou `excluir.php` (inclusive por requisição direta); sessão é renovada no login.

### Etapa 3 — Gerenciamento de usuários e permissões
**Objetivo:** permitir que o administrador gerencie os acessos.
**Atividades:** `exigir_admin()`; CRUD em `usuarios/` (listar, visualizar, cadastrar, editar, excluir) com senha via `password_hash()`; e-mail único; item de menu "Usuários" só para admin; restrições administrativas conforme item 3.
**Concluída quando:** usuário comum e visitante não acessam nenhuma página/endpoint de `usuarios/` (testado por URL direta e POST); o admin consegue gerir usuários; e-mail duplicado é recusado.

### Etapa 4 — CRUD de jogadores
**Objetivo:** cadastrar e consultar jogadores vinculados aos times.
**Atividades:** criar `jogadores` com a FK (seção 3); pasta `jogadores/` seguindo o padrão de `times/` (`function.php`, listagem, cadastrar, editar, visualizar, exclusão por POST); `<select>` de times; listagem/visualização com nome do time; upload de foto (seção 5); listagem e visualização públicas, escrita com `exigir_login()` + CSRF; links para jogadores na navbar e, se simples, na tela do time; tratamento da exclusão de time com jogadores (item 6); regra de camisa repetida (item 5).
**Concluída quando:** CRUD completo funciona; `time_id` inválido é recusado pelo servidor e pelo banco; foto é opcional e `NULL` quando ausente; arquivos antigos são tratados conforme o item 7; visitante só lê.

### Etapa 5 — Testes e implantação
**Objetivo:** validar tudo e publicar com segurança.
**Atividades (manuais, sem framework de testes):**
- Funcional: fluxo completo de times, usuários e jogadores.
- Permissões: repetir cada ação como visitante, comum e admin, inclusive por URL/POST direto.
- Segurança: aspas e `<script>` nos campos, POST sem token, upload de arquivo `.php`/falso `.jpg`/grande demais, ID inexistente ou inválido.
- Regressão de times: listar, buscar, cadastrar (com e sem imagem), visualizar, editar, excluir.
- Hospedagem: checklist da seção 6; importar só as tabelas novas; manter o `config.php` de produção; criar `assets/img/jogadores/`; testar login, sessão e uploads em produção; remover arquivos temporários de diagnóstico.
**Concluída quando:** todos os testes passam local e em produção, sem alterar credenciais nem sobrescrever o `config.php` do servidor. Commits e deploy só mediante pedido explícito.
