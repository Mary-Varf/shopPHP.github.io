<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/cookieHandler.php';
include $_SERVER['DOCUMENT_ROOT'] . '/php/serverCred.php';

$out = json_decode(file_get_contents('php://input'), true);

$connect = new mysqli($host, $user, $passwordSql, $dbname);
mysqli_set_charset($connect,'utf8'); 

if(mysqli_connect_errno()) {
    echo json_encode('Возникла ошибка, повторите попытку позже');
} else {
    if (!isset($out['email']) || !isset($out['password'])|| $out['email'] == '' || $out['password'] == '') {
        echo json_encode('Не все поля заполнены');
    } else {
        $login = $connect->real_escape_string($out['email']);
        $password = $connect->real_escape_string($out['password']);
        $productID = $connect->real_escape_string($out['productID']);
    
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $result = mysqli_query($connect, "SELECT login, name, password, users.id, ru.roles_id FROM users
        left join role_user as ru on users.id = ru.users_id
         where login='$login';");
        if($result && $result->num_rows > 0) {
            $data = $result->fetch_array();
            if (password_verify($password, $data['password'])) {
                cookieHandler\createCookie($data);
                cookieHandler\setSession($data);
                
                if (count($out) > 2) {
                    $data['productID'] = $productID;
                }
                echo json_encode($data);    
    
            } else {
                echo json_encode('Не верная пара логин и пароль');
            }
        } else {
            echo json_encode('Такого логина не существует');
        }
    }


}
mysqli_close($connect);