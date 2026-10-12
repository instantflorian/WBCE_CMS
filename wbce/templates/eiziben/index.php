<?php
/**
 * WBCE CMS Template: Schlank
 * index.php – Haupt-Template-Datei
 *
 * Blöcke:
 *   page_content(1) → Hauptinhalt (Mitte)
 *   page_content(2) → Sidebar (rechts, optional)
 *
 * Menü:
 *   show_menu2(1, ...) → Hauptnavigation (bis 3 Ebenen)
 *
 * Hero-Bild: Legen Sie eine Datei img/hero.jpg ins Template-Verzeichnis.
 * Seitenspezifisches Hero-Bild: Benennen Sie es nach der PAGE_ID,
 *   z.B. img/hero-5.jpg für Seite mit ID 5 – das Template wählt es automatisch.
 *
 * Sidebar ausblenden: Setzen Sie im Backend bei einer Seite das Metatag
 *   "keywords" auf "no-sidebar" ODER lassen Sie Block 2 leer –
 *   das Template blendet die Sidebar dann automatisch aus.
 */

// Sidebar-Inhalt erfassen, um zu prüfen ob sie befüllt ist
ob_start();
page_content(2);
$sidebar_content = ob_get_clean();
$has_sidebar = (trim(strip_tags($sidebar_content)) !== '');

// Hero-Bild ermitteln: seitenspezifisch > global > keins
$hero_page   = TEMPLATE_DIR . '/img/hero-' . PAGE_ID . '.jpg';
$hero_global = TEMPLATE_DIR . '/img/hero.jpg';
$hero_path_page   = WB_PATH . '/templates/' . TEMPLATE . '/img/hero-' . PAGE_ID . '.jpg';
$hero_path_global = WB_PATH . '/templates/' . TEMPLATE . '/img/hero.jpg';

if (file_exists($hero_path_page)) {
    $hero_src = $hero_page;
} elseif (file_exists($hero_path_global)) {
    $hero_src = $hero_global;
} else {
    $hero_src = '';
}

// show_menu2: Hauptnavigation, alle Ebenen, mit CSS-Klassen für JS-Toggle
$nav = show_menu2(
    1,           // Menü 1
    SM2_ROOT,    // ab Root
    SM2_ALL,     // alle Ebenen
    SM2_ALL | SM2_PRETTY | SM2_BUFFER,
    // Item-Open: class="menu-expand" bei Eltern-Items → JS-Toggle
    '<li class="[class][if(class==menu-expand){ has-children}]">'
    . '<a href="[url]" target="[target]">[menu_title]</a>',
    '</li>',
    '<ul class="sub-menu">',
    '</ul>',
    false,
    '<ul class="nav-list">'
);
?><!DOCTYPE html>
<html lang="<?php echo strtolower(LANGUAGE); ?>">
<head>
  <meta charset="<?php echo (defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php 
  /* Generieren von Metatags (Seitentitel, Sprache, Beschreibung) */
  simplepagehead(); ?>
  
  <?php 
  /* 
  Datenschutzfreundliches Laden der im Template verwendeten Schriften - diese werden nur von Google geladen, wenn sie noch nicht auf dem eigenen Webspace vorhanden sind
  Bereitstellung im Verzeichnis cache/fonts, eine CSS-Datei pro Schriftart
  */
  
  I::insertWebFont('https://fonts.googleapis.com/css2?family=Bitter:ital,wght@0,400;0,700;1,400','Bitter'); 
  I::insertWebFont('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap','Nunito'); 
  
  /*
  Zusammenfassen und Einbinden der templatespezifischen Stylesheets sowie der Schriftart-CSS 
  */
  
  I::insertCssBundle([
		'{CACHE}/fonts/bitter.css',
		'{CACHE}/fonts/nunito.css',
		'{TEMPLATE}/style.css',
  ],'eiziben');
  
  /*
  Laden der modulspezifischen CSS- und JS-Dateien
  Zur Optimierung der Ladegeschwindigkeit (Reduzierung von Serveranfragen) können diese auch mit in den insertCSSBundle / insertJSBundle-Aufruf integriert werden 
  */
 
  register_frontend_modfiles('css');
  register_frontend_modfiles('jquery');
  register_frontend_modfiles('js');
  ?>
</head>

<body class="<?php echo (LEVEL > 0 ? 'is-subpage' : 'is-root'); ?> page-<?php echo PAGE_ID; ?>">

<a class="skip-link" href="#main-content">Zum Inhalt springen</a>

