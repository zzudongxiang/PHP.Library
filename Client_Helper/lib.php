<?php # 需要在此文件下配置数据库操作函数

# 此处需要引入mysql的相关操作, 需要支持的mysql函数为:
# 1. array $DataSet = GetDataTable(string $Query, array $Param = null);
# 2. string $Result = GetSingleResult(string $Query, array $Param = null);
# 3. array $DataSet = GetSingleRow(string $Query, array $Param = null);
# 4. ExecuteSQLScript(string $Query, array $Param = null);

require_once dirname(__FILE__)."/../../.config/default.php";

/**
 * Class CLIENT_LIB 操作关于客户端信息的相关方法
 */
class CLIENT_LIB
{
    /**
     * 根据获取的完整信息组合OS的详细信息, 并记录到数据库中
     *
     * @param array $Info 完整的信息
     *
     * @return string 返回[OS, Browse]信息
     */
    protected function GetOSInfo(array $Info)
    {
        $OS = "Unknow OS";
        if(!empty($Info["OS"])) $OS = $Info["OS"];
        if(!empty($Info["Phone"])) $OS = $OS." | ".$Info["Phone"];
        if(!empty($Info["Tablet"])) $OS = $OS." | ".$Info["Tablet"];
        if(!empty($Info["Utilitie"])) $OS = $OS." | ".$Info["Utilitie"];
        return $OS;
    }

    /**
     * 从数据库中获取匹配信息, 如果搜索失败, 尝试新建数据库
     *
     * @param string $Method 操作方法, 如果是Create, 将不再尝试新建表
     *                       一般使用默认值即可
     *
     * @return array 返回对应的信息
     */
    protected function GetInfo(string $Method = "Search")
    {
        try
        {
            $Query  = "SELECT `Type`, GROUP_CONCAT(`Value`, '; ') AS `Value` FROM (SELECT * FROM `HTTP.UA` WHERE :UserAgent REGEXP BINARY `Regex` ORDER BY `Weight` DESC) AS TMP GROUP BY `Type`;";
            $Param  = [":UserAgent" => $_SERVER['HTTP_USER_AGENT']];
            $Data   = GetDataTable($Query, $Param);
            $Result = [];
            if(!empty($Data)) foreach($Data as $Row) $Result[$Row["Type"]] = $Row["Value"];
            return ["OS"       => empty($Result["OS"]) ? "Unknow" : $Result["OS"],
                    "Browser"  => empty($Result["Browser"]) ? "Unknow" : $Result["Browser"],
                    "Phone"    => empty($Result["Phone"]) ? "" : $Result["OS"],
                    "Tablet"   => empty($Result["Tablet"]) ? "" : $Result["Tablet"],
                    "Utilitie" => empty($Result["Utilitie"]) ? "" : $Result["Utilitie"]];
        }
        catch(Exception $Ex)
        {
            if($Method == "Create") return ["OS" => "Unknown ({$Ex -> getMessage()})"];
            try
            {
                $Query  = "SHOW TABLES LIKE :TableName;";
                $Param  = [":TableName" => "HTTP.UA"];
                $Result = GetSingleResult($Query, $Param);
                if(!empty($Result)) throw $Ex;
                else
                {
                    ExecuteSQLScript(dirname(__FILE__)."/sql/HTTP_UA.sql");
                    return $this -> GetInfo("Create");
                }
            }
            catch(Exception $Ex)
            {
                return ["OS" => "Unknown ({$Ex -> getMessage()})"];
            }
        }
    }

    /**
     * 根据访客的信息获取访问者的IP地址
     *
     * @return mixed|string 返回对应的IP
     */
    protected function GetIP()
    {
        $IPFormat = '#^((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3}$#';
        $UserIP   = preg_match($IPFormat, $_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1";
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $Matches))
        {
            foreach($Matches[0] as $XIP)
            {
                if(!preg_match('#^(10|172\.16|192\.168)\.#', $XIP))
                {
                    $UserIP = $XIP;
                    break;
                }
            }
        }
        else
        {
            $IPInfo = ["HTTP_CLIENT_IP", "HTTP_CF_CONNECTING_IP", "HTTP_X_REAL_IP",];
            foreach($IPInfo as $IP)
            {
                if(isset($_SERVER[$IP]) && preg_match($IPFormat, $_SERVER[$IP]))
                {
                    $UserIP = $_SERVER[$IP];
                    break;
                }
            }
        }
        return preg_match($IPFormat, $UserIP) ? $UserIP : "127.0.0.1";
    }

    /**
     * 根据访客的IP地址, 从数据库中查询得到真实的定位信息
     *
     * @param string $IP     输入的IP地址
     *
     * @param string $Method 操作方法, 如果是Create, 将不再尝试新建表
     *                       一般使用默认值即可
     *
     * @return string  返回真实的定位地址
     */
    protected function GetAddress(string $IP, string $Method = "Search")
    {
        try
        {
            $Query                   = "SELECT * FROM `HTTP.IP` WHERE INET_ATON(:UserIP) BETWEEN INET_ATON(`StartIP`) AND INET_ATON(`EndIP`);";
            $Parameter               = [":UserIP" => $IP];
            $AddressInfo             = (array)GetSingleRow($Query, $Parameter);
            $AddressInfo["Province"] = empty($AddressInfo["Province"]) ? "Unknow" : $AddressInfo["Province"];
            $AddressInfo["City"]     = empty($AddressInfo["City"]) ? "" : $AddressInfo["City"];
            if(empty($AddressInfo)) return "Unknown";
            else return $AddressInfo["Province"].", ".$AddressInfo["City"];
        }
        catch(Exception $Ex)
        {
            if($Method == "Create") return "Unknown ({$Ex -> getMessage()})";
            try
            {
                $Query  = "SHOW TABLES LIKE :TableName;";
                $Param  = [":TableName" => "HTTP.IP"];
                $Result = GetSingleResult($Query, $Param);
                if(!empty($Result)) throw $Ex;
                else
                {
                    ExecuteSQLScript(dirname(__FILE__)."/sql/HTTP_UA.sql");
                    return $this -> GetAddress($IP, "Create");
                }
            }
            catch(Exception $Ex)
            {
                return "Unknown ({$Ex -> getMessage()})";
            }
        }
    }
}

/**
 * 初始化客户端相关的数据库数据
 *
 * @throws \Exception 初始化失败时抛出异常
 */
function CLIENT_InitClient()
{
    $SQLScripts = ["HTTP_UA.sql", "HTTP_UA.sql"];
    foreach($SQLScripts as $Script)
    {
        try
        {
            ExecuteSQLScript(dirname(__FILE__)."/sql/".$Script);
        }
        catch(Exception $Ex)
        {
            throw $Ex;
        }
    }
}