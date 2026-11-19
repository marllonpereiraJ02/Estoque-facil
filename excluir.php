<?php

include_once "config/proteger.php";
include_once "config/conexao.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: listar.php");
    exit;
}

try {
    // Busca a imagem antes de excluir o registro
    $stmtBusca = $conexao->prepare("SELECT imagem FROM tb_produtos WHERE id = :id");
    $stmtBusca->execute([':id' => $id]);
    $produto = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo "Produto não encontrado.";
        echo "<br><a href='listar.php'>Voltar para a lista de produtos</a>";
        exit;
    }

    $sql = "DELETE FROM tb_produtos WHERE id = :id";
    $stmt = $conexao->prepare($sql);

    if ($stmt->execute([':id' => $id])) {
        // Remove o arquivo de imagem da pasta
        if (!empty($produto['imagem']) && file_exists("imagens/" . $produto['imagem'])) {
            unlink("imagens/" . $produto['imagem']);
        }
        echo "Produto excluído com sucesso!";
    } else {
        echo "Erro ao excluir produto.";
    }
} catch (PDOException $e) {
    echo "Erro ao excluir produto: " . $e->getMessage();
}

echo "<br><a href='listar.php'>Voltar para a lista de produtos</a>";

?>
