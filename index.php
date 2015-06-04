<?php
require_once 'api.php';
require_once 'db.php';

$users = new api('users');
$auth = new api('auth');


$users->getOne(function($id) {    
    $user = db_user::getInstance()->getOne($id);
    api::$status = 200;
    echo $user ? json_encode($user): json_encode([]);
});
$users->getAll(function() {
    db_user::getInstance()->log();
    $users = db_user::getInstance()->getAll();
    api::$status = 200;
    echo json_encode($users);
    
});
$users->create(function($user) {
    if(!isset($user->name)){
        api::$status = 400;
        return;
    }
    $inserted_user = db_user::getInstance()->insert($user);    
    if($inserted_user){
        api::$status = 200;
        echo json_encode($inserted_user);
    } else {
        api::$status = 409;
    }    
});
$users->edit(function($id, $user) {
    if(!isset($user->name)){
        api::$status = 400;
        return;
    }
    $user = db_user::getInstance()->update($id, $user);
    if(!$user){
        api::$status = 409;
        return;
    }
    api::$status = 200;
    echo json_encode($user);
});
$users->delete(function($id) {
    db_user::getInstance()->delete($id);
    api::$status = 200;
});



// AUTH 
$auth->create(function($credentials) {
//      UNCOMMENT THIS WHEN HTTPS WILL BE CONFIGURED    
//    if (!isset($_SERVER['HTTPS']) || !($_SERVER['HTTPS'] != 'off')) {
//        api::$status = 400;
//        return;
//    }
    
    if (!isset($credentials->name)) {
        api::$status = 400;
        return;
    }
    //get user from DB
    $user = db_user::getInstance()->login($credentials->name);
    global $secret_key;
    $hash = ejor2eb($user, $secret_key);
    api::$status = 200;
    echo json_encode(['authorization' => $hash]);    
});
api::dispatch();
db_user::getInstance()->close_connection();