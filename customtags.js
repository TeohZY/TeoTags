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
    event.preventDefault();  // 防止执行点击按钮的默认行为
  
    $('html, body').animate({
      scrollTop: 0
    }, 600);  // 用 'smooth' 替换为具体的动画时长（如 600 毫秒），因为 jQuery 的 animate 方法不支持 'smooth' 这一关键字
  });
}