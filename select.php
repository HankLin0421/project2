<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
// header('Content-Type:application/json;charset=utf-8');
require_once("./PDOconnect.php");
if (isset($_GET['do'])) {
    //class
    if ($_GET['do'] == "class") {
        $SQLstring = "SELECT * FROM p_class WHERE level = 1 ORDER BY sort";
        $Parentclass = $link->query($SQLstring);
        $array = [];
        while ($p_row = $Parentclass->fetch()) {
            $p_array = ["id" => $p_row["id"], "class" => $p_row["class"], "cname" => $p_row["cname"], "childclass" => [], "active" => false];
            $SQLstring = sprintf("SELECT * FROM p_class WHERE level = 2 AND uplink = '%s' ORDER BY sort", $p_row["class"]);
            $Childclass = $link->query($SQLstring);
            while ($c_row = $Childclass->fetch()) {
                $c_arry = ["id" => $c_row["id"], "class" => $c_row["class"], "cname" => $c_row["cname"], "uplink" => $c_row["uplink"]];
                array_push($p_array["childclass"], $c_arry);
            }
            array_push($array, $p_array);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_GET['do'] == "productlist" && isset($_GET['class'])) {
        $SQLstring = sprintf("SELECT * FROM product,product_img WHERE product_img.sort = 1 AND product.p_id = product_img.p_id AND product.class = '%s' AND product_img.class = '%s'", $_GET['class'], $_GET['class']);
        $Parentclass = $link->query($SQLstring);
        $array = [];
        while ($p_row = $Parentclass->fetch()) {
            $p_array = ["p_id" => $p_row["p_id"], "class" => $p_row["class"], "p_name" => $p_row["p_name"], "p_intro" => $p_row["p_intro"], "p_price" => $p_row["p_price"], "p_image" => $p_row["p_image"]];
            array_push($array, $p_array);
        };
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_GET['do'] == "search" && isset($_GET['searchText'])) {
        $SQLstring = sprintf("SELECT * FROM product,product_img WHERE product_img.sort = 1 AND product.p_id = product_img.p_id AND product.p_intro LIKE '%s' ORDER BY product.p_id ASC", '%' . $_GET['searchText'] . '%');
        $Search = $link->query($SQLstring);
        $array = [];
        while ($s_row = $Search->fetch()) {
            $s_array = ["p_id" => $s_row["p_id"], "class" => $s_row["class"], "p_name" => $s_row["p_name"], "p_intro" => $s_row["p_intro"], "p_price" => $s_row["p_price"], "p_image" => $s_row["p_image"]];
            array_push($array, $s_array);
        };
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_GET['do'] == "product" && isset($_GET['p_id'])) {
        $SQLstring = sprintf("SELECT * FROM product,product_img WHERE product.p_id = %d AND product_img.sort = 1 AND product.p_id = product_img.p_id", $_GET['p_id']);
        $product = $link->query($SQLstring);
        $p_row = $product->fetch();
        $array = ["p_id" => $p_row["p_id"], "class" => $p_row["class"], "p_name" => $p_row["p_name"], "p_intro" => $p_row["p_intro"], "p_price" => $p_row["p_price"], "p_content" => $p_row["p_content"], "p_image" => $p_row["p_image"], "all_image" => []];
        $SQLstring = sprintf("SELECT * FROM product_img WHERE product_img.p_id = %d ORDER BY sort", $_GET['p_id']);
        $imgList = $link->query($SQLstring);
        while ($i_row = $imgList->fetch()) {
            $i_array = ["img_name" => $i_row['p_image'], "sort_id" => $i_row['sort']];
            array_push($array['all_image'], $i_array);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_GET['do'] == "cart" && isset($_GET['p_id']) && isset($_GET['qty'])) {
        $SQLstring = sprintf("SELECT * FROM product,product_img WHERE product.p_id = %d AND product_img.sort = 1 AND product.p_id = product_img.p_id", $_GET['p_id']);
        $product = $link->query($SQLstring);
        $p_row = $product->fetch();
        $array = ["p_id" => $p_row["p_id"], "class" => $p_row["class"], "p_name" => $p_row["p_name"], "p_price" => $p_row["p_price"], "qty" => intval($_GET['qty']), "p_image" => $p_row["p_image"]];
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($_GET['do'] == "article" && isset($_GET['uplink'])) {
        $SQLstring = sprintf("SELECT * FROM p_class WHERE uplink = '%s'", $_GET['uplink']);
        $find = $link->query($SQLstring);
        $class_Arr = [];
        while ($class = $find->fetch()) {
            array_push($class_Arr, $class['class']);
        }
        $array= [];
        foreach ($class_Arr as $class) {
            $SQLstring = sprintf("SELECT * FROM product,product_img WHERE product_img.sort = 1 AND product.p_id = product_img.p_id AND product.class = '%s' AND product_img.class = '%s'", $class, $class);
            $find = $link->query($SQLstring);
            while($product = $find->fetch()) {
                $product_row = array(
                    "p_id" => $product['p_id'],
                    "p_name" => $product['p_name'],
                    "p_image" => $product['p_image'],
                    "p_class" => $product['class'],
                );
                array_push($array, $product_row);
            }
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        return;
    }
}
