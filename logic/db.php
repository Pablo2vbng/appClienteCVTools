<?php
$host = "localhost";
$db_name = "u249173200_cvtools"; 
$username = "u249173200_Pablo2vbngcvt";   
$password = "Piramide73++%%";        

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
} catch(PDOException $e) {
    die("Error conexión: " . $e->getMessage());
}
?>