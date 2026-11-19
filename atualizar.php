<?php

include_once "config/proteger.php";
include_once "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listar.php");
    exit;
}

$id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome      = trim($_POST["nome"] ?? '');
$categoria = trim($_POST["categoria"] ?? '');
$preco     = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
$qtd       = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

if (!$id || $id <= 0 || $nome === '' || $categoria === '' || $preco === false || $preco <= 0 || $qtd === false || $qtd < 0) {
    die("Dados inválidos para atualização. <br><a href='listar.php'>Voltar para a lista</a>");
}

try {
    // Busca o produto atual para obter o nome da imagem antiga
    $stmtBusca = $conexao->prepare("SELECT imagem FROM tb_produtos WHERE id = :id");
    $stmtBusca->execute([':id' => $id]);
    $produtoAtual = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$produtoAtual) {
        die("Produto não encontrado. <br><a href='listar.php'>Voltar para a lista</a>");
    }

    $nomeImagem = $produtoAtual['imagem'];
    $novaImagemSalva = false;
    $diretorioDestino = 'imagens/';

    // Verifica se o usuário enviou uma nova imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            die("Erro no upload da nova imagem.");
        }

        if ($_FILES['imagem']['size'] > 2 * 1024 * 1024) {
            die("A nova imagem ultrapassa o limite de 2 MB.");
        }

        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $_FILES['imagem']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($extensao, $extensoesPermitidas) || !in_array($mimeReal, ['image/jpeg', 'image/png', 'image/webp'])) {
            die("Formato de imagem inválido. Use JPG, PNG ou WEBP.");
        }

        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0755, true);
        }

        $novoNome = uniqid("Produto_") . '.' . $extensao;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorioDestino . $novoNome)) {
            die("Falha ao salvar a nova imagem.");
        }

        $nomeImagem = $novoNome;
        $novaImagemSalva = true;
    }

    $sql = "UPDATE tb_produtos SET 
                nome = :nome, 
                categoria = :categoria, 
                preco = :preco, 
                qtd = :qtd,
                imagem = :imagem
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':categoria', $categoria);
    $stmt->bindValue(':preco', $preco);
    $stmt->bindValue(':qtd', $qtd, PDO::PARAM_INT);
    $stmt->bindValue(':imagem', $nomeImagem);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    // Se uma nova imagem foi salva, apaga a antiga do servidor
    if ($novaImagemSalva && !empty($produtoAtual['imagem']) && file_exists($diretorioDestino . $produtoAtual['imagem'])) {
        unlink($diretorioDestino . $produtoAtual['imagem']);
    }

    echo "Produto atualizado com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao atualizar produto: " . $e->getMessage();
}

echo "<br><a href='listar.php'>Voltar para a lista de produtos</a>";
?>
