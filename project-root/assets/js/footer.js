const footerPlaceholder = document.getElementById('footer-placeholder');

if (footerPlaceholder) {
  fetch(new URL('../include/footer.html', document.currentScript.src))
    .then((res) => res.text())
    .then((html) => {
      footerPlaceholder.innerHTML = html;
    });
}