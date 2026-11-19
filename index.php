<?php

session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: listar.php");
} else {
    header("Location: login.html");
}
exit;
?>
