const ESTADOS_BR = [
  "Acre", "Alagoas", "Amapá", "Amazonas", "Bahia", "Ceará", "Distrito Federal",
  "Espírito Santo", "Goiás", "Maranhão", "Mato Grosso", "Mato Grosso do Sul",
  "Minas Gerais", "Pará", "Paraíba", "Paraná", "Pernambuco", "Piauí",
  "Rio de Janeiro", "Rio Grande do Norte", "Rio Grande do Sul", "Rondônia",
  "Roraima", "Santa Catarina", "São Paulo", "Sergipe", "Tocantins"
];

const CATEGORIAS = [
  "Série A", "Série B", "Série C", "Série D"
]

function preencherEstados(selectEl, selecionado) {
  ESTADOS_BR.forEach(uf => {
    const opt = document.createElement("option");
    opt.value = uf;
    opt.textContent = uf;
    if (uf === selecionado) opt.selected = true;
    selectEl.appendChild(opt);
  });
}

function preencherCategorias(selectEl, selecionado) {
  CATEGORIAS.forEach(cat => {
    const opt = document.createElement('option');
    opt.value = cat;
    opt.textContent = cat;
    if (cat === selecionado) opt.selected = true;
    selectEl.appendChild(opt);
  })
}