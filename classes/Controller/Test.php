<?php
/**
 * Created by PhpStorm.
 * User: Mikhail
 * Date: 05.04.2017
 * Time: 15:52
 */

namespace classes\Controller;


use Slim\Container;
use Slim\Csrf\Guard;
use Slim\Http\Request;
use Slim\Http\Response;

class Test
{
    /** @var Guard */
    private $csrf;

    public function __construct(Container $c)
    {
        $this->csrf = $c->get("csrf");
    }

    public function testGen(Request $request, Response $response, $args)
    {
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);
        $response->getBody()->write(<<<RESP
            <form method='post' action='$_SERVER[REQUEST_URI]'>
                <input name="$nameKey" value="$name"><br>
                <input name="$valueKey" value="$value"><br>
                <input type="submit">
            </form>
RESP
);
    }

    public function testCheck(Request $request, Response $response, $args)
    {
        $response->getBody()->write("CSRF OK");
    }
}