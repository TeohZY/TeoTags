$(document).on('handsomePjaxEnd', function() {
  // 检查是否存在.tabs类的元素，然后初始化tabs控件
  if ($('.tabs').length) {
    console.log("Tabs detected. Initializing...");
    initializeTabs();
  } else {
    console.log("No tabs detected.");
  }
});

function initializeTabs() {
  // 初始化所有含有.tabs类的组件
  $('.tabs').each(function() {
    var $tabsComponent = $(this);
    var $tabs = $tabsComponent.find('.tab');
    var $tabContents = $tabsComponent.find('.tab-item-content');

    // 清除所有tab和内容的active类
    function clearActiveClasses() {
      $tabs.removeClass('active');
      $tabContents.removeClass('active').hide();
    }

    // 默认显示第一个tab和内容
    clearActiveClasses();
    $tabs.first().addClass('active');
    $tabContents.first().addClass('active').show();

    // 设置tabs点击事件
    $tabs.click(function() {
      // 当点击tab时清除所有的'active'类并显示相关内容
      clearActiveClasses();
      $(this).addClass('active');
      var targetContentId = $(this).attr('data-href');
      $('#' + targetContentId).addClass('active').show();
    });
  });

  // 初始化滚动到顶部按钮
  $('.tab-to-top button').click(function() {
    $('html,body').animate({
      scrollTop: 0
    }, 'smooth');
  });
}