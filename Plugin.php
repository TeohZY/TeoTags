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
        // $jsUrl = Helper::options()->pluginUrl . '/CustomTags/customtags.js';
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />' . "\n";
        echo '<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />' . "\n";
        // echo '<script type="text/javascript" src="' . $jsUrl . '"></script>';
    }
    public static function console($data)
    {

        $console = 'console.log(' . json_encode($data) . ');';
        $console = sprintf('<script>%s</script>', $console);
        echo $console;
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

        $content = preg_replace_callback(
            '/{%\s*label\s+(\S+?)(?:\s+(\S+))?\s*%}/',
            function ($matches) {
                $text = $matches[1]; // 获取文本内容
                // 如果指定了颜色类，则使用，否则默认为'default'
                $color_class = isset($matches[2]) ? $matches[2] : 'default';

                return '<mark class="hl-label ' . $color_class . '">' . $text . '</mark>';
            },
            $content
        );

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
            '/{%\s*tabs\s*(.*?),?\s*([\d-]*)?\s*%}([\s\S]*?){%\s*endtabs\s*%}/',
            function ($matches) {
                $id = $matches[1];
                $defaultActiveTab = !empty($matches[2]) ? (int)$matches[2] : 0;
                $tabsBlock = $matches[3];

                preg_match_all(
                    '/<!--\s*tab\s*(.*?)\s*-->([\s\S]*?)<!--\s*endtab\s*-->/',
                    $tabsBlock,
                    $tabs_matches
                );
                $tabTitles = $tabs_matches[1];
                $tabContents = $tabs_matches[2];

                $html = '<div class="tabs" id="' . $id . '"><ul class="nav-tabs">';

                foreach ($tabTitles as $i => $title) {
                    $index = $i + 1;
                    $active = $i === ($defaultActiveTab - 1) ? ' active' : '';
                    if (strpos($title, '@') !== false) {
                        $titleParts = explode('@', $title, 2);
                        $iconClass = isset($titleParts[1]) ? '<i class="' . trim($titleParts[1]) . '" style="text-align:center"></i>' : '';
                        $title = isset($titleParts[0]) && !empty(trim($titleParts[0])) ? $iconClass . ' ' . trim($titleParts[0]) : $iconClass;
                    } else {
                        $title = !empty($title) ? $title : $id . ' ' . $index;
                    }
                    $html .= '<button type="button" class="tab' . $active . '" data-href="' . $id . '-' . $index . '">' . $title . '</button>';
                }

                $html .= '</ul><div class="tab-contents">';

                foreach ($tabContents as $i => $content) {
                    $index = $i + 1;
                    $active = $i === ($defaultActiveTab - 1) ? ' active' : '';
                    $html .= '<div class="tab-item-content' . $active . '" id="' . $id . '-' . $index . '"><p>' . $content . '</p></div>';
                }

                $html .= '</div><div class="tab-to-top"><button type="button" aria-label="scroll to top"><i class="fas fa-arrow-up"></i></button></div></div>';
                $jsUrl = Helper::options()->pluginUrl . '/CustomTags/customtags.js';
                $html .= '<script type="text/javascript" src="' . $jsUrl . '"></script>';
                return $html;
            },
            $content
        );

        $content = preg_replace_callback(
            '/{%\s*timeline\s*(.*?)\s*%}(.*?){%\s*endtimeline\s*%}/s',
            function ($matches) {
                // 分解并处理模板标签参数
                $params = explode(',', $matches[1]);
                $year = trim($params[0]);
                $color_class = isset($params[1]) ? trim($params[1]) : 'undefined';

                // 分解并处理时间线内容
                $timeline_contents = '';
                preg_match_all('#<!--\s*timeline\s*(.*?)\s*-->(.*?)<!--\s*endtimeline\s*-->#is', $matches[2], $timeline_contents_template);
                for ($i = 0; $i < count($timeline_contents_template[1]); $i++) {
                    $date = $timeline_contents_template[1][$i];
                    $content = $timeline_contents_template[2][$i];

                    $timeline_contents .= '<div class="timeline-item"><div class="timeline-item-title"><div class="item-circle"><p>' . trim($date) . '</p></div></div><div class="timeline-item-content"><p>' . trim($content) . '</p></div></div>';
                }

                // 构建最终的HTML结构
                $rendered_html = '<div class="custom-tags"><div class="timeline ' . $color_class . '"><div class="timeline-item headline"><div class="timeline-item-title"><div class="item-circle"><p>' . $year . '</p></div></div></div>' . $timeline_contents . '</div></div>';

                return $rendered_html;
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
