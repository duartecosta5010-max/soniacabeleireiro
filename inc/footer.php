<?php
if (!defined('ROOT_PATH')) {
  require_once __DIR__ . '/../config.php';
}
?>

    </main>


    <footer class="site-footer">
      <div class="footer-top">
        <div class="container footer-grid">

          <div class="footer-col">
            <h4 class="footer-title">Contactos</h4>
            <ul class="footer-list">
              <li>
                <span class="footer-muted">Morada</span><br>
                Rua D. Manuel Correia Ramalho, Lote 6, Loja N<br>
                2070-095 Cartaxo
              </li>
              <li>
                <span class="footer-muted">Telefone</span><br>
                <a class="footer-link" href="tel:+351933148593">+351 933 148 593</a>
              </li>
              <li>
                <span class="footer-muted">Email</span><br>
                <a class="footer-link" href="mailto:soniarego78@hotmail.com">soniarego78@hotmail.com</a>
              </li>
            </ul>
          </div>

          <div class="footer-col">
            <h4 class="footer-title">Horário</h4>
            <ul class="footer-list">
              <li>Ter–Sex: <span class="footer-muted">09:00 – 18:00</span></li>
              <li>Sábado: <span class="footer-muted">09:00 – 13:00</span></li>
              <li>Domingo: <span class="footer-muted">Encerrado</span></li>
              <li>Segunda: <span class="footer-muted">Encerrado</span></li>
            </ul>
          </div>

          <!-- Coluna 3: Menu -->
          <div class="footer-col">
            <h4 class="footer-title">Menu</h4>
            <ul class="footer-list footer-links">
              <li><a class="footer-link" href="<?= BASE_URL ?>/index.php">Início</a></li>
              <li><a class="footer-link" href="<?= BASE_URL ?>/pages/servicos.php">Serviços</a></li>
              <li><a class="footer-link" href="<?= BASE_URL ?>/pages/produtos.php">Produtos</a></li>
              <li><a class="footer-link" href="<?= BASE_URL ?>/pages/sobre.php">Sobre nós</a></li>
              <li><a class="footer-link" href="<?= BASE_URL ?>/pages/blog.php">Blog</a></li>
              <li><a class="footer-link" href="<?= BASE_URL ?>/pages/contactos.php">Contactos / Marcações</a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4 class="footer-title">Legal</h4>
            <ul class="footer-list footer-links">
              <li><a class="footer-link" href="#" onclick="return false;">Aviso Legal</a></li>
              <li><a class="footer-link" href="#" onclick="return false;">Privacidade</a></li>
              <li><a class="footer-link" href="#" onclick="return false;">Cookies</a></li>
              <li><a class="footer-link" href="https://www.livroreclamacoes.pt/" target="_blank" rel="noopener">Livro de Reclamações</a></li>
              <li><a class="footer-link" href="https://www.consumidor.gov.pt/" target="_blank" rel="noopener">RAL</a></li>
            </ul>
          </div>

        </div>
      </div>

      <div class="footer-bottom">
        <div class="container footer-bottom-inner">
          <div class="footer-copy">
            <small>© <?= date('Y') ?> Sónia Cabeleireiro | Todos os direitos reservados</small>
          </div>

          <div class="footer-social" aria-label="Redes sociais">
            <a class="social-btn"
               href="https://www.instagram.com/sonia_cabeleireiro.1978/"
               target="_blank" rel="noopener"
               aria-label="Instagram" title="Instagram">IG</a>

            <a class="social-btn"
               href="https://www.facebook.com/share/1HkwHnCfdh/?mibextid=wwXIfr"
               target="_blank" rel="noopener"
               aria-label="Facebook" title="Facebook">FB</a>
          </div>

          <div class="footer-note">
            <small class="footer-muted">Cartaxo, Santarém, Portugal</small>
          </div>
        </div>
      </div>
    </footer>

    <?php
      $authDrawerPath = ROOT_PATH . '/inc/auth_drawer.php';
      if (file_exists($authDrawerPath)) {
        require_once $authDrawerPath;
      }
    ?>

    <!-- JS do site -->
    <script src="<?= BASE_URL ?>/assets/js/reveal.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/auth-drawer.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/before-after.js" defer></script>

  </body>
</html>