<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 评论区显示 ip 及归属地的插件 (使用<a href="https://www.ipip.net/product/ip.html">ipip.net Free库</a>, 以及<a href="https://github.com/ipipdotnet/ipdb-php">ipdb for PHP)</a>
 * 
 * @package CommentShowIp
 * @author 社会易姐QwQ
 * @version 0.0.1
 * @link https://shakaianee.top
 */
class CommentShowIp_Plugin implements Typecho_Plugin_Interface {
    /**
     * 激活插件方法
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
        Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = ['CommentShowIp_Plugin', 'replyHook'];
    }

    /**
     * 禁用插件方法
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate() {}
    
    /**
     * 插件配置方法
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {
        $mode = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'mode',
            [
                'hook' => '以hook模式显示ip (不推荐使用)',
            ],
            NULL,
            _t('插件模式设置'),
            _t('Hook模式指使用contentEx位点添加ip信息到评论正文前<br>缺点是无法自定义样式')
        );
        $ipShow = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'ip_show',
            [
                'mask' => 'ip后16位打码',
                'city' => '归属地精确到城市',
            ],
            ['mask', 'city'],
            _t('ip展示设置')
        );
        $form->addInput($mode);
        $form->addInput($ipShow);
    }
    
    /**
     * 个人用户的配置方法
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    /**
     * 获取ip归属地
     *
     * @access private
     * @param string $ip
     * @return string
     */
    private static function getIpLoc(string $ip): string {
        require_once('ipip/db/City.php');
        require_once('ipip/db/CityInfo.php');
        require_once('ipip/db/Reader.php');

        $config = Typecho_Widget::widget('Widget_Options')->Plugin('CommentShowIp');
        $dir = dirname(__file__);
        $ipdbPath = $dir.'/ipipfree.ipdb';

        try {
            $ipdb = new \ipip\db\City($ipdbPath); // 加载ipip.net库
            $city = $ipdb->findInfo($ip, 'CN');
            if($city->country_name != '中国')
                $loc = $city->country_name;
            else {
                $loc = $city->region_name;
                if(in_array('city', ($config->ip_show) ?: []))
                    $loc .= $city->city_name;
            }
        } catch(Exception $e) {
            $loc = '获取错误';
        }
        return $loc;
    }

    /**
     * 为真实ip打码
     *
     * @access private
     * @param string $ip
     * @return string
     */
    private static function maskIp(string $ip): string {
        $config = Typecho_Widget::widget('Widget_Options')->Plugin('CommentShowIp');
        if(in_array('mask', ($config->ip_show) ?: []))
            $ip = join('.', array_slice(explode('.', $ip), 0, 2)).'.*.*';
        return $ip;
    }

    /**
     * 输出评论区ip
     *
     * @access public
     * @param Widget_Abstract_Comments $reply
     * @param string $template
     * @param int $type
     * @return string
     */
    public static function output(Widget_Abstract_Comments $reply, ?string $template = NULL, int $type = 0): ?string {
        $loc = self::getIpLoc($reply->ip);
        $ip = self::maskIp($reply->ip);
        if($template == NULL) {
            $template = "ip属地: {loc} ({ip})<br>";
        }
        $content = str_replace(['{ip}', '{loc}'], [$ip, $loc], $template);
        if($type == 1) return $content;
        else echo $content;
        return NULL;
    }

    /**
     * 使用Hook模式显示评论区ip
     *
     * @access public
     * @param string $template
     * @param Widget_Abstract_Comments $reply
     * @return string
     */
    public static function replyHook(string $text, Widget_Abstract_Comments $reply): string {
        $config = Typecho_Widget::widget('Widget_Options')->Plugin('CommentShowIp');
        if(
            in_array('hook', ($config->mode) ?: []) &&
            !(
                $reply instanceof Widget\Comments\Admin || 
                $reply instanceof Widget\Comments\Edit || 
                $reply instanceof Widget\Comments\Ping ||
                $reply instanceof Widget\Comments\Recent
            )
        )
            return $reply->autoP(self::output($reply, NULL, 1)).$text;
        else
            return $text;
    }
}
