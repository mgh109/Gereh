document.querySelectorAll('[data-admin-filter]').forEach(input => {
  const form = input.closest('form');
  input.addEventListener('change', () => form.requestSubmit());
});
// Client-side filters apply only to lists that are fully loaded by the server.
document.querySelectorAll('[data-local-filter]').forEach(form => {
  const apply = () => {
    const query = (form.querySelector('[name="q"]')?.value || '').trim().toLocaleLowerCase('fa');
    const status = form.querySelector('[name="status"]')?.value || '';
    let count = 0;
    document.querySelectorAll('[data-filter-row]').forEach(row => {
      row.hidden = !((!query || row.textContent.toLocaleLowerCase('fa').includes(query)) && (!status || row.dataset.status === status));
      if (!row.hidden) count++;
    });
    const empty = document.querySelector('[data-filter-empty]');
    if (empty) empty.hidden = count > 0;
  };
  form.addEventListener('submit', event => { event.preventDefault(); apply(); });
  form.addEventListener('input', apply);
  form.addEventListener('change', apply);
});
