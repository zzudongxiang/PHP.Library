<?php

# 引入mysql的库函数所在的文件夹
require_once dirname(__FILE__)."/mysql.php";

/**
 * 获取一个数据表, 注意这里只允许使用执行一条查询语句
 *
 * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
 * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
 * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
 *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
 *
 * @return array 返回sql的执行结果, 将返回的数据表
 *
 * @throws \Exception 访问出错后抛出异常
 */
function GetDataTable(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
{
    $MYSQL = new MYSQL($DB_HOST);
    return $MYSQL -> ExecuteQuery($QueryString, $Parameters)["Data"];
}

/**
 * 获取单一的查询结果, 如果有多条查询结果, 则返回第一条结果
 *
 * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
 * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
 * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
 *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
 *
 * @return string 返回sql的执行结果, 如果有多个返回结果, 则返回第一个结果
 *
 * @throws \Exception 访问出错后抛出异常
 */
function GetSingleResult(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
{
    $Result = GetDataTable($QueryString, $Parameters, $DB_HOST);
    while(is_array($Result) && count($Result) > 0) $Result = array_shift($Result);
    return $Result;
}

/**
 * 获取单一行的查询结果, 如果有多条查询结果, 则返回第一行结果
 *
 * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
 * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
 * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
 *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
 *
 * @return string 返回sql的执行结果, 如果有多个返回结果, 则返回第一行结果
 *
 * @throws \Exception 访问出错后抛出异常
 */
function GetSingleRow(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
{
    $Result = GetDataTable($QueryString, $Parameters, $DB_HOST);
    if(is_array($Result)) $Result = array_shift($Result);
    return $Result;
}

/**
 * 执行一个(或一组)sql语句, 一般为UPDATE, INSERT, DELETE语句
 *
 * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
 * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
 * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
 *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
 *
 * @return int 返回受影响的行数, 如果是多条sql语句, 则返回多条执行结果的总和
 *
 * @throws \Exception 访问出错后抛出异常
 */
function ExecuteNonQuery(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
{
    $MYSQL = new MYSQL($DB_HOST);
    return $MYSQL -> ExecuteQuery($QueryString, $Parameters)["RowCount"];
}

/**
 * 执行多条SQL语句, 要执行的sql语句以数组方式传入
 *
 * @param array         $Querys     SQL语句集合
 * @param array|null    $Parameters 参数集合
 * @param \DB_HOST|null $DB_HOST    数据库连接对象
 *
 * @return array        返回每条SQL执行的结果
 * @throws \Exception   SQL执行错误
 */
function ExecuteNonQuerys(array $Querys, array $Parameters = null, DB_HOST $DB_HOST = null)
{
    $Results = [];
    foreach($Querys as $QueryString)
    {
        $Result = ExecuteNonQuery($QueryString, $Parameters, $DB_HOST);
        array_push($Results, $Result);
    }
    return $Results;
}

/**
 * 执行一个SQL脚本
 *
 * @param string $ScriptPath SQL脚本文件所在的文件路径
 *
 * @return array|bool 返回SQL执行结果或false(文件不存在)
 * @throws \Exception 抛出SQL执行异常
 */
function ExecuteSQLScript(string $ScriptPath)
{
    if(file_exists($ScriptPath))
    {
        $Script  = file($ScriptPath);
        $Scripts = [];
        $Query   = "";
        foreach($Script as $SQL)
        {
            if(preg_match("/(^-{2})|(\/\*.*\*\/)/", trim($SQL))) continue;
            $Query .= $SQL;
            if(preg_match("/;$/", trim($SQL)))
            {
                if(!empty(trim($Query)))
                {
                    $Query = str_replace(PHP_EOL, ' ', $Query);
                    array_push($Scripts, trim($Query));
                }
                $Query = "";
            }
        }
        return ExecuteNonQuerys($Scripts);
    }
    else return false;
}
