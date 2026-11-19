<?php

include_once "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cadastro_usuarios.html");
    exit;
}

$nome  = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '') {
    die("Preencha todos os campos obrigatórios. <br><a href='cadastro_usuarios.html'>Voltar</a>");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Informe um endereço de e-mail válido. <br><a href='cadastro_usuarios.html'>Voltar</a>");
}

try {
    $sqlVerifica = "SELECT id FROM usuarios WHERE email = :email";
    $stmtVerifica = $conexao->prepare($sqlVerifica);
    $stmtVerifica->execute([':email' => $email]);

    if ($stmtVerifica->rowCount() > 0) {
        echo "E-mail já cadastrado. <br><a href='cadastro_usuarios.html'>Voltar</a>";
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sqlInsert = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmtInsert = $conexao->prepare($sqlInsert);
    $stmtInsert->bindValue(':nome', $nome);
    $stmtInsert->bindValue(':email', $email);
    $stmtInsert->bindValue(':senha', $senhaHash);

    if ($stmtInsert->execute()) {
        echo "Usuário cadastrado com sucesso.";
        echo "<br><a href='login.html'>Fazer login</a>";
    } else {
        echo "Erro ao cadastrar usuário.";
    }
} catch (PDOException $e) {
    echo "Erro ao cadastrar usuário: " . $e->getMessage();
}
?>
