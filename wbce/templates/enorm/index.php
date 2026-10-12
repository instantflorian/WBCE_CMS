<!DOCTYPE html>
<html lang="de">
<head>
<?php simplepagehead(); ?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0" />

<?php
	I::insertWebFont('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap','Inter');
	I::insertWebFont('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap','Space Grotesk');
	I::insertCssBundle([
	'{WB_URL}/include/font-awesome/css/font-awesome.css',
    '{TEMPLATE}/css/sm-core-css.css',
	'{CACHE}/fonts/inter.css',
	'{CACHE}/fonts/space_grotesk.css',
    '{TEMPLATE}/css/style.css',
	
	], 'enorm');
	
	I::insertJsBundle([
	'{WB_URL}/include/jquery/jquery-min.js',	
	'{WB_URL}/include/jquery/jquery-insert.js',	
	'{WB_URL}/include/jquery/jquery-migrate-min.js',	
	'{WB_URL}/include/jquery/jquery_theme.js',    
	'{TEMPLATE}/js/jquery.smartmenus.js',    
	'{TEMPLATE}/js/nav.js',    
	],'enorm','head');

	register_frontend_modfiles('css');
	register_frontend_modfiles('js');

	require_once __DIR__.'/info.php';
			foreach($block as $k=>$v){
			ob_start(); 
			page_content($k); 
			$block[$k] = ob_get_clean();
		}
		
		
		if(defined('MODULES_BLOCK2') AND MODULES_BLOCK2 != '') { 
			$block[2] .= MODULES_BLOCK2;
		}

?>
</head>
<body>

<header class="site-header">
  <div class="toolbar">
	<?php if (SHOW_SEARCH) { ?>
		<form class="wbcesuche" name="search" action="<?php echo WB_URL; ?>/search/index.php" method="get">
			<input type="hidden" name="referrer" value="<?php echo defined('REFERRER_ID') ? REFERRER_ID : PAGE_ID; ?>" />
			<input type="text" name="string" class="search" placeholder="<?php echo $TEXT['SEARCH']; ?>" />
		</form>				
	<?php
		 }
		if (FRONTEND_LOGIN) {				 
		  if (is_numeric($wb->get_session('USER_ID'))) {
			$loginlink = '<a href="'.LOGOUT_URL.'"><span class="fa fa-fw fa-sign-out" aria-hidden="true"></span></a> <a href="'.PREFERENCES_URL.'"><span class="fa fa-fw fa-user" aria-hidden="true"></span></a>';
		  } else {	  
			$loginlink = '<a href="'.LOGIN_URL.'"><span class="fa fa-fw fa-sign-in" aria-hidden="true"></span></a>';
				if (FRONTEND_SIGNUP) {
					$loginlink .= '<a href="'.SIGNUP_URL.'"><span class="fa fa-fw fa-user-plus" aria-hidden="true"></span></a>';
				}
			  }			  
		  } else {
			  $loginlink = '';
		  }
		  
		  if ($wb->ami_group_member('1')) {
			  $loginlink .= '<a href="'.ADMIN_URL.'/pages/modify.php?page_id='.PAGE_ID.'" target="_blank"><span class="fa fa-fw fa-edit" aria-hidden="true"></span></a>';
		  }
		  echo $loginlink;
		  if(function_exists('language_menu')) { language_menu(); }  

		?>	
  </div>
  <div class="wrap">
    <a href="<?php echo WB_URL; ?>" class="logo">
      
      <?php echo WEBSITE_TITLE; ?>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Navigation öffnen" aria-expanded="false" aria-controls="main-nav">
      <span></span>
    </button>

    <?php
    // Hauptnavigation (Menü 1, siehe info.php $menu[1]).
    // Erzeugt automatisch die mehrstufige <ul id="main-nav" class="sm">-Struktur
    // aus der Seitenhierarchie im Backend - passend für SmartMenus.
    show_menu2(
      1,                    // Menü-Nummer
      SM2_ROOT,              // ab der Wurzelebene
      SM2_ALL,                // alle Untermenü-Ebenen anzeigen
      SM2_ALL|SM2_PRETTY, // Zukunftsmusik:|SM2_EXTERNAL_MENULINKS|SM2_USE_ARIA,     // Standardoptionen, sauberes Markup, externe Links im Menü direkt als Ziel-URL, barrierefreie ARIA-Attribute im Menü
      '<li><a href="[url]" class="[class]" target="[target]">[menu_title]</a>',
      '</li>',
      '<ul>',
      '</ul>',
      false,
      '<ul id="main-nav" class="sm">'
    );
    ?>
  </div>
</header>

<?php 
if ($block[4]!='') { ?>
<section class="hero-full">
  <div class="hero-inner">
    <?php echo $block[4]; ?>
  </div>
</section>
<?php } ?>

<main>
	<?php 
	if ($block[3]!='') { ?>
		<section class="wide-text-top">
		<?php echo $block[3]; ?>
		</section>
	<?php } ?>	

  <?php
  if ($block[2]!='') { ?>
  <section class="content">
    <?php echo $block[1]; ?>
  </section>

  <!-- Seitenspalte: Block 2, siehe info.php $block[2] -->
  <aside class="sidebar">
    <div class="widget">
      <?php echo $block[2]; ?>
    </div>
  </aside>
  <?php } else { ?>
	<section class="wide-text">
		<?php echo $block[1]; ?>
	</section>
  <?php } ?>

  <?php 
  if ($block[5]!='') { ?>
  <section class="wide-text">
    <?php echo $block[5]; ?>
  </section>
  <?php } ?>
</main>

<footer class="site-footer">
  <div class="wrap">
   <?php echo WEBSITE_FOOTER; ?>
  </div>
  <div class="footer-bottom">
    <span>© <?php echo date('Y'); ?> <?php echo WEBSITE_HEADER; ?></span>
    
  </div>
</footer>
<?php register_frontend_modfiles_body();?>

</body>
</html>
