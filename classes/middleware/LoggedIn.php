<?php
/**
 * Created by PhpStorm.
 * User: Mikhail
 * Date: 29.01.2017
 * Time: 14:10
 */

namespace classes\middleware;


class LoggedIn
{
    public function __invoke($request, $response, $next)
    {
        if ($_SESSION["user_id"]) {
            return $response->withStatus(302)->withHeader('Location', '/');
        }
        $next($request, $response);
        return $response;
    }
}