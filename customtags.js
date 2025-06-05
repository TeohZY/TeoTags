

function initializeTabs() {
  // 获取所有的tabs组件
  const tabsComponents = document.querySelectorAll('.tabs');

  // 为每一个tabs组件定义行为
  tabsComponents.forEach(function (tabsComponent) {
    function clearActiveClasses(elements) {
      elements.forEach(function (element) {
        element.classList.remove('active');
      });
    }

    // 对于每一个组件,只显示第一个tab内容
    const tabs = tabsComponent.querySelectorAll('.tab');
    const tabContents = tabsComponent.querySelectorAll('.tab-item-content');

    // 清除所有tab的active类并为第一个选项卡添加active类
    clearActiveClasses(tabs);
    tabs[0].classList.add('active');

    // 初始隐藏所有内容并只显示第一个内容块
    clearActiveClasses(tabContents); // 确保开始时没有tabContent具有active类
    tabContents.forEach(content => content.style.display = 'none');
    tabContents[0].style.display = 'block';
    tabContents[0].classList.add('active');

    // 为每个tab添加点击事件
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        const targetContentId = tab.getAttribute('data-href');

        clearActiveClasses(tabs); // 清除所有tab的active类
        tab.classList.add('active'); // 为当前tab添加active类

        clearActiveClasses(tabContents); // 在显示新的tabContent前，确保所有tabContents没有active类
        tabContents.forEach(function (content) {
          content.style.display = 'none'; // 隐藏所有tab内容
          if (content.getAttribute('id') === targetContentId) {
            content.style.display = 'block'; // 显示与当前tab匹配的内容
            content.classList.add('active'); // 为当前tabContent添加active类
          }
        });
      });
    });

    tabsComponent.querySelector(".tab-to-top button").addEventListener("click", function () {
      const header = document.querySelector('header');
      let headerHeight = 0;
    
      // 检测header是否具有类fix-padding
      if (header.classList.contains('fix-padding')) {
        headerHeight = header.offsetHeight; // 获取header的高度
      }
    
      const elementRect = tabsComponent.getBoundingClientRect();
      const elementTopRelativeToViewport = elementRect.top;
    
      // 减去header的高度以及可能存在的文档边框宽度（例如浏览器默认的边框）
      const offsetPosition = elementTopRelativeToViewport + window.scrollY - headerHeight - (document.documentElement.clientTop || 0);
    
      window.scroll({
        top: offsetPosition,
        behavior: 'smooth'
      });
    });
  });
}

/**
 * 优化后的 PJAX 兼容初始化代码
 */
(function() {
  // 缓存 jQuery 对象（如果存在）
  const $document = typeof jQuery !== 'undefined' ? $(document) : null;
  
  // 主初始化函数
  function initialize() {
    // 确保 DOM 已加载
    if (!document.body) return;
    
    // 检查是否有需要初始化的元素
    if ($document && $('.tabs').length > 0 || document.querySelector('.tabs')) {
      initializeTabs();
    }
  }
  
  // 绑定 PJAX 相关事件
  function bindPjaxEvents() {
    // 原生事件绑定
    if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', initialize);
      document.addEventListener('pjax:complete', initialize);
      document.addEventListener('pjax:end', initialize);
      document.addEventListener('contentLoaded', initialize);
    }
    
    // jQuery 事件绑定（如果可用）
    if ($document) {
      $document.ready(initialize);
      $document.on('pjax:complete pjax:end contentLoaded', initialize);
    }
  }
  
  // 立即执行或等待加载
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initialize();
  } else {
    bindPjaxEvents();
  }
  
  // 确保至少执行一次初始化
  setTimeout(initialize, 100);
})();