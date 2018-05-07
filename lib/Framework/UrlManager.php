<?php

namespace hx\Lib\Framework;

use hx\Helper\MConfig;

class UrlManager {

    private static $_pathInfo;

    private static $_scriptUrl;

    private static $_baseUrl;

    private static $_url;

    private static $_scriptFile;

    /**
     * Returns the path info of the currently requested URL.
     * A path info refers to the part that is after the entry script and before the question mark (query string).
     * The starting and ending slashes are both removed.
     * @return string part of the request URL that is after the entry script and before the question mark.
     * Note, the returned path info is already URL-decoded.
     * @throws \Exception if the path info cannot be determined due to unexpected server configuration
     */
    public static function getPathInfo()
    {
        if (self::$_pathInfo === null) {
            self::$_pathInfo = self::resolvePathInfo();
        }

        return self::$_pathInfo;
    }

    protected static function resolvePathInfo()
    {
        $pathInfo = self::getUrl();

        if (($pos = strpos($pathInfo, '?')) !== false) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }

        $pathInfo = urldecode($pathInfo);

        // try to encode in UTF8 if not so
        // http://w3.org/International/questions/qa-forms-utf-8.html
        if (!preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $pathInfo)
        ) {
            $pathInfo = utf8_encode($pathInfo);
        }

        $scriptUrl = self::getScriptUrl();
        $baseUrl = self::getBaseUrl();
        if (strpos($pathInfo, $scriptUrl) === 0) {
            $pathInfo = substr($pathInfo, strlen($scriptUrl));
        } elseif ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0) {
            $pathInfo = substr($pathInfo, strlen($baseUrl));
        } elseif (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0) {
            $pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
        } else {
            throw new \Exception('Unable to determine the path info of the current request.');
        }

        if (substr($pathInfo, 0, 1) === '/') {
            $pathInfo = substr($pathInfo, 1);
        }

        if ($pathInfo) $pathInfo = MConfig::get('urlManager')[$pathInfo];

        return (string) $pathInfo;
    }

    /**
     * Returns the currently requested relative URL.
     * This refers to the portion of the URL that is after the [[hostInfo]] part.
     * It includes the [[queryString]] part if any.
     * @return string the currently requested relative URL. Note that the URI returned is URL-encoded.
     * @throws \Exception if the URL cannot be determined due to unusual server configuration
     */
    public static function getUrl()
    {
        if (self::$_url === null) {
            self::$_url = self::resolveRequestUri();
        }

        return self::$_url;
    }

    /**
     * Resolves the request URI portion for the currently requested URL.
     * This refers to the portion that is after the [[hostInfo]] part. It includes the [[queryString]] part if any.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string|boolean the request URI portion for the currently requested URL.
     * Note that the URI returned is URL-encoded.
     * @throws \Exception if the request URI cannot be determined due to unusual server configuration
     */
    protected static function resolveRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new \Exception('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    /**
     * Returns the relative URL of the entry script.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string the relative URL of the entry script.
     * @throws \Exception if unable to determine the entry script URL
     */
    public static function getScriptUrl()
    {
        if (self::$_scriptUrl === null) {
            $scriptFile = self::getScriptFile();
            $scriptName = basename($scriptFile);
            if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                self::$_scriptUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $scriptName) {
                self::$_scriptUrl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                self::$_scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (isset($_SERVER['PHP_SELF']) && ($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
                self::$_scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            } elseif (!empty($_SERVER['DOCUMENT_ROOT']) && strpos($scriptFile, $_SERVER['DOCUMENT_ROOT']) === 0) {
                self::$_scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $scriptFile));
            } else {
                throw new \Exception('Unable to determine the entry script URL.');
            }
        }

        return self::$_scriptUrl;
    }

    /**
     * Returns the entry script file path.
     * The default implementation will simply return `$_SERVER['SCRIPT_FILENAME']`.
     * @return string the entry script file path
     * @throws \Exception
     */
    public static function getScriptFile()
    {
        if (isset(self::$_scriptFile)) {
            return self::$_scriptFile;
        } elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
            return $_SERVER['SCRIPT_FILENAME'];
        } else {
            throw new \Exception('Unable to determine the entry script file path.');
        }
    }

    /**
     * Returns the relative URL for the application.
     * This is similar to [[scriptUrl]] except that it does not include the script file name,
     * and the ending slashes are removed.
     * @return string the relative URL for the application
     * @see setScriptUrl()
     */
    public static function getBaseUrl()
    {
        if (self::$_baseUrl === null) {
            self::$_baseUrl = rtrim(dirname(self::getScriptUrl()), '\\/');
        }

        return self::$_baseUrl;
    }

    /**
     * 获取控制器    @lynn
     * @param $namespace
     * @param $id
     * @param $actionName
     * @return null
     */
    public static function createControllerByID( $namespace, $id ,$actionName)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $className = ltrim( $namespace . '\\' . str_replace('/', '\\', $prefix)  . $className, '\\');
        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }
		//对静态变量action进行初始化
        $className::$action = $actionName;
        $controller = new $className;
        return get_class($controller) === $className ? $controller : null;
    }

    /**
     * 执行方法     @lynn
     * @param $controller
     * @param $id
     * @throws \Exception
     */
    public static function runAction( $controller,$id ){
        $methodName = $id.'Action';
        if (method_exists( $controller, $methodName)) {
            $args = [];
            call_user_func_array( [ $controller, $methodName], $args);
        }
        else {
            header('HTTP/1.1 404 Not Found');
            throw new \Exception('No such action.');
        }
    }


}