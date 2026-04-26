document.addEventListener('click', function (event) {
  var target = event.target.closest('[data-seeder-focus-main]');
  if (!target) return;
  var main = document.getElementById('main-body');
  if (main) main.focus();
});
