<?php
/*
 * Copyright (c) 2022.
 * User: Fesdam
 * project: WizarFrameWork
 * Date Created: $file.created
 * 6/30/22, 6:30 PM
 * Last Modified at: 6/30/22, 6:30 PM
 * Time: 6:30
 * @author Wizarphics <Wizarphics@gmail.com>
 *
 */

namespace wizarphics\wizarframework;

use Throwable;
use wizarphics\wizarframework\db\Database;

class Application
{
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';

    protected array $eventListeners = [];

    public static string $ROOT_DIR;
    public static string $CORE_DIR;

    public string $layout = 'main';

    public string $userClass;
    public static Application $app;
    public ?Controller $controller = null;
    public Request $request;
    public Router $router;
    public Response $response;
    public Database $db;
    public Session $session;
    public ?UserModel $user;

    public View $view;

    public function __construct($rootPath, array $config)
    {
        $this->userClass = $config['userClass'];
        self::$ROOT_DIR = $rootPath;
        self::$CORE_DIR = (__DIR__) . DIRECTORY_SEPARATOR;
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response);
        $this->view = new View();

        $this->db = new Database($config['db']);

        $primaryValue = $this->session->getValue('user');
        if ($primaryValue) {
            $userClassInstance = new $this->userClass;
            $primaryKey = $userClassInstance->primaryKey();
            $this->user = $userClassInstance->findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }

    public function handleExceptions(Throwable $e)
    {
        log_message('error', [$e->getMessage(), $e->getTraceAsString()]);
        $code = $e->getCode();
        if (is_string($code)) {
            $code = 500;
        }

        if (is_cli()) :
            echo $e->getCode();
            exit;
        else :
            $this->response->setStatusCode($code);
            if (file_exists(VIEWPATH . '_errors/_' . $code . '.php'))
                echo $this->view->renderView('_errors/_' . $code, [
                    'exception' => $e
                ]);
            else
                echo $this->view->renderView('_errors/_exceptions', [
                    'exception' => $e
                ]);
        endif;
    }

    public function run()
    {
        set_exception_handler([$this, 'handleExceptions']);
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);
        try {
            echo $this->router->resolve();
        }catch(Throwable $e){
            $this->handleExceptions($e);
        }
        $this->triggerEvent(self::EVENT_AFTER_REQUEST);
    }

    public function triggerEvent($eventName)
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    public function on($eventName, $callback)
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    /**
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function login(UserModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }
}
