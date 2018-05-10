<?php
//var_dump($_SERVER);

require __DIR__ . '/../lib/User.php';
require __DIR__ . '/../lib/Article.php';
$pdo = require __DIR__ . '/../lib/db.php';

class Restful
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @var Article
     */
    private $_article;

    /**
     * 请求方法
     * @var string
     */
    private $_requestMethod;

    /**
     * 请求的资源名称
     * @var string
     */
    private $_resourceName;

    /**
     * 请求的资源ID
     * @var
     */
    private $_id;

    /**
     * 允许请求的资源列表
     * @var array
     */
    private $_allowResources = ['users', 'articles'];

    /**
     * 允许请求的 HTTP 方法
     * @var array
     */
    private $_allowRequestMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

    /**
     * 常用状态码
     * @var array
     */
    private $_statusCodes = [
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server Internal Error'
    ];

    /**
     * Restful constructor.
     * @param User $_user
     * @param Article $_article
     */
    public function __construct(User $_user, Article $_article)
    {
        $this->_user = $_user;
        $this->_article = $_article;
    }

    /**
     *
     */
    public function run()
    {
        try {
            $this->_setupRequestMethod();
            $this->_setupResource();
//            $this->_setupId();
            if ($this->_resourceName == 'users') {
                $this->_json($this->_handleUser());
            } else {
                $this->_json($this->_handleArticle());
            }
        } catch (Exception $exception) {
//            $this->_json(['errmsg' => $exception->getMessage(), 'errcode' => $exception->getCode()]);
            $this->_json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * 初始化请求方法
     */
    private function _setupRequestMethod()
    {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->_requestMethod, $this->_allowRequestMethods)) {
            throw new Exception('请求方法不被允许', 405);
        }
    }

    /**
     * 初始化请求资源
     */
    private function _setupResource()
    {
//        var_dump($_SERVER);
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/', $path);
        $this->_resourceName = isset($params[1]) ? $params[1] : '';
        if (!in_array($this->_resourceName, $this->_allowResources)) {
            throw new Exception('请求资源不被允许', 403);
        }
        if (!empty($params[2])) {
            $this->_id = $params[2];
        }
//        var_dump($path);
//        var_dump($params);
    }

    /**
     * 初始化请求资源标识符
     */
    private function _setupId()
    {
    }

    /**
     * 输出JSON
     * @param $array
     */
    private function _json($array, $code = 0)
    {
        if ($array === null && $code === 0) {
            $code = 204;
        }
        if ($array !== null && $code === 0) {
            $code = 200;
        }
        header('HTTP/1.1 ' . $code . ' ' . $this->_statusCodes[$code]);
        header('Content-type:application/json;charset=utf-8');
        if ($array !== null) {
            echo json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    /**
     * 请求用户
     *
     * http://demo.api.com/restful/users/
     * Content-Type   application/json
     * 请求测试数据：{"username":"admin4","password":"admin4"}
     */
    private function _handleUser()
    {
        if ($this->_requestMethod != 'POST') {
            throw new Exception('请求方法不被允许', 405);
        }
        $body = $this->_getBodyParams();
        if (empty($body['username'])) {
            throw new Exception('用户名不能为空', 400);
        }
        if (empty($body['password'])) {
            throw new Exception('密码不能为空', 400);
        }
        return $this->_user->register($body['username'], $body['password']);
    }

    /**
     * 请求文章资源
     *
     * http://demo.api.com/restful/articles
     * Content-Type   application/json
     * Authorization      Basic YWRtaW4zOmFkbWluMw==   (备注：base64(username:password)编码)
     * {"title":"articel title test","content":"article content test"}
     */
    private function _handleArticle()
    {
        switch ($this->_requestMethod) {
            case 'POST':
                return $this->_handleArticleCreate();
            case 'PUT':
                return $this->_handleArticleEdit();
            case 'DELETE':
                return $this->_handleArticleDelete();
            case 'GET':
                if (empty($this->_id)) {
                    return $this->_handleArticleList();
                } else {
                    return $this->_handleArticleView();
                }
            default:
                throw new Exception('请求不被允许', 405);
        }
    }

    /**
     * 获取请求体
     * @return mixed
     * @throws Exception
     */
    private function _getBodyParams()
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            throw new Exception('请求参数错误', 400);
        }
        return json_decode($raw, true);
    }

    /**
     * 创建文章
     */
    private function _handleArticleCreate()
    {
        $body = $this->_getBodyParams();
        if (empty($body['title'])) {
            throw new Exception('文章标题不能为空', 400);
        }
        if (empty($body['content'])) {
            throw new Exception('文章内容不能为空', 400);
        }

        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        try {
            $article = $this->_article->create($body['title'], $body['content'], $user['user_id']);
            return $article;
        } catch (Exception $exception) {
            if (in_array($exception->getCode(),
                [
                    ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY,
                    ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY
                ]
            )) {
                throw new Exception($exception->getMessage(), 400);
            }
            throw new Exception($exception->getMessage(), 500);
        }
    }

    /**
     * 编辑文章
     */
    private function _handleArticleEdit()
    {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        try {
            $article = $this->_article->view($this->_id);
            if ($article['user_id'] != $user['user_id']) {
                throw  new Exception('您无权编辑', 403);
            }
            $body = $this->_getBodyParams();
            $title = empty($body['title']) ? $article['title'] : $body['title'];
            $content = empty($body['content']) ? $article['content'] : $body['content'];
            if ($title == $article['title'] && $content == $article['content']) {
                return $article;
            }
            return $this->_article->edit($this->_id, $title, $content, $user['user_id']);
        } catch (Exception $exception) {
            if ($exception->getCode() < 100) {
                if ($exception->getCode() == ErrorCode::ARTICLE_NOT_FOUND) {
                    throw new Exception($exception->getMessage(), 404);
                } else {
                    throw new Exception($exception->getMessage(), 400);
                }
            } else {
                throw $exception;
            }
        }
    }

    /**
     * 文章删除
     */
    private function _handleArticleDelete()
    {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        try {
            $article = $this->_article->view($this->_id);
            if ($article['user_id'] != $user['user_id']) {
                throw  new Exception('您无权编辑', 403);
            }
            $this->_article->delete($this->_id, $user['user_id']);
            return null;
        } catch (Exception $exception) {
            if ($exception->getCode() < 100) {
                if ($exception->getCode() == ErrorCode::ARTICLE_NOT_FOUND) {
                    throw new Exception($exception->getMessage(), 404);
                } else {
                    throw new Exception($exception->getMessage(), 400);
                }
            } else {
                throw $exception;
            }
        }
    }

    /**
     * 文章列表
     */
    private function _handleArticleList()
    {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $size = isset($_GET['size']) ? $_GET['size'] : 10;
        if ($size > 100) {
            throw new Exception('分页大小最大100', 400);
        }
        return $this->_article->getList($user['user_id'], $page, $size);
    }

    /**
     * 查看文章
     */
    private function _handleArticleView()
    {
        try {
            return $this->_article->view($this->_id);
        } catch (Exception $exception) {
            if ($exception->getCode() == ErrorCode::ARTICLE_NOT_FOUND) {
                throw new Exception($exception->getMessage(), 404);
            } else {
                throw new Exception($exception->getMessage(), 500);
            }
        }
    }

    /**
     * 用户登录
     * @param $PHP_AUTH_USER
     * @param $PHP_AUTH_PW
     * @return mixed
     * @throws Exception
     */
    private function _userLogin($PHP_AUTH_USER, $PHP_AUTH_PW)
    {
        try {
            return $this->_user->login($PHP_AUTH_USER, $PHP_AUTH_PW);
        } catch (Exception $exception) {
            if (in_array($exception->getCode(),
                [
                    ErrorCode::USERNAME_CANNOT_EMPTY,
                    ErrorCode::PASSWORD_CANNOT_EMPTY,
                    ErrorCode::USERNAME_OR_PASSWORD_INVALID
                ]
            )) {
                throw new Exception($exception->getMessage(), 400);
            }
            throw new Exception($exception->getMessage(), 500);
        }
    }
}

$user = new User($pdo);
$article = new Article($pdo);

$restful = new Restful($user, $article);
$restful->run();