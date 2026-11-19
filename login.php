<?php

session_start();
include_once "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    die("Preencha o e-mail e a senha. <br><a href='login.html'>Voltar</a>");
}

try {
    $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        header("Location: listar.php");
        exit;
    } else {
        echo "E-mail ou senha inválidos. <br><a href='login.html'>Tentar novamente</a>";
    }
} catch (PDOException $e) {
    echo "Erro ao realizar login: " . $e->getMessage();
}
?>
