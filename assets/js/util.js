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
  idExcluir = id;

  const modal = new bootstrap.Modal(document.getElementById("modalExcluir"));

  modal.show();
}

const botaoConfirmarExclusao = document.getElementById("btnConfirmarExclusao");

if (botaoConfirmarExclusao) {
  botaoConfirmarExclusao.addEventListener("click", async (event) => {
    event.preventDefault();
    const scriptUtil = document.querySelector('script[src*="util.js"]');
    const endpointExclusao = new URL("../../excluir.php", scriptUtil.src);
    const resposta = await fetch(endpointExclusao, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "id=" + idExcluir,
    });

    if (resposta.ok) {
      location.reload();
    }
  });
}
