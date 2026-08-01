    </main>

    <footer class="footer">
      <span>&copy; 2026 BiblioTrack. Todos los derechos reservados.</span>
    </footer>

    <script src="js/menu.js"></script>
    <?php foreach ($pageJs ?? [] as $script): ?>
      <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
