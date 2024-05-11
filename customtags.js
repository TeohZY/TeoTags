$(document).ready(function() {
  initializeTabs();
  setupScrollToTopButton();
});

$(document).on('contentLoaded', function() {
  initializeTabs();
  setupScrollToTopButton();
});
$(document).on('pjax:end', function() {
  if ($('.tabs').length > 0) {
    initializeTabs();
    setupScrollToTopButton();
  }
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
  $('.tab-to-top button').click(function(event) {
    event.preventDefault(); // 阻止默认行为
    event.stopPropagation(); // 阻止事件冒泡
    
    // 使用 'body, html' 可能会在一些情况下导致滚动问题
    // 可以只用 'html' 或者 'body' 来避免这个问题
    $('html').animate({
      scrollTop: 0
    }, 600); // 使用数字来指定动画时长
  });
}