<?php
/**
 * 开启用户头像上传插件，默认显示gravatar头像，上传后显示。css和js都写在插件里了，按需整合。
 *
 * @package avatarUpload
 * @author Gaobukai
 * @version 1.0.0
 * @link https://gaobukai.com
 * */

class avatarUpload_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Helper::addAction("avatarupload",'avatarUpload_Action');
        Typecho_Plugin::factory('Widget_Abstract_Comments')->gravatar = array('avatarUpload_Plugin', 'gravatar');
        Typecho_Plugin::factory('admin/profile.php')->bottom = array('avatarUpload_Plugin', 'profileAvatar');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        Helper::removeAction("avatarupload");
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {}

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    /**
     * 后台头像调用地址 $uid 登录用户的id
     *
     */
    public static function avatar($uid) {
      $options = Helper::options();
      $name = $uid.'.png';
      $path =  Typecho_Common::url(defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : '/usr/uploads',
          defined('__TYPECHO_UPLOAD_ROOT_DIR__') ? __TYPECHO_UPLOAD_ROOT_DIR__ : __TYPECHO_ROOT_DIR__)
          . '/avatar';

      $path= $path.'/'.$name;
      if(file_exists($path)) {
        $path = Typecho_Common::url(defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : '/usr/uploads',
            $options->siteUrl). '/avatar';
        $path= $path.'/'.$name;
      }else {return false;}
      return $path;
    }

    /**
     * 调用gravatar输出用户头像
     *
     * @access public
     * @param integer $size 头像尺寸
     * @param string $default 默认输出头像
     * @return void
     */
    public static function gravatar($size = 32, $rating, $default = NULL, $comment)
    {
        $gravatarsUrl = Helper::options()->JCustomAvatarSource ? Helper::options()->JCustomAvatarSource : 'https://gravatar.helingqi.com/wavatar/';
        $options = Helper::options();
        $mail = $comment->mail;
        $mailLower = strtolower($mail);
        $md5MailLower = md5($mailLower);
        $qqMail = str_replace('@qq.com', '', $mailLower);
        if ($options->commentsAvatar && 'comment' == $comment->type) {
            if ($comment->authorId > 0 && self::avatar($comment->authorId)) {
                echo '<a href="/author/'.$comment->authorId.'"><img class="avatar" src="' . self::avatar($comment->authorId) . '" alt="' .
                $comment->author . '" width="' . $size . '" height="' . $size . '" /></a>';
            } else {
                if (strstr($mailLower, "qq.com") && is_numeric($qqMail) && strlen($qqMail) < 11 && strlen($qqMail) > 4) {
		            echo '<img class="avatar" src="' . 'https://thirdqq.qlogo.cn/g?b=qq&nk=' . $qqMail . '&s=100' . '" alt="' .
                $comment->author . '" width="' . $size . '" height="' . $size . '" />';
                } else {
                    echo '<img class="avatar" src="' . $gravatarsUrl . $md5MailLower . '?d=mm' . '" alt="' .
                $comment->author . '" width="' . $size . '" height="' . $size . '" />';
                }
            }
        }
    }

    public static function profileAvatar()
    {
        $options = Helper::options();
        $user = Typecho_Widget::widget('Widget_User');
    ?>
    <script>
    var html = '<a href="javascript:;" id="avatarupload"><span>点击上传更换头像</span><img class="profile-avatar" src="<?php echo self::avatar($user->uid) ? self::avatar($user->uid) : Typecho_Common::gravatarUrl($user->mail, 220, 'X', 'identicon', Typecho_Request::getInstance()->isSecure()); ?>" alt="<?php $user->screenName(); ?>"><input type="file" id="avatar" title=""></a>';

    $('.profile-avatar').closest('p').html(html);

           $('#avatar').on('change', function() {
              var formData = new FormData();
              var file = $('#avatar')[0].files[0];
              formData.append('file',file);
              var index = (file.name).lastIndexOf(".");
              var ext   = (file.name).substr(index+1);
              if(ext !== 'png' && ext !== 'jpg' && ext !== 'jpeg' && ext !== 'gif'){
                  alert('请上传图片文件，仅限格式png,jpg,jpeg,gif');
                  return false;
              } else if (file.size > 102400) {
                  alert('不能超过100K，请压缩图片。');
                  return false;
              }
              $('#avatarupload span').html('上传中。。。').animate({ opacity: '1' });
              $.ajax({
                url:'../index.php/action/avatarupload',
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                success:function(res){
                    //location.reload(true); /*不知道为什么不能自动更新*/
                    $('#avatarupload span').html('上传成功，CTRL+F5刷新显示').animate({ opacity: '1' });
                }
            })
        })
    </script>
<style>
#avatarupload {
    display: flex;
    align-items: center;
    justify-content: center;
    position:relative;
}
#avatarupload span {
    opacity:0;
    position:absolute;
    z-index: 2;
    color: #000000;
    font-size: 1.2rem;
    transition: opacity .3s ease-in-out;
}
#avatarupload:hover span {
    opacity: 1;
    transition: opacity .3s ease-in-out;
}
#avatarupload .profile-avatar {
    transition: all .2s ease-in-out;
}
#avatarupload:hover .profile-avatar {
    border-color: #b2bbbe;
    transition: all .3s ease-in-out;
    opacity: .8;
}
#avatarupload #avatar {
    width: 100%;
    height: 100%;
    opacity:0;
    position:absolute;
    top:0;
    left:0;
    z-index: 3;
    cursor:pointer;
}
</style>
    <?php    }

}
