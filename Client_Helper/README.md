# PHP-Client-Helper

PHP-Client-Helper 主要用于协助PHP程序获取来自客户端的一些设备信息



## 1. 注意: 该库文件需要读写数据库的相关操作

```php
# 1. array $DataSet = GetDataTable(string $Query, array $Param = null);
	// 查询得到一张数据表的函数
# 2. string $Result = GetSingleResult(string $Query, array $Param = null);
	// 获取一个单一结果(字符串格式)
# 3. array $DataSet = GetSingleRow(string $Query, array $Param = null);
	// 获取一行结果
# 4. ExecuteSQLScript(string $Query, array $Param = null);
	// 运行一个SLQ脚本文件
```

**该库在开发时配合 [PHP-Mysql-Helper](https://github.com/zzudongxiang/PHP-Mysql-Helper) 进行**



## 2. 使用说明

### A). 包含所需要的文件

```php
require_once "core.php" # 此处的文件路径根据项目自定义
```

### B). 初始化数据库

```php
/**
 * 初始化客户端相关的数据库数据
 *
 * @throws \Exception 初始化失败时抛出异常
 */
function InitClient()
```

**`注意:` 仅在第一次运行时需要初始化数据库, 将数据添加进数据库, 后续运行时不再需要运行该函数**

### C). 获取客户端信息

```php
/**
 * 获取相关的所有信息
 *
 * @return array 返回所有信息的列表,
 * 包含:OS, Browser, DateTime, IP, Address, UserAgent, IsMobile
 */
function GetInfo()
```

**例如:**

```php
$ClientInfo = GetInfo();

/*
* $ClientInfo = [
*		"OS"		=> "Android; Linux; ",
* 		"Browser"	=> "Chrome; FireFox; ",
*		"DateTime"	=> "2020-01-01 00:00:00",
*		"IP"		=> "127.0.0.1",
*		"Address"	=> "保留地址, 局域网地址",
*		"UserAgent" => "UserAgent",
*		"IsMobile"	=> true,
* ];
*/
```

<b style="color: red">需要注意的是: 在获取OS和Browser时或得到多个匹配的内容, 需要自行判断所需要的内容</b>

