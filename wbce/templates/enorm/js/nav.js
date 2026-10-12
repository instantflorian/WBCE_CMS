jQuery(function ($) {
  $('#main-nav').smartmenus({
    subMenusSubOffsetX: 6,
    subMenusSubOffsetY: -6
  });

  var $toggle = $('#navToggle');
  var $nav = $('#main-nav');

  $toggle.on('click', function () {
    var expanded = $toggle.attr('aria-expanded') === 'true';
    $toggle.attr('aria-expanded', String(!expanded));
    $nav.toggleClass('sm-mobile-open');
  });

  // Beim Zurückwechseln auf Desktop-Breite das mobile Menü zurücksetzen
  $(window).on('resize', function () {
    if ($(window).width() > 700) {
      $nav.removeClass('sm-mobile-open');
      $toggle.attr('aria-expanded', 'false');
    }
  });
});
