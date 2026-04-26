document.addEventListener('invalid', function (event) {
  var field = event.target;
  if (field && field.setAttribute) {
    field.setAttribute('aria-invalid', 'true');
  }
}, true);
