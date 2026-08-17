    </main>

    <footer class="footer">
      <span>&copy; 2026 BiblioTrack. Todos los derechos reservados.</span>
    </footer>

    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
    <script src="js/menu.js"></script>
    <?php foreach ($pageJs ?? [] as $script): ?>
      <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
