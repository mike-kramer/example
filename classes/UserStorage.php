<?php
/**
 * Created by PhpStorm.
 * User: Mikhail
 * Date: 29.01.2017
 * Time: 11:05
 */

namespace classes;


class UserStorage
{
    const TABLE_NAME = "user";
    /** @var  \mysqli */
    private $db;

    public function __construct($container)
    {
        $this->db = $container->get("db");
    }

    public function getUser($id)
    {
        $res = $this->db->query("select * from " . self::TABLE_NAME . " where id=" . (int) $id);
        return $res->fetch_object(User::class);
    }

    public function getUserByLogin($login)
    {
        $stmt = $this->db->prepare("select * from `" . self::TABLE_NAME . "` where login=?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        return $stmt->get_result()->fetch_object(User::class);
    }

    public function addUser(User $u)
    {
        $stmt = $this->db->prepare("insert into `" . self::TABLE_NAME . "` set login=?, password=?, birthday=?");
        $stmt->bind_param("sss", $u->login, password_hash($u->password, PASSWORD_DEFAULT), $u->birthday);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function updateUserCounter(User $u)
    {
        $this->db->query("update `" . self::TABLE_NAME . "` set counter=" . (int) $u->counter . " where id=" . $u->id);
    }
}