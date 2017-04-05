<?php
use classes\User;
use classes\UserStorage;
use sndsgd\Form;
use sndsgd\form\field\StringField;
use sndsgd\form\rule\ClosureRule;
use sndsgd\form\rule\RequiredRule;
use sndsgd\form\Validator;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . "/db_conf.php";


$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ]
]);
// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer(__DIR__ . "/template/");
};

$container["db"] = function ($container) {
    $db = new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8");
    return $db;
};

$container["userStorage"] = function ($container) {
    return new UserStorage($container);
};

$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

$app->get("/", function ($request, $response, $args) {
    if (!isset($_SESSION["user_id"])) {
        return $response->withStatus(302)->withHeader('Location', '/login');
    }
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $name = $request->getAttribute($nameKey);
    $value = $request->getAttribute($valueKey);
    $user = $this->userStorage->getUser($_SESSION["user_id"]);
    return $this->view->render($response, "home.php", [
        "user" => $user,
        "nameKey" => $nameKey,
        "valueKey" => $valueKey,
        "name" => $name,
        "value" => $value
    ]);
})->add($container->get('csrf'));
$app->post("/", function ($request, $response, $args) {
    if (isset($_POST["logout"])) {
        unset($_SESSION["user_id"]);
        return $response->withStatus(302)->withHeader('Location', '/login');
    }
    $user = $this->userStorage->getUser($_SESSION["user_id"]);
    $user->counter++;
    $this->userStorage->updateUserCounter($user);
    return $response->withStatus(302)->withHeader('Location', '/');
})->add($container->get('csrf'));

$loggedInMid = new \classes\middleware\LoggedIn();
$app->get("/login", function ($request, $response, $args) {
    return $this->view->render($response, "login.php", ["error" => ""]);
})->add($loggedInMid);
$app->post("/login", function ($request, $response, $args) {
    if (isset($_POST["register"])) {
        return $response->withStatus(302)->withHeader('Location', '/register');
    }
    $err = false;
    if (empty($_POST["login"]) || empty($_POST["password"])) {
        $err = "Необходимо ввести логин и пароль";
    } else {
        $user = $this->userStorage->getUserByLogin($_POST["login"]);
        if ($user && password_verify($_POST["password"], $user->password)) {
            $_SESSION["user_id"] = $user->id;
        }
        else {
            $err = "Неверные имя пользователя или пароль";
        }
    }

    if ($err) {
        return $this->view->render($response, "login.php", ["error" => $err]);
    }
    else {
        return $response->withStatus(302)->withHeader('Location', '/');
    }
})->add($loggedInMid);;

$app->get("/register", function ($request, $response, $args) {
    return $this->view->render($response, "register.php", ["error" => ""]);
})->add($loggedInMid);;

$app->post("/register", function ($request, $response, $args) {
    $form = new Form();
    /* @var \DateTime $birthday */
    $birthday = null;
    $form->addFields(
        (new StringField("login"))
            ->addRule(new RequiredRule())
            ->addRule(new ClosureRule(function ($value, sndsgd\form\Validator $validator = null) : bool {
                $user = $this->userStorage->getUserByLogin($value);
                if ($user) {
                    $validator->addError("login", "Логин не уникален");
                    return false;
                }
                return true;
            })),
        (new StringField("password"))
            ->addRule(new RequiredRule()),
        (new StringField("birthday"))
            ->addRule(new RequiredRule())
            ->addRule(new ClosureRule(function ($value, Validator $validator = null) use(&$birthday) : bool  {
                $d = date_parse_from_format("d.m.Y", $value);
                if ($d["error_count"] != 0) {
                    $validator->addError("birthday", "Неверный формат даты");
                    return false;
                }
                if (!checkdate($d["month"], $d["day"], $d["year"])) {
                    $validator->addError("birthday", "Неверная дата");
                    return false;
                }
                $birthday = \DateTime::createFromFormat("d.m.Y", $value);
                $now = new DateTime();
                $diff = $now->diff($birthday);
                if ($diff->format("r") === "-") {
                    $validator->addError("birthday", "Вы ещё не родились!");
                    return false;
                }
                if ($diff->y < 5) {
                    $validator->addError("birthday", "Too young!");
                    return false;
                }
                if ($diff->y > 150) {
                    $validator->addError("birthday", "Too old!");
                    return false;
                }
                return true;
            }))
    );
    $validator = new Validator($form);
    try {
        unset($_POST["register"]);
        $validator->validate($_POST);
        $u = new User;
        $u->login    = $_POST["login"];
        $u->password = $_POST["password"];
        $u->birthday = $birthday->format("Y-m-d");
        $_SESSION["user_id"] = $this->userStorage->addUser($u);
        return $response->withStatus(302)->withHeader('Location', '/');
    } catch (\sndsgd\form\ValidationException $ex) {
        $messages = array_map(function ($e) {return $e->getMessage();}, $ex->getErrors());
        return $this->view->render($response, "register.php", ["error" => implode("<br>", $messages)]);
    }
})->add($loggedInMid);;

$app->group("/csrf", function () {
    $this->get("/", \classes\Controller\Test::class . ":testGen");
    $this->post("/", \classes\Controller\Test::class . ":testCheck");
})->add($container->get("csrf"));

session_start();
$app->run();