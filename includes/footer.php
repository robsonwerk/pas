</main> <footer class="text-center text-muted py-4">
    <p>&copy; <?php echo date('Y'); ?> Programa Alimento Seguro</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

<link rel="manifest" href="manifest.json">

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js')
        .then((reg) => console.log('Service Worker registrado com sucesso! Escopo:', reg.scope))
        .catch((err) => console.error('Falha ao registrar o Service Worker:', err));
    });
  }
</script>
</html>