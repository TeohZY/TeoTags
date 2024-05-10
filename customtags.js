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
      // 计算tabs组件相对于视口顶部的偏移量并滚动
      const elementRect = tabsComponent.getBoundingClientRect();
      const elementTopRelativeToViewport = elementRect.top;
      const offsetPosition = elementTopRelativeToViewport + window.scrollY - (document.documentElement.clientTop || 0);
      window.scroll({
        top: offsetPosition,
        behavior: 'smooth'
      });
    });
  });
}

// 初始页面加载时，调用一次初始化函数
$(document).ready(initializeTabs);

// 监听 Pjax 结束事件，然后重新初始化
$(document).on('pjax:end', initializeTabs);