<div class="site-wrapper">

  <!-- ===== HEADER / NAVIGATION ===== -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="nav-wrapper">

        <!-- Logo / Websitetitel -->
        <a href="<?php echo WB_URL; ?>/" class="site-logo" aria-label="Startseite">
          <?php echo WEBSITE_HEADER; ?>
        </a>

        <!-- Hamburger-Button (mobil) -->
        <button class="nav-toggle" aria-expanded="false" aria-controls="main-nav" aria-label="Navigation öffnen">
          <span></span><span></span><span></span>
        </button>

        <!-- Hauptnavigation (per show_menu2 generiert) -->
        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
          <?php echo $nav; ?>
        </nav>

      </div><!-- /.nav-wrapper -->
    </div><!-- /.container -->
  </header>

  <!-- ===== HERO ===== -->
  <?php 
  /* Hero-Image nur auf Seiten auf der oberesten Ebene und nur, wenn es dafür ein Bild gibt */
  if ($hero_src !== '' && LEVEL == 0) : ?>
  <section class="hero<?php echo ($hero_src === '' ? ' hero--placeholder' : ''); ?>" aria-label="Hero-Bereich">
    <?php if ($hero_src !== '') : ?>
      <img class="hero-image" src="<?php echo $hero_src; ?>" alt="">
    <?php else : ?>
      <div class="hero-image" role="presentation"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <div class="container">
        <h1 class="hero-title"><?php echo PAGE_TITLE; ?></h1>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===== BREADCRUMB ===== -->
  <?php if (LEVEL > 0) : ?>
  <nav class="breadcrumb-bar" aria-label="Brotkrümel-Navigation">
    <div class="container">
      <ol class="breadcrumb" role="list">
        <li><a href="<?php echo WB_URL; ?>/">Startseite</a></li>
        <?php
        show_menu2(
            1,
            SM2_ROOT + 1,
            SM2_ALL,
            SM2_CRUMB,
            '<li><span class="[class]"><a href="[url]">[menu_title]</a></span></li>',
            '',
            '',
            '',
            '<li><span class="[class]">[menu_title]</span></li>',
            false
        );
        ?>
      </ol>
    </div>
  </nav>
  <?php endif; ?>

  <!-- ===== CONTENT ===== -->
  <main class="content-area" id="main-content">
    <div class="container">

      <div class="content-grid<?php echo ($has_sidebar ? '' : ' no-sidebar'); ?>">

        <!-- Hauptinhalt (Block 1) -->
        <article class="main-column prose">
          <?php page_content(1); ?>
        </article>

        <!-- Sidebar (Block 2) – nur wenn befüllt -->
        <?php if ($has_sidebar) : ?>
        <aside class="sidebar" aria-label="Ergänzende Inhalte">
          <?php echo $sidebar_content; ?>
        </aside>
        <?php endif; ?>

      </div><!-- /.content-grid -->

    </div><!-- /.container -->
  </main>

  <!-- ===== FOOTER ===== -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-inner">
        <?php page_footer(); ?>
      </div>
      <div class="footer-bottom">
        <span>&copy; <?php echo date('Y'); ?> <?php echo WEBSITE_HEADER; ?></span>
        <?php if (SHOW_SEARCH) : ?>
        <form class="footer-search" action="<?php echo WB_URL; ?>/search/index.php" method="get" role="search">
          <input type="hidden" name="referrer" value="<?php echo (defined('REFERRER_ID') ? REFERRER_ID : PAGE_ID); ?>">
          <input type="search" name="string" placeholder="Suche…" aria-label="Suchbegriff">
          <button type="submit"><?php echo (isset($TEXT['SEARCH']) ? $TEXT['SEARCH'] : 'Suchen'); ?></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </footer>

</div><!-- /.site-wrapper -->

<script>
/* ===== Navigation: Hamburger & mehrstufige Dropdowns ===== */
(function () {
  'use strict';

  var toggle = document.querySelector('.nav-toggle');
  var nav    = document.getElementById('main-nav');
  if (!toggle || !nav) return;

  /* ---- Hamburger ---- */
  toggle.addEventListener('click', function () {
    var open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    nav.classList.toggle('is-open', !open);
    toggle.setAttribute('aria-label', open ? 'Navigation öffnen' : 'Navigation schließen');
  });

  /* Klick außerhalb schließt Menü */
  document.addEventListener('click', function (e) {
    if (!nav.contains(e.target) && !toggle.contains(e.target)) {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-label', 'Navigation öffnen');
    }
  });

  /* ---- Mobile Dropdown-Toggle ---- */
  function isMobile() { return window.innerWidth < 768; }

  function closeChildren(el) {
    el.querySelectorAll('.has-children.open').forEach(function (c) {
      c.classList.remove('open');
    });
  }

  nav.querySelectorAll('.has-children > a').forEach(function (link) {
    link.addEventListener('click', function (e) {
      if (!isMobile()) return; /* Desktop: CSS :hover */
      e.preventDefault();
      var parent  = link.parentElement;
      var wasOpen = parent.classList.contains('open');

      /* Geschwister auf gleicher Ebene schließen */
      parent.parentElement.querySelectorAll(':scope > .has-children').forEach(function (el) {
        if (el !== parent) { closeChildren(el); el.classList.remove('open'); }
      });

      if (wasOpen) {
        closeChildren(parent);
        parent.classList.remove('open');
      } else {
        parent.classList.add('open');
      }
    });
  });

  /* ESC schließt alles */
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (nav.classList.contains('is-open')) {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-label', 'Navigation öffnen');
      toggle.focus();
    }
    nav.querySelectorAll('.has-children.open').forEach(function (el) {
      el.classList.remove('open');
    });
  });

}());
</script>
<?php register_frontend_modfiles_body();?>
</body>
</html>
