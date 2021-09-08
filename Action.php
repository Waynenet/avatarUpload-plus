<?php
/**
 * Gaobukai
 * https://gaobukai.com
 * Date: 2020/11/04
 */

class avatarUpload_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $db;
    const UPLOAD_DIR = '/usr/uploads';

    public function action()
    {
        $this->db = Typecho_Db::get();
        $cookieUid = Typecho_Cookie::get('__typecho_uid');
        if (NULL !== $cookieUid) {
            /** 验证登陆 */
            $user = $this->db->fetchRow($this->db->select()->from('table.users')
                ->where('uid = ?', intval($cookieUid))
                ->limit(1));

            $cookieAuthCode = Typecho_Cookie::get('__typecho_authCode');
            if ($user && Typecho_Common::hashValidate($user['authCode'], $cookieAuthCode)) {
                $this->_user = $user;
                if ($this->_hasLogin = true){
                    $file = $_FILES['file'];
                    if($file['size'] > 102400){echo '非法上传！';return false;}
                    $name = $user['uid'].'.png';
                    $path =  Typecho_Common::url(defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : self::UPLOAD_DIR,
                        defined('__TYPECHO_UPLOAD_ROOT_DIR__') ? __TYPECHO_UPLOAD_ROOT_DIR__ : __TYPECHO_ROOT_DIR__)
                        . '/avatar';

                    //创建上传目录
                    if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                    }

                    $path= $path.'/'.$name;
                    if (isset($file['tmp_name'])) {
                        @unlink($path);
                        //移动上传文件
                        if (!@move_uploaded_file($file['tmp_name'], $path)) {
                            return false;
                        }
                    } else if (isset($file['bytes'])) {
                        @unlink($path);
                        //直接写入文件
                        if (!file_put_contents($path, $file['bytes'])) {
                            return false;
                        }
                    } else {
                        return false;
                    }

                }else{
                    echo "非法";
                }
            }

        }else{
            echo "非法";
        }

    }

}
