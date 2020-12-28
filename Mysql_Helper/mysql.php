<?php

# 引入DB_HOST文件的路径
require_once dirname(__FILE__)."/db_host.php";

/**
 * Class MYSQL
 */
class MYSQL
{

    /**
     * 数据库连接对象对应的成员变量
     *
     * @var \DB_HOST
     */
    protected $DB_HOST;

    /**
     * MYSQL constructor. 获取数据库连接参数
     *
     * @param \DB_HOST $DB_HOST 传入的$DB_HOST对象,
     *                          如果为空, 则调用默认的对象
     */
    public function __construct(DB_HOST $DB_HOST = null)
    {
        if(empty($DB_HOST)) $this -> DB_HOST = new DB_HOST();
        else $this -> DB_HOST = $DB_HOST;
    }

    /**
     * 绑定sql语句中的参数, 一般防止sql注入攻击,
     * 不会把sql中的变量直接拼接为sql语句,
     * 而是通过传入参数的方法进行拼接
     *
     * @param PDOStatement $Query      sql语句的PDO对象, 用于对其添加数据变量
     * @param array        $Parameters 要添加的参数列表, 参数格式为: ":Name$Type"
     *                                 例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
     *
     * @return void 返回添加参数的sqlPDO对象
     *
     * @throws \Exception 访问出错后抛出异常
     */
    protected function BindParam(PDOStatement &$Query, array $Parameters)
    {
        foreach($Parameters as $Key => &$Value)
        {
            $Key = str_replace(" ", "", $Key);
            if(preg_match("/^:[a-zA-Z_][a-zA-Z0-9_]*(@[dbsDBS])?$/", $Key))
            {
                $Fragment = explode("@", $Key);
                $Key      = $Fragment[0];
                $Types    = PDO::PARAM_STR;
                $Match    = ["d" => PDO::PARAM_INT,
                             "b" => PDO::PARAM_BOOL,
                             "s" => PDO::PARAM_STR,];
                if(count($Fragment) > 1)
                {
                    $TypeStr = strtolower($Fragment[1]);
                    foreach($Match as $SubKey => $SubValue)
                    {
                        if($TypeStr == $SubKey)
                        {
                            $Types = $SubValue;
                            break;
                        }
                    }
                }
                if(strpos($Query -> queryString, $Key)) $Query -> bindParam($Key, $Value, $Types);
            }
            else throw new Exception("参数类型应符合格式 :Name@Type");
        }
    }

    /**
     * 执行sql语句, 不区分sql的语句类型(即不区分增删改查操作)
     * 如果要执行的sql语句为多条, 则返回的结果会在一个list集合中
     * 如果多条sql语句中有一条执行错误, 将会在该语句处抛出异常
     *
     * @param string $QueryString      要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
     * @param array  $Parameters       对应的参数列表, 参数格式为: ":Name$Type"
     *                                 例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
     *
     * @return array 返回sql的执行结果,
     *               如果有多条sql语句, 则返回结果中的Data数据为一个List
     *               返回的结果中RowCount数据为所有语句执行的结果总和
     *
     * @throws \Exception 访问出错后抛出异常
     */
    public function ExecuteQuery(string $QueryString, array $Parameters = null)
    {
        $Connection = $this -> DB_HOST -> GetConnection();
        $Result     = ["Data"     => array(),
                       "RowCount" => 0];
        if($Query = $Connection -> prepare($QueryString))
        {
            if(!empty($Parameters)) $this -> BindParam($Query, $Parameters);
            if($Query -> execute())
            {
                while($Row = $Query -> fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) array_push($Result["Data"], $Row);
                $Result["RowCount"] += $Query -> rowCount();
            }
            else throw new Exception($Query -> errorInfo()[2]." 请检查: $QueryString");
        }
        else throw new Exception($Query -> errorInfo()[2]);
        $Connection = null;
        return $Result;
    }
}