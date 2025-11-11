// Função para alterar o favicon com base no modo do navegador
function updateFavicon() {
  const isDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const favicon = document.getElementById("favicon");
  favicon.href = isDarkMode
    ? "./assets/data/images/LogoWhite.svg"
    : "./assets/data/images/LogoBlack.svg";
}

// Atualiza o favicon ao carregar a página
updateFavicon();

// Adiciona um listener para detectar mudanças no modo do navegador
window
  .matchMedia("(prefers-color-scheme: dark)")
  .addEventListener("change", updateFavicon);
