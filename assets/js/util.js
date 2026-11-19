let modalExcluir = null;
let idExcluir = null;

document.addEventListener("DOMContentLoaded", () => {
  const ano = document.getElementById("ano");
  const elementoModal = document.getElementById("modalExcluir");

  if (ano) {
    ano.textContent = new Date().getFullYear();
  }

  if (elementoModal) {
    modalExcluir = new bootstrap.Modal(elementoModal);
  }
});

function abrirModalExcluir(nome, id) {
  document.getElementById("nomeExcluir").innerText = nome;
  mostrarErroExcluir("");
  idExcluir = id;

  const modal = new bootstrap.Modal(document.getElementById("modalExcluir"));

  modal.show();
}

// Exibe (ou, com texto vazio, esconde) o aviso de erro dentro do modal de exclusão.
function mostrarErroExcluir(texto) {
  const aviso = document.getElementById("erroExcluir");

  if (aviso) {
    aviso.textContent = texto;
    aviso.classList.toggle("d-none", texto === "");
  }
}

// Botão "Apagar" de cada linha: o nome e o id vêm de data-* (sem JavaScript inline na página).
document.addEventListener("click", (event) => {
  const botaoApagar = event.target.closest(".btn-apagar[data-id]");

  if (botaoApagar) {
    abrirModalExcluir(botaoApagar.dataset.nome, botaoApagar.dataset.id);
  }
});

const botaoConfirmarExclusao = document.getElementById("btnConfirmarExclusao");

if (botaoConfirmarExclusao) {
  botaoConfirmarExclusao.addEventListener("click", async (event) => {
    event.preventDefault();
    const scriptUtil = document.querySelector('script[src*="util.js"]');
    const endpointExclusao = new URL("../../excluir.php", scriptUtil.src);

    try {
      const resposta = await fetch(endpointExclusao, {
        method: "POST",
        headers: {
          // token CSRF da sessão (data-csrf no botão); o servidor rejeita com 403 se faltar/for inválido
          "X-CSRF-Token": botaoConfirmarExclusao.dataset.csrf || "",
        },
        body: new URLSearchParams({ id: idExcluir }),
      });

      if (resposta.ok) {
        location.reload();
      } else if (resposta.status === 403) {
        mostrarErroExcluir("Sua sessão expirou. Recarregue a página e tente novamente.");
      } else {
        mostrarErroExcluir("Não foi possível excluir o time. Tente novamente.");
      }
    } catch (erro) {
      mostrarErroExcluir("Falha de comunicação com o servidor. Tente novamente.");
    }
  });
}
