const icons = () => {};
const modal = document.querySelector('#tripModal');
const toast = document.querySelector('#toast');
const currencyText = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
while (currencyText.nextNode()) currencyText.currentNode.nodeValue = currencyText.currentNode.nodeValue.replaceAll('€', 'Rs ');
const openModal = () => { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); document.querySelector('#tripName').focus(); };
const closeModal = () => { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); };
document.querySelector('#newTripButton').addEventListener('click', openModal);
document.querySelector('#closeModal').addEventListener('click', closeModal);
modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeModal(); });
document.querySelector('#tripForm').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.target;
  const submitButton = form.querySelector('button[type="submit"]');
  const formData = new FormData(form);
  submitButton.disabled = true;
  try {
    const response = await fetch('api/trips.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: formData.get('name'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        destination: formData.get('destination')
      })
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Could not save trip');
    closeModal();
    toast.querySelector('span').textContent = 'Trip saved to the database.';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
    form.reset();
  } catch (error) {
    toast.querySelector('span').textContent = error.message + ' Start Apache and MySQL in XAMPP.';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4500);
  } finally {
    submitButton.disabled = false;
  }
});
document.querySelectorAll('.save-button').forEach(button => button.addEventListener('click', () => {
  button.classList.toggle('saved');
  button.setAttribute('aria-label', button.classList.contains('saved') ? 'Remove bookmark' : 'Save destination');
}));
document.querySelectorAll('[data-view]').forEach(item => item.addEventListener('click', () => {
  document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
  const matchingNav = document.querySelector(`.nav-item[data-view="${item.dataset.view}"]`);
  if (matchingNav) matchingNav.classList.add('active');
  const label = item.dataset.view === 'dashboard' ? 'Overview' : item.dataset.view[0].toUpperCase() + item.dataset.view.slice(1);
  document.querySelector('.breadcrumbs strong').textContent = label;
  if (window.innerWidth < 620) document.querySelector('.sidebar').classList.remove('open');
}));
document.querySelector('.mobile-menu').addEventListener('click', () => document.querySelector('.sidebar').classList.toggle('open'));
document.querySelector('#openItinerary').addEventListener('click', () => {
  document.querySelector('.breadcrumbs strong').textContent = 'Itinerary';
  document.querySelector('.topbar').scrollIntoView({ behavior: 'smooth' });
  toast.querySelector('span').textContent = 'Itinerary view is ready for your edits.';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
});
icons();
