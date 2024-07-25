<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
require_once("./PDOconnect.php");
if (isset($_GET['token'])) {
    $jwt = $_GET['token'];
    $check = explode('.', $jwt);
    if (count($check) == 3) {
        list($base64Header, $base64Payload, $base64Signature) = explode('.', $jwt);
        $secretKey = '897564231';

        $validSignature = hash_hmac('sha256', $base64Header . $base64Payload, $secretKey);

        if (hash_equals($validSignature, $base64Signature)) {
            echo "true";
        } else {
            echo "false";
        }
        return;
    } else {
        echo "false";
        return;
    }
}
function JWT()
{
    $header = array('alg' => 'HS256', 'typ' => 'JWT');
    $payload = array(
        'iss' => "Shop.com",
        'exp' => "20241231",
        'name' => "Mr.Lin",
    );
    $header = base64_encode(json_encode($header, JSON_UNESCAPED_UNICODE));
    $payload = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
    $secretKey = "897564231";

    $signature = hash_hmac('sha256', $header . $payload, $secretKey);
    return $header . '.' . $payload . '.' . $signature;
}
if (isset($_POST['do'])) {
    if ($_POST['do'] == 'login' && isset($_POST['account']) && isset($_POST['password'])) {
        $SQLstring = sprintf("SELECT * FROM member WHERE BINARY m_account = '%s' AND BINARY m_password = '%s'", $_POST['account'], $_POST['password']);
        $find = $link->query($SQLstring);
        $member = $find->fetch();
        if ($member) {
            $array = ["m_name" => $member["m_name"], "m_account" => $member["m_account"], "checklogin" => true, "token" => JWT()];
        } else {
            $array = ["checklogin" => false];
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_POST['do'] == 'getProfile') {
        $SQLstring = sprintf("SELECT * FROM member WHERE BINARY m_account = '%s'", $_POST['m_account']);
        $find = $link->query($SQLstring);
        $member = $find->fetch();
        $array = array(
            "m_account" => $member["m_account"],
            "m_password" => $member["m_password"],
            "m_name" => $member["m_name"],
            "m_birthday" => $member["m_birthday"],
            "m_tel" => $member["m_tel"],
            "m_phone" => $member["m_phone"],
            "m_email" => $member["m_email"],
            "m_address" => $member["m_address"],
            "m_join_date" => $member["create_date"]
        );
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_POST['do'] == 'updateProfile') {
        $SQLstring = sprintf(
            "UPDATE member SET m_name = '%s', m_birthday = '%s', m_tel = '%s', m_phone = '%s', m_email = '%s', m_address = '%s' WHERE m_account = '%s'",
            $_POST['m_name'],
            $_POST['m_birthday'],
            $_POST['m_tel'],
            $_POST['m_phone'],
            $_POST['m_email'],
            $_POST['m_address'],
            $_POST['m_account']
        );
        $rs = $link->exec($SQLstring);
        echo true;
        return;
    }
    if ($_POST['do'] == 'orderInfo' && isset($_POST['m_account'])) {
        $SQLstring = sprintf("SELECT * FROM member WHERE BINARY m_account = '%s'", $_POST['m_account']);
        $find = $link->query($SQLstring);
        $orderInfo = $find->fetch();
        $array = ["m_account" => $orderInfo["m_account"], "m_name" => $orderInfo["m_name"], "m_phone" => $orderInfo["m_phone"], "m_address" => $orderInfo["m_address"]];
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_POST['do'] == "checkout") {
        $o_id = date("#YmdGis");
        $o_member = $_POST['orderInfo'];
        $account = $o_member['o_account'];
        $name = $o_member['o_name'];
        $address = $o_member['o_address'];
        $phone = $o_member['o_phone'];
        $note = $o_member['o_note'];
        $total = $o_member['o_total'];
        $delivery = $o_member['o_delivery'];
        $deliveryStatus = $o_member['o_deliveryStatus'];
        $SQLstring = sprintf("INSERT INTO member_order(o_id, o_account, o_name, o_address, o_phone, o_note, o_total, o_delivery, o_deliveryStatus, o_item) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')", $o_id, $account, $name, $address, $phone, $note, $total, $delivery, $deliveryStatus, $_POST['cartInfo']);
        $add = $link->query($SQLstring);
        if($add){
            echo true; 
        }else{
            echo false; 
        }
        return;
    }
    if($_POST['do'] == "orderSummary" && isset($_POST['o_account'])) {
        $SQLstring = sprintf("SELECT * FROM member_order WHERE BINARY o_account = '%s'", $_POST['o_account']);
        $find = $link->query($SQLstring);
        $array = [];
        while($order_row = $find->fetch()){
            $row = array(
                "o_id" => $order_row['o_id'],
                "o_deliveryStatus" => $order_row['o_deliveryStatus'],
                "o_create" => $order_row['o_create'],
                "o_delivery" => $order_row['o_delivery'],
                "o_total" => $order_row['o_total']
            );
            array_push($array, $row);
        };
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if($_POST['do'] == "orderDetail" && isset($_POST['o_id']) && isset($_POST['o_account'])){
        $SQLstring = sprintf("SELECT * FROM member_order WHERE BINARY o_account = '%s' AND o_id = '%s'", $_POST['o_account'], $_POST['o_id']);
        $find = $link->query($SQLstring);
        $orderdetail = $find->fetch();
        $array = array(
            "o_id" => $orderdetail['o_id'],
            "o_deliveryStatus" => $orderdetail['o_deliveryStatus'],
            "o_create" => $orderdetail['o_create'],
            "o_delivery" => $orderdetail['o_delivery'],
            "o_total" => $orderdetail['o_total'],
            "o_item" => $orderdetail['o_item'],
            "o_note" => $orderdetail['o_note'],
            "o_phone" => $orderdetail['o_phone'],
            "o_address" => $orderdetail['o_address'],
            "o_account" => $orderdetail['o_account'],
            "o_name" => $orderdetail['o_name'],
        );
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
}
