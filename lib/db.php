<?php
/**
 * 连接数据库并返回数据库连接句柄
 */
$pdo = new PDO('mysql:host=localhost;dbname=restful_db;charset=UTF8', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
return $pdo;