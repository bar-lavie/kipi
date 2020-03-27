<?php

namespace app;

use app\DB;
use PDO;

class CRUD
{
    public static function getAll()
    {
        $res =  DB::connect()->query("SELECT * FROM passwords")->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($res);
        DB::disconnect();
    }


    public static function store($newData, $id)
    {
        $connect = DB::connect();
        $sql = "UPDATE accounts a SET a.data = ? WHERE a.user_id = ?";
        $stmt = $connect->prepare($sql);
        $data = [$newData, $id];
        $status = $stmt->execute($data);
        DB::disconnect();
    }
}
