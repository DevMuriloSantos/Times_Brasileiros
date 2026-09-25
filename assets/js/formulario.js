document.addEventListener("DOMContentLoaded", () => {
  const inputImagem = document.getElementById("imagem");
  const previewImg = document.getElementById("previewImg");
  let imagemBase64 = null;

  if (inputImagem) {
    inputImagem.addEventListener("change", (e) => {
      const arquivo = e.target.files[0];
      if (arquivo) {
        const reader = new FileReader();
        reader.onload = (ev) => {
          imagemBase64 = ev.target.result;
          previewImg.src = imagemBase64;
        };
        reader.readAsDataURL(arquivo);
      }
    });
  }

  (() => {
    "use strict";

    const forms = document.querySelectorAll(".needs-validation");

    // Loop over them and prevent submission
    Array.from(forms).forEach((form) => {
      form.addEventListener(
        "submit",
        (event) => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }

          form.classList.add("was-validated");
        },
        false,
      );
    });
  })();
});
