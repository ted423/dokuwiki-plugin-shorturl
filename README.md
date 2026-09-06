# ShortURL Plugin for DokuWiki

为 DokuWiki 页面生成短网址，并在访问短址时自动跳转回原页面。映射关系集中保存在一个配置文件中，也支持外部 URL 跳转。

Generates short URLs for DokuWiki pages and automatically redirects visitors back to the original page when a short URL is opened. All mappings are kept in a single central file, and redirects to external URLs are supported as well.

基于 Andreas Gohr 的 redirect 插件，适配 PHP 8+。

Based on the redirect plugin by Andreas Gohr, adapted for PHP 8+.

## 功能 Features

中文：

- 访问短网址时自动跳转到原页面（支持内部页面与外部 `http(s)://` 链接）
- 可在后台管理页面集中查看、编辑所有短网址映射
- 可选的自动显示：在页面标题上方或正文下方自动显示短网址链接，兼容所有模板
- 提供页面内语法 `~~SHORTURL~~`，可在正文中直接输出短网址链接
- 提供 helper 接口，供其他插件或模板调用
- 自带英文、德文、简体中文语言包

English:

- Automatic redirection from short URLs to the original page (internal pages and external `http(s)://` links)
- Central admin page to view and edit all URL mappings
- Optional automatic display of the short URL link above the page title or below the page content, working in any template
- In-page syntax `~~SHORTURL~~` to print the short URL link inside the page content
- Helper API for use by other plugins or templates
- Ships with English, German and Simplified Chinese translations

## 系统要求 Requirements

| 项目 Item | 要求 Requirement |
|---|---|
| DokuWiki | 2020 及以后版本（Igor 或更新推荐） / 2020 or later (Igor or newer recommended) |
| PHP | 7.4+，已适配 PHP 8+ / 7.4+, adapted for PHP 8+ |
| 权限 Permissions | 插件目录或缓存目录可写 / plugin directory or cache directory must be writable |

## 安装 Installation

中文：

1. 将本目录复制（或克隆）到 `lib/plugins/shorturl/`，保持目录名与插件名一致。
2. 在管理后台“扩展管理”中确认插件已启用。
3. 确认 Web 服务器对插件目录有写权限；若无写权限，请在配置中启用“将映射文件保存到缓存目录”。

English:

1. Copy (or clone) this directory to `lib/plugins/shorturl/`, keeping the directory name identical to the plugin name.
2. Check the Extension Manager to confirm the plugin is enabled.
3. Make sure the web server can write to the plugin directory; if it cannot, enable the "save mappings to cache dir" option in the configuration.

## 配置 Configuration

在管理后台的“配置设置”中调整以下选项。

Adjust the following options in the Configuration Settings of the admin area.

| 配置项 Setting | 默认值 Default | 说明 Description |
|---|---|---|
| `showmsg` | 0 | 跳转发生时是否向用户显示提示消息 / Whether to show a message to the user when a redirection happens |
| `saveconftocachedir` | 0 | 映射文件是否保存到缓存目录（插件目录不可写时启用）/ Save the mappings file to the cache dir (use when the plugin dir is not writable) |
| `auto_display` | 0 | 自动显示短网址链接，适用于所有模板，无需修改模板文件 / Automatically show the short URL link, works in any template without template edits |
| `auto_display_position` | `top` | 链接显示位置：`top` 为标题上方右对齐小按钮，`bottom` 为正文下方 / Link position: `top` = small button above the page title, `bottom` = below the page content |

## 使用 Usage

### 自动显示 Automatic display

中文：

在配置设置中启用 `auto_display` 即可。链接通过 DokuWiki 核心的 `TPL_CONTENT_DISPLAY` 事件注入，因此不依赖任何特定模板：在 bootstrap3 等模板中会显示在内容面板（`panel panel-default`）内，在其他模板中同样显示在内容区域。无需再手动修改 main.php。

English:

Just enable `auto_display` in the configuration. The link is injected through the core `TPL_CONTENT_DISPLAY` event, so it does not depend on any particular template: with bootstrap3 and similar templates it appears inside the content panel (`panel panel-default`), and with other templates it appears in the content area as well. No manual edits to main.php are needed.

### 页面内语法 In-page syntax

在正文中写 `~~SHORTURL~~`，页面渲染时会自动生成短网址并输出链接。

Write `~~SHORTURL~~` anywhere in a page; the short URL is generated on render and printed as a link.

### 手动模板集成 Manual template integration

如需完全自定义位置，可在模板文件中调用 helper：

For full control over the placement, call the helper from a template file:

```php
<?php
if (!plugin_isdisabled('shorturl') && auth_quickaclcheck($ID) >= AUTH_READ) {
    $shorturl = plugin_load('helper', 'shorturl');
    if ($shorturl) {
        echo $shorturl->shorturlPrintLink($ID);
    }
}
?>
```

### 管理映射 Managing mappings

管理后台的"ShortURL"（短网址设置）页面可直接编辑映射文件，每行格式为 `短网址 页面ID`（以空白分隔）。映射目标也可以是以 `http://` 或 `https://` 开头的外部链接。保存后立即生效。

The "ShortURL" admin page lets you edit the mapping file directly. Each line has the form `shortID pageID` (separated by whitespace). Targets may also be external links starting with `http://` or `https://`. Changes take effect immediately after saving.

## 工作原理 How it works

短网址由页面 ID 的 md5 值经 base32 编码生成（6 个小写字母数字字符），冲突时自动选取下一个候选值。映射保存在 `shorturl.conf`（或缓存目录）中。访问短网址对应的页面时，action 组件查找映射并执行跳转。所有操作均要求用户对目标页面具备读权限。

The short ID is derived from the md5 hash of the page ID using base32 encoding (6 lowercase alphanumeric characters); on collisions the next candidate is used automatically. Mappings are stored in `shorturl.conf` (or the cache dir). When a short URL page is opened, the action component looks up the mapping and performs the redirect. All operations require the user to have at least read permission for the target page.

## 语言 Languages

英语 (en)、德语 (de)、简体中文 (zh)。

English (en), German (de), Simplified Chinese (zh).

## 作者与许可 Authors & License

原作 based on the redirect plugin by Andreas Gohr，由 Frank Schiebel 开发并维护，Grok Flash 适配 PHP 8+。

Originally based on the redirect plugin by Andreas Gohr, developed and maintained by Frank Schiebel, adapted for PHP 8+ by Grok Flash.

许可 License: GPL 2 (http://www.gnu.org/licenses/gpl.html)

插件主页 Plugin page: http://www.dokuwiki.org/plugin:shorturl
