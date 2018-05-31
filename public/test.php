<?php 
if (!isset($_SESSION)) {
  session_start();
}//初始化SESSION

	echo $_SESSION['ext_user'];
?>