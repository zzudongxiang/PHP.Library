<?php # 需要在 ./lib.php 文件下配置数据库操作函数

# 引入基类操作对象
require_once dirname(__FILE__)."/lib.php";

/**
 * Class CLIENT extends CLIENT_LIB
 */
class CLIENT extends CLIENT_LIB
{
    /**
     * 输出的相关信息
     * @var array
     */
    public $Info = ["IsMobile"  => false,
                    "OS"        => "",
                    "Browser"   => "",
                    "IP"        => "",
                    "Address"   => "",
                    "DateTime"  => "",
                    "UserAgent" => ""];

    /**
     * 构造函数, 自动获取相关信息
     * CLIENT constructor.
     */
    public function __construct()
    {
        $Detail                    = $this -> GetInfo();
        $this -> Info["IsMobile"]  = !empty($Detail["Phone"]);
        $this -> Info["IsMobile"]  = $this -> Info["IsMobile"] || preg_match("/Android/i", $Detail["OS"]);
        $this -> Info["IsMobile"]  = $this -> Info["IsMobile"] || preg_match("/JavaOS/i", $Detail["OS"]);
        $this -> Info["IsMobile"]  = $this -> Info["IsMobile"] || preg_match("/BlackBerryOS/i", $Detail["OS"]);
        $this -> Info["IsMobile"]  = $this -> Info["IsMobile"] || preg_match("/WindowsMobileOS/i", $Detail["OS"]);
        $this -> Info["IsMobile"]  = $this -> Info["IsMobile"] || preg_match("/WindowsPhoneOS/i", $Detail["OS"]);
        $this -> Info["IsMobile"]  = $this -> Info["IsMobile"] || preg_match("/iPadOS/i", $Detail["OS"]);
        $this -> Info["OS"]        = $this -> GetOSInfo($Detail);
        $this -> Info["Browser"]   = empty($Detail["Browser"]) ? "Unknow" : $Detail["Browser"];
        $this -> Info["IP"]        = $this -> GetIP();
        $this -> Info["Address"]   = $this -> GetAddress($this -> Info["IP"]);
        $this -> Info["DateTime"]  = date("Y-m-d H:i:s", time());
        $this -> Info["UserAgent"] = $_SERVER['HTTP_USER_AGENT'];
    }
}
