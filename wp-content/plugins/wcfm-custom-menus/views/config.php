<?php
$host = "localhost"; /* Host name */
$user = "gigant_gigant"; /* User */
$password = "XDX&WkowfvJ["; /* Password */
$dbname = "gigant_new"; /* Database name */

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}