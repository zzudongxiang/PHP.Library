## 1. 需要的依赖项

```bash
apt install -y php-mysql php-mysqli
```

## 2. 数据库连接相关参数设置

```text
edit ./json/mysql.php
```

```json
{
    "Host": "localhost", 
    "UserName": "root",
    "PassWord": "******",
    "DB_Name": "mysql",
    "Encode": "utf8"
}
```

## 3. 使用说明

### A). 包含所需要的文件

```php
require_once "core.php"; # 此处的文件路径根据项目自定义
```

### B). mysql参数的格式说明

```text
参数格式为: 冒号+参数名(+AT符号+参数类型) 括号内部分可省略
参数类型可选: d/D(Int类型), b/B(Bool类型), s/S(String类型)
例如: 
:Param 		-> (指定一个参数Param, 类型为默认字符串类型)
:Param@d 	-> (指定一个参数Param, 类型为double类型)

整体格式为:
$Param = [":Args_1"=>"Args", ":Args_2@d"=>"1", ":Args_3@S"=>"Args_String"];
```

### C). 相关函数说明

#### ----- 增, 删, 改操作 -----

> 1). 执行一个不需要返回值的SQL语句
>
> ```php
> /**
>  * 执行一个(或一组)sql语句, 一般为UPDATE, INSERT, DELETE语句
>  *
>  * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
>  * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
>  * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
>  *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
>  *
>  * @return int 返回受影响的行数, 如果是多条sql语句, 则返回多条执行结果的总和
>  *
>  * @throws \Exception 访问出错后抛出异常
>  */
> function ExecuteNonQuery(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
> ```
>
>  **例如:**
>
> ```php
> $Query = "INSERT INTO tb (ID) VALUE (:ID);";
> $Param = [":ID@d"=>1];
> ExecuteNonQuery($Query, $Param); # 这里如果需要可以手动进行异常处理
> ```



> 2). 指定多条不需要返回值的SQL语句
>
> ```php
> /**
>  * 执行多条SQL语句, 要执行的sql语句以数组方式传入
>  *
>  * @param array         $Querys     SQL语句集合
>  * @param array|null    $Parameters 参数集合
>  * @param \DB_HOST|null $DB_HOST    数据库连接对象
>  *
>  * @return array        返回每条SQL执行的结果
>  * @throws \Exception   SQL执行错误
>  */
> function ExecuteNonQuerys(array $Querys, array $Parameters = null, DB_HOST $DB_HOST = null)
> ```
>
> **例如:**
>
> ```php
> $Query = ["INSERT INTO tb (ID) VALUE (:ID);",
>           "UPDATE tb SET ID = :NewID WHERE ID = :ID"];
> $Param = [":ID@d"=>1, ":NewID"=>2];
> ExecuteNonQuerys($Query, $Param); # 这里如果需要可以手动进行异常处理
> ```



#### ----- 查询操作 -----

> 1). 获取一个数据表
>
> ```php
> /**
>  * 获取一个数据表, 注意这里只允许使用执行一条查询语句
>  *
>  * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
>  * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
>  * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
>  *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
>  *
>  * @return array 返回sql的执行结果, 将返回的数据表
>  *
>  * @throws \Exception 访问出错后抛出异常
>  */
> function GetDataTable(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
> ```
>
> **例如:**
>
> ```php
> $Query  = "SELECT * FROM tb WHERE ID = :ID";
> $Param  = [":ID@d"=>1];
> $Result = GetDataTable($Query, $Param); # 这里如果需要可以手动进行异常处理
> # $Result = [
> 	0=>[Col_1=>1, Col_2=>2], 
> 	1=>[...]]
> ```



> 2). 获取一整行结果
>
> ```php
> /**
>  * 获取单一行的查询结果, 如果有多条查询结果, 则返回第一行结果
>  *
>  * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
>  * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
>  * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
>  *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
>  *
>  * @return string 返回sql的执行结果, 如果有多个返回结果, 则返回第一行结果
>  *
>  * @throws \Exception 访问出错后抛出异常
>  */
> function GetSingleRow(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
> ```
>
> **例如:**
>
> ```php
> $Query  = "SELECT * FROM tb WHERE ID = :ID";
> $Param  = [":ID@d"=>1];
> $Result = GetSingleRow($Query, $Param); # 这里如果需要可以手动进行异常处理
> # $Result = [Col_1=>1, Col_2=>2]
> ```



> 3). 获取一个单一结果
>
> ```php
> /**
>  * 获取单一的查询结果, 如果有多条查询结果, 则返回第一条结果
>  *
>  * @param \DB_HOST|null $DB_HOST     用于连接数据库对象的参数
>  * @param string        $QueryString 要执行的sql语句, 其中变量部分可以使用 :Name@Type 代替
>  * @param array         $Parameters  对应的参数列表, 参数格式为: ":Name$Type"
>  *                                   例如: :ID@d 当未指定类型时, 将自动补充为字符串类型
>  *
>  * @return string 返回sql的执行结果, 如果有多个返回结果, 则返回第一个结果
>  *
>  * @throws \Exception 访问出错后抛出异常
>  */
> function GetSingleResult(string $QueryString, array $Parameters = null, DB_HOST $DB_HOST = null)
> ```
>
> **例如:**
>
> ```php
> $Query  = "SELECT * FROM tb WHERE ID = :ID";
> $Param  = [":ID@d"=>1];
> $Result = GetSingleResult($Query, $Param); # 这里如果需要可以手动进行异常处理
> # $Result = "Result String"
> ```



#### ----- 运行SQL脚本 -----

> 执行一个SQL脚本文件
> ```php
> /**
>  * 执行一个SQL脚本
>  *
>  * @param string $ScriptPath SQL脚本文件所在的文件路径
>  *
>  * @return array|bool 返回SQL执行结果或false(文件不存在)
>  * @throws \Exception 抛出SQL执行异常
>  */
> function ExecuteSQLScript(string $ScriptPath)
> ```
>
> **例如:**
>
> ```php
> ExecuteNonQuerys("script.sql"); # 这里如果需要可以手动进行异常处理
> ```
>
