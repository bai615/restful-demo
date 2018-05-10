<?php
header('Content-type:text/html;charset=utf-8');
require __DIR__ . '/lib/User.php';
require __DIR__ . '/lib/Article.php';
$pdo = require __DIR__ . '/lib/db.php';
//$user = new User($pdo);
//var_dump($user->login('admin2', 'admin2'));
$article = new Article($pdo);
//var_dump($article->create('文章标题','文章内容',2));
//var_dump($article->view(15));
//var_dump($article->edit(15,'文章标题15','文章内容15',2));
//var_dump($article->delete(15,2));
var_dump($article->getList(2,2,2));