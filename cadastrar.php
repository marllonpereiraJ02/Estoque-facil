<?php

include_once "config/proteger.php";
include_once "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cadastrar.html");
    exit;
}

$nome      = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$preco     = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
$qtd       = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

if ($nome === '' || $categoria === '' || $preco === false || $preco <= 0 || $qtd === false || $qtd < 0) {
    die("Preencha todos os campos corretamente. <br><a href='cadastrar.html'>Voltar</a>");
}

// Verifica se o arquivo foi enviado sem erros
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    $erroUpload = $_FILES['imagem']['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($erroUpload === UPLOAD_ERR_INI_SIZE || $erroUpload === UPLOAD_ERR_FORM_SIZE) {
        die("O tamanho da imagem excede o limite permitido pelo servidor.");
    }

    die("Erro ao enviar a imagem. Selecione um arquivo válido e tente novamente.");
}

if ($_FILES['imagem']['size'] > 2 * 1024 * 1024) {
    die("O tamanho da imagem é muito grande (máximo de 2 MB).");
}

$extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
$extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));

if (!in_array($extensao, $extensoesPermitidas)) {
    die("Formato não permitido. Envie uma imagem .jpg, .jpeg, .png ou .webp.");
}

// Validação do tipo MIME real do arquivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeReal = finfo_file($finfo, $_FILES['imagem']['tmp_name']);
finfo_close($finfo);

$mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mimeReal, $mimesPermitidos)) {
    die("O arquivo enviado não é uma imagem válida.");
}

$diretorioDestino = 'imagens/';
if (!is_dir($diretorioDestino)) {
    mkdir($diretorioDestino, 0755, true);
}

$nomeImagem = uniqid("Produto_") . '.' . $extensao;

if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorioDestino . $nomeImagem)) {
    die("Falha ao salvar a imagem no servidor.");
}

try {
    $sql = "INSERT INTO tb_produtos (nome, qtd, categoria, preco, imagem) 
            VALUES (:nome, :qtd, :categoria, :preco, :imagem)";
    $stmt = $conexao->prepare($sql);

    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':qtd', $qtd, PDO::PARAM_INT);
    $stmt->bindValue(':categoria', $categoria);
    $stmt->bindValue(':preco', $preco);
    $stmt->bindValue(':imagem', $nomeImagem);

    $stmt->execute();

    echo "Produto cadastrado com sucesso!";
} catch (PDOException $e) {
    if (file_exists($diretorioDestino . $nomeImagem)) {
        unlink($diretorioDestino . $nomeImagem);
    }
    echo "Erro ao cadastrar produto: " . $e->getMessage();
}

echo "<br><a href='cadastrar.html'>Voltar</a> | <a href='listar.php'>Ver lista</a>";
?>
