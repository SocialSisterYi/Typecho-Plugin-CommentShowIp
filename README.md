# Typecho-Plugin-CommentShowIp

Tpecho 的评论区显示评论发布者 ip 及归属地插件

## 功能介绍

为博客评论区留言增加 ip 及归属地显示，用以确定发布者身份及防止“地域黑”行为

> 2021年10月26日，国家网信办发布《互联网用户账号名称信息管理规定（征求意见稿）》（以下简称“《征求意见稿》”），拟要求平台在用户账号信息页面展示账号IP地址属地信息：境内用户账号IP地址属地信息需标注到省（区、市），境外账号IP地址属地信息需标注到国家（地区）。

以上内容与本项目无实际关联，仅作为整活使用（手动滑稽

- 双模式输出（Hook与API）
- 支持自定义HTML模板
- ip打码及归属地精度可选

## 安装

将本项目文件夹放入 /usr/plugins 下，并在后台的插件管理中启用插件

并将目录名设置为 `CommentShowIp`

## 插件配置

- 开启以hook模式显示ip：见下文介绍
- ip后16位打码：模板输出的`{ip}`信息是否后16bit使用\*表示，如：192.168.\*.\*
- 归属地精确到城市：模板输出的`{loc}`信息是否精确到地级市，如：广东广州

## 使用方法

### Hook模式

将插件配置中的`以hook模式显示ip`复选框选中，发布者 ip 将自动输出至评论正文区域的第一行

默认关闭，不推荐使用，使用该方法无法自定义ip显示样式和输出位点

eg：使用默认模板

![](http://i0.hdslb.com/bfs/album/048aa4afb3531c83346574ed93988788bdc38f66.png)

### API模式

使用`CommentShowIp_Plugin::output($reply, $template = NULL, $type = 0)`函数进行调用

参数说明：

| 参数     | 说明           | 类型                     | 是否可选 | 说明                                                         |
| -------- | -------------- | ------------------------ | -------- | ------------------------------------------------------------ |
| reply    | 评论View层对象 | Widget_Abstract_Comments | ×        |                                                              |
| template | 模板           | string \|\| NULL         | √        | 输出渲染模板，如为NULL，则使用内建样式<br />格式化符`{ip}`：ip地址信息<br />格式化符`{loc}`：归属地信息 |
| type     | 输出模式       | int                      | √        | 0：使用`echo`输出到网页<br />1：使用`return`返回             |

eg：使用 MDUI 框架

在合适位置加入以下代码（`$this`为实例化的评论区View层对象）

```php
CommentShowIp_Plugin::output($this, "
<div>
    <span style=\"font-size:10px;\" mdui-tooltip=\"{content: '{ip}'}\">ip属地: {loc}</span>
</div>"
);
```

![](http://i0.hdslb.com/bfs/album/c53eaa826316400cd1c4ef0d2c912768878e961f.png)

## 声明

插件使用[ipip.net Free库](https://www.ipip.net/product/ip.html)以及[ipdb for PHP Library](https://github.com/ipipdotnet/ipdb-php)

商业使用禁止
