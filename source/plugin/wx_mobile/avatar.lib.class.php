<?php
/**
 *      HuiZhou Media Group(C)2008-2099.
 *
 *      $Id: wx_mobile.lib.class.php 2015-07-31 14:44 stellar $
 */

@set_time_limit(120);
ignore_user_abort();

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('UC_SERVER_ROOT', '/usr/local/www/ucenter16/');			//UCenter(uc_server) 路径

class avatar {
	
	public static function transfer($data){
		if(preg_match_all('/^http(.+)\.(.+)$/', $data['avatar'], $a)){
			self::set_home($data['uid'], UC_SERVER_ROOT.'data/avatar');

			$avatar = $data['avatar'];
			$ucavatar = UC_SERVER_ROOT.'data/avatar/'.self::get_avatar($data['uid'], 'middle');

			$ucavatar = str_replace('\\','/',$ucavatar);

			if(!file_exists($ucavatar)) {
				$create = FALSE;
				$img = new Image_Lite($avatar, $ucavatar);
				if($img->imagecreatefromfunc && $img->imagefunc) {
					if($img->Thumb(120, 120)) {
						$create = TRUE;
					}
				}
				if($create) {
					$ucavatar = UC_SERVER_ROOT.'data/avatar/'.self::get_avatar($data['uid'], 'big');
					$ucavatar = str_replace('\\','/',$ucavatar);
					@copy($avatar, $ucavatar);

					$ucavatar = UC_SERVER_ROOT.'data/avatar/'.self::get_avatar($data['uid'], 'small');
					$ucavatar = str_replace('\\','/',$ucavatar);
					$img = new Image_Lite($avatar, $ucavatar);
					if($img->imagecreatefromfunc && $img->imagefunc) {
						$img->Thumb(48, 48);
					}

				}
			}
		}
	}

	public static function set_home($uid, $dir = '.') {
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		!is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
		return $dir1.'/'.$dir2.'/'.$dir3;
	}


	public static function get_home($uid) {
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		return $dir1.'/'.$dir2.'/'.$dir3;
	}

	public function get_avatar($uid, $size = 'big') {
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2)."_avatar_$size.jpg";
	}

}

class Image_Lite {

	var $attachinfo = '';
	var $srcfile = '';
	var $targetfile = '';
	var $imagecreatefromfunc = '';
	var $imagefunc = '';
	var $attach = array();
	var $animatedgif = 0;

	function Image_Lite($srcfile, $targetfile) {
		$this->srcfile = $srcfile;
		$this->targetfile = $targetfile;
		$this->attachinfo = @getimagesize($srcfile);


		switch($this->attachinfo['mime']) {
			case 'image/jpeg':
				$this->imagecreatefromfunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
				$this->imagefunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
				break;
			case 'image/gif':
				$this->imagecreatefromfunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
				$this->imagefunc = function_exists('imagegif') ? 'imagegif' : '';
				break;
			case 'image/png':
				$this->imagecreatefromfunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
				$this->imagefunc = function_exists('imagepng') ? 'imagepng' : '';
				break;
		}

		$this->attach['size'] = @filesize($srcfile);
		if($this->attachinfo['mime'] == 'image/gif') {
			$fp = fopen($srcfile, 'rb');
			$targetfilecontent = fread($fp, $this->attach['size']);
			fclose($fp);
			$this->animatedgif = strpos($targetfilecontent, 'NETSCAPE2.0') === FALSE ? 0 : 1;
		}
	}

	function Thumb($thumbwidth, $thumbheight, $preview = 0) {
		$return = $this->Thumb_GD($thumbwidth, $thumbheight);
		$this->attach['size'] = @filesize($this->targetfile);
		return $return;
	}

	function Thumb_GD($thumbwidth, $thumbheight) {
		if(function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled') && function_exists('imagejpeg')) {
			
			$imagecreatefromfunc = $this->imagecreatefromfunc;
			$imagefunc = $this->imagefunc;
			list($img_w, $img_h) = $this->attachinfo;

			if(!$this->animatedgif) {                                                         // && ($img_w >= $thumbwidth || $img_h >= $thumbheight)
				$attach_photo = $imagecreatefromfunc($this->srcfile);

				$imgratio = $img_w / $img_h;
				$thumbratio = $thumbwidth / $thumbheight;

				if($imgratio >= 1 && $imgratio >= $thumbratio || $imgratio < 1 && $imgratio > $thumbratio) {
					$cuty = $img_h;
					$cutx = $cuty * $thumbratio;
				} elseif($imgratio >= 1 && $imgratio <= $thumbratio || $imgratio < 1 && $imgratio < $thumbratio) {
					$cutx = $img_w;
					$cuty = $cutx / $thumbratio;
				}

				$dst_photo = imagecreatetruecolor($cutx, $cuty);
				imageCopyMerge($dst_photo, $attach_photo, 0, 0, 0, 0, $cutx, $cuty, 100);

				$thumb['width'] = $thumbwidth;
				$thumb['height'] = $thumbheight;

				$targetfile = $this->targetfile;

				$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
				imageCopyreSampled($thumb_photo, $dst_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $cutx, $cuty);
				clearstatcache();
				if($this->attachinfo['mime'] == 'image/jpeg') {
					$imagefunc($thumb_photo, $targetfile, 100);
				} else {
					$imagefunc($thumb_photo, $targetfile);
				}
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>