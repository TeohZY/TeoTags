$(document).ready(function() {
  initializeTabs();
  setupScrollToTopButton();
});

$(document).on('handsomePjaxEnd', function() {
  initializeTabs();
  setupScrollToTopButton();
});

function initializeTabs() {
  $('.tabs').each(function() {
    var $tabsComponent = $(this);
    var $tabs = $tabsComponent.find('.tab');
    var $tabContents = $tabsComponent.find('.tab-item-content');

    function clearActiveClasses() {
      $tabs.removeClass('active');
      $tabContents.removeClass('active').hide();
    }

    clearActiveClasses();
    $tabs.first().addClass('active');
    $tabContents.first().addClass('active').show();

    $tabs.click(function() {
      clearActiveClasses();
      $(this).addClass('active');
      var targetContentId = $(this).attr('data-href');
      $('#' + targetContentId).addClass('active').show();
    });
  });
}

function setupScrollToTopButton() {
  $('.tab-to-top button').click(function() {
    $('html,body').animate({
      scrollTop: 0
    }, 'smooth');
  });
}