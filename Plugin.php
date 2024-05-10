<?php

/**
 * 适配 {% %} 语法
 *
 * @package CustomTags
 * @author TeohZY
 * @version 1.0.0
 * @dependence 14.10.10-a
 * @link https://blog.teohzy.com
 *
 **/

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

class CustomTags_Plugin implements Typecho_Plugin_Interface
{
    /* 激活插件方法 */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('CustomTags_Plugin', 'applyCustomTemplateParsing');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('CustomTags_Plugin', 'applyCustomTemplateParsing');
        Typecho_Plugin::factory('Widget_Archive')->content = array('CustomTags_Plugin', 'applyCustomTemplateParsing');

        // 添加header钩子来输出CSS文件
        Typecho_Plugin::factory('Widget_Archive')->header = array('CustomTags_Plugin', 'header');
    }

    /* 禁用插件方法 */
    public static function deactivate()
    {
    }

    /* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function applyCustomTemplateParsing($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;
        if ($widget instanceof Widget_Archive && $widget->is('single')) {
            $content = self::parseCustomTemplateTags($content);
        }
        return $content;
    }


    public static function header()
    {
        $cssUrl = Helper::options()->pluginUrl . '/CustomTags/customtags.css';
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />' . "\n";
        echo '<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />' . "\n";
    }

    public static function parseCustomTemplateTags($content)
    {
        $original_content = $content;
        // 匹配多行和单行标记
        $multiLinePattern = '/{%\s*(\w+)\s+([\w\s]+?)\s*%}(.*?)\{%\s*end\1\s*%}/su';
        $notePattern = '/{%\s*note\s+([\w\s]+?)\s*%}(.*?)\{%\s*endnote\s*%}/su';

        $content = preg_replace_callback($notePattern, function ($matches) {
            // 在此处，$matches[1] 是属性，$matches[2] 是内容
            $classString = htmlspecialchars(trim('note') . ' ' . trim($matches[1]));
            $textContent = trim($matches[2]);

            // 处理 <br> 标签的问题，确保它不会被转义。
            $textContent = str_replace('<br>', '<br/>', $textContent);

            return "<div class=\"{$classString}\"><p>{$textContent}</p></div>";
        }, $content);


        $content = preg_replace_callback('/\{%\s*hideToggle\s+(.*?)\s*%\}(.*?)\{%\s*endhideToggle\s*%\}/su', function ($matches) {
            $title = $matches[1];
            $content = $matches[2];
            // Convert each line of content into a paragraph
            $contentLines = explode("\n", $content);
            $contentHtml = "";

            foreach ($contentLines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $contentHtml .= "<p>$line</p>";
                }
            }

            return "<details class=\"toggle\"><summary class=\"toggle-button\">$title</summary><div class=\"toggle-content\">$contentHtml</div></details>";
        }, $content);


        $content = preg_replace_callback('/\{%\slink\s(.*?),(.*?),(.*?)\s%\}/s', function ($matches) {
            // 提取$matches[3]中的链接
            if (preg_match('/href="([^"]+)"/', $matches[3], $linkMatches)) {
                $url = $linkMatches[1];
            } else {
                // 如果没有找到链接，可以设置一个默认值或进行错误处理
                $url = $matches[3]; // 请根据需要替换为合适的URL
            }
        
            // 检测提取出的链接是否包含http://或https://
            if (!preg_match('~https?://~', $url)) {
                $url = 'https://' . $url;
            }
        
            // 从URL中解析出主机名用于构造favicon图标的URL
            $host = parse_url($url, PHP_URL_HOST);
            if ($host) {
                $imgUrl = "https://api.iowen.cn/favicon/" . $host . ".png";
            } else {
                // 处理无法解析主机名的情况
                $imgUrl = "placeholder_image_url"; // 替换为合适的占位图标URL
            }
        
            // 构建HTML结构，并返回
            return "<div><a class=\"tag-Link\" target=\"_blank\" href=\"{$url}\">
                <div class=\"tag-link-tips\">引用站外地址</div>
                <div class=\"tag-link-bottom\">
                    <div class=\"tag-link-left\" style=\"background-image: url({$imgUrl});\"></div>
                    <div class=\"tag-link-right\">
                        <div class=\"tag-link-title\">{$matches[1]}</div>
                        <div class=\"tag-link-sitename\">{$matches[2]}</div>
                    </div>
                    <i class=\"fa-solid fa-angle-right\"></i>
                </div>
            </a></div>";
        }, $content);

        // tables 
        $content = preg_replace_callback(
            '/\{% tabs (.*?) %\}(.*?)\{% endtabs %\}/is',
            function ($matches) {
                [$fullMatch, $tabId, $tabBlocks] = $matches;
        
                // 分割每个tab块，并转换内容
                $tabNavs = '';
                $tabContents = '';
                preg_match_all('/<!-- tab (.*?) -->(.*?)<!-- endtab -->/is', $tabBlocks, $tabMatches);
                foreach ($tabMatches[1] as $index => $tabTitle) {
                    // 解析Tab标题，检查是否包含图标
                    $iconHtml = '';
                    $tabName = $tabTitle;
                    if (strpos($tabTitle, '@') !== false) {
                        [$tabName, $icon] = explode('@', $tabTitle);
                        $iconHtml = "<i class=\"$icon\"></i>";
                    }
        
                    // 创建Tab导航
                    $activeClass = $index === 0 ? ' active' : '';
                    $tabNavs .= "<button type=\"button\" class=\"tab$activeClass\" data-href=\"$tabId-" . ($index + 1) . "\">$iconHtml $tabName</button>";
        
                    // 创建Tab内容
                    $tabContents .= "<div class=\"tab-item-content$activeClass\" id=\"$tabId-" . ($index + 1) . "\"><p><strong>" . trim($tabMatches[2][$index]) . "</strong></p></div>";
                }
        
                // 组合所有部分
                $data = "<div class=\"tabs\" id=\"$tabId\"><ul class=\"nav-tabs\">$tabNavs</ul><div class=\"tab-contents\">$tabContents</div><div class=\"tab-to-top\"><button type=\"button\" aria-label=\"scroll to top\"><i class=\"fas fa-arrow-up\"></i></button></div></div>";
                $console = 'console.log(' . json_encode($data) . ');';
                $console = sprintf('<script>%s</script>', $console);
                echo $console;
                return $data;
            },
            $content
        );
        

        // 如果内容经过解析后发生了变化，就清除所有的 <br> 标签
        if ($content !== $original_content) {
            $content = preg_replace('/<br\s?\/?>/i', '', $content);
        }
        return $content;
    }
}
