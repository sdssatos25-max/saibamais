
// Gráfico de acessos por dispositivo
const ctx = document.getElementById('accessChart').getContext('2d');
const mobileCount = rawData.filter(log => log.device === 'MOBILE').length;
const desktopCount = rawData.filter(log => log.device === 'DESKTOP').length;
const chart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ['Mobile', 'Desktop'],
    datasets: [{
      label: 'Dispositivos',
      data: [mobileCount, desktopCount],
      backgroundColor: ['#03dac5', '#bb86fc']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { labels: { color: '#e0e0e0' } }
    }
  }
});

// Formulário para salvar URLs
document.getElementById('urlForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const offer = document.getElementById('offer_url').value;
  const fake = document.getElementById('fake_url').value;

  const res = await fetch('save_config.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ offer_url: offer, fake_url: fake })
  });
  const result = await res.text();
  document.getElementById('formStatus').textContent = result;
});
