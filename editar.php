<?php

include_once "config/proteger.php";
include_once "config/conexao.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: listar.php");
    exit;
}

$sql = "SELECT * FROM tb_produtos WHERE id = :id";
$stmt = $conexao->prepare($sql);
$stmt->execute([':id' => $id]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header("Location: listar.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto | Estoque Fácil</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">

        <h1>Estoque Fácil</h1>
        <p class="subtitulo">Editar produto</p>

        <div class="formulario">

            <h2>Alterar Produto</h2>

            <form action="atualizar.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="id" value="<?php echo (int) $produto["id"]; ?>">

                <label for="nome">Nome do produto:</label>
                <input type="text" id="nome" name="nome"
                       value="<?php echo htmlspecialchars($produto["nome"]); ?>"
                       required>

                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione uma categoria</option>
                    <option value="Periféricos" <?php if ($produto["categoria"] == "Periféricos") echo "selected"; ?>>Periféricos</option>
                    <option value="Componentes" <?php if ($produto["categoria"] == "Componentes") echo "selected"; ?>>Componentes</option>
                    <option value="Monitores" <?php if ($produto["categoria"] == "Monitores") echo "selected"; ?>>Monitores</option>
                    <option value="Acessórios" <?php if ($produto["categoria"] == "Acessórios") echo "selected"; ?>>Acessórios</option>
                </select>

                <label for="preco">Preço de venda (R$):</label>
                <input type="number" id="preco" name="preco"
                       min="0.01" step="0.01"
                       value="<?php echo htmlspecialchars($produto["preco"]); ?>"
                       required>

                <label for="quantidade">Quantidade em estoque:</label>
                <input type="number" id="quantidade" name="quantidade"
                       min="0"
                       value="<?php echo (int) $produto["qtd"]; ?>"
                       required>

                <label>Imagem atual:</label>
                <?php if (!empty($produto["imagem"]) && file_exists("imagens/" . $produto["imagem"])): ?>
                    <div style="margin-bottom: 10px;">
                        <img width="100" src="imagens/<?php echo htmlspecialchars($produto["imagem"]); ?>" alt="Imagem atual">
                    </div>
                <?php else: ?>
                    <p style="color: #aaa; font-size: 13px;">Sem imagem cadastrada.</p>
                <?php endif; ?>

                <label for="imagem">Alterar imagem (opcional):</label>
                <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp">

                <button type="submit">Salvar alterações</button>

            </form>

        </div>

        <p class="rodape">
            <a href="listar.php" style="color: #39ff88;">Voltar para a lista</a>
        </p>

    </div>

</body>
</html>
