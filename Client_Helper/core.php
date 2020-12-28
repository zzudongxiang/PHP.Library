<?php # 需要在 ./lib.php 文件下配置数据库操作函数

# 引入相关的库文件
require_once dirname(__FILE__)."/client.php";

#
/**
 * 获取相关的所有信息
 *
 * @return array 返回所有信息的列表,
 * 包含:OS, Browser, DateTime, IP, Address, UserAgent, IsMobile
 */
function GetInfo()
{
    $Client = new CLIENT();
    return $Client -> Info;
}

/**
 * 初始化客户端相关的数据库数据
 *
 * @throws \Exception 初始化失败时抛出异常
 */
function InitClient()
{
    CLIENT_InitClient();
}