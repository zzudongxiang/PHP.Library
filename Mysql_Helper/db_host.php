<?php

/**
 * Class DB_HOST
 * 用于与数据库链接的相关参数
 * 不同数据库链接之间可以创建不同对象
 */
class DB_HOST
{
    /**
     * 数据库链接的地址, 一般是IP或域名, 默认为localhost
     */
    public $Host = "localhost";

    /**
     * 登录数据库所需要的用户名, 默认是root
     */
    public $UserName = "root";

    /**
     * 登录数据库所需要的链接密码
     */
    public $PassWord = "******";

    /**
     * 对应地址数据库的数据库名, 默认为mysql
     */
    public $DB_Name = "mysql";

    /**
     * 对应链接的编码方式, 默认为utf-8
     */
    public $Encode = 'utf8';

    /**
     * DB_HOST constructor. 自动加载当前目录下的mysql.json配置文件(如果可用)
     */
    public function __construct()
    {
        $this -> LoadConfig(dirname(__FILE__)."/json/mysql.json");
    }

    /**
     * 获取一个Mysql的连接对象
     *
     * @return \PDO 返回一个PDO对象
     */
    public function GetConnection()
    {
        $Connection = "mysql:host={$this -> Host};dbname={$this -> DB_Name}";
        $Encode     = "SET NAMES '{$this -> Encode}';";
        $Options    = [PDO::MYSQL_ATTR_INIT_COMMAND => $Encode];
        return new PDO($Connection, $this -> UserName, $this -> PassWord, $Options);
    }

    /**
     * 加载一个配置文件,
     * 当前方法不会返回构造的PDO对象, 仅返回加载结果
     *
     * @param string $JsonPath 配置文件所在的json文件路径
     *
     * @return bool 返回是否加载成功
     */
    public function LoadConfig(string $JsonPath)
    {
        if(file_exists($JsonPath))
        {
            $Config           = json_decode(file_get_contents($JsonPath), true);
            $this -> Host     = empty($Config["Host"]) ? $this -> Host : $Config["Host"];
            $this -> UserName = empty($Config["UserName"]) ? $this -> UserName : $Config["UserName"];
            $this -> PassWord = empty($Config["PassWord"]) ? $this -> PassWord : $Config["PassWord"];
            $this -> DB_Name  = empty($Config["DB_Name"]) ? $this -> DB_Name : $Config["DB_Name"];
            $this -> Encode   = empty($Config["Encode"]) ? $this -> Encode : $Config["Encode"];
            return true;
        }
        else return false;
    }

}
