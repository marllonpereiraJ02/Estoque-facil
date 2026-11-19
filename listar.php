<?php

include_once "config/proteger.php";
include_once "config/conexao.php";

$pesquisa = isset($_GET["pesquisa"]) ? trim($_GET["pesquisa"]) : "";  

$sql = "SELECT * FROM tb_produtos 
        WHERE nome LIKE :pesquisa OR categoria LIKE :pesquisa 
        ORDER BY id DESC";

$resultado = $conexao->prepare($sql);
$resultado->execute([':pesquisa' => "%$pesquisa%"]);
$produtos = $resultado->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos | Estoque Fácil</title>
    <link rel="stylesheet" href="style.css?v=3">
</head>

<body>

    <div class="container">

        <h1>Estoque Fácil</h1>
        <p class="subtitulo">Produtos cadastrados na loja</p>

        <div class="topo-usuario">
            <span>Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></strong></span>
            <span>|</span>
            <a href="logout.php" class="link-sair">Sair</a>
        </div>

        <div class="formulario">

            <h2>Lista de Produtos</h2>

            <form action="listar.php" method="GET" class="form-busca">
                <input type="text" name="pesquisa" 
                       value="<?php echo htmlspecialchars($pesquisa); ?>" 
                       placeholder="Pesquisar produtos...">
                <button type="submit">Buscar</button>
                <a href="listar.php">Limpar</a>
            </form>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produtos) > 0): ?>
                            <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?php echo (int) $produto["id"]; ?></td>
                                    <td>
                                        <?php if (!empty($produto["imagem"]) && file_exists("imagens/" . $produto["imagem"])): ?>
                                            <img width="70" src="imagens/<?php echo htmlspecialchars($produto["imagem"]); ?>" alt="Imagem do produto">
                                        <?php else: ?>
                                            <span>Sem imagem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($produto["nome"]); ?></td>
                                    <td><?php echo htmlspecialchars($produto["categoria"]); ?></td>
                                    <td>R$ <?php echo number_format((float) $produto["preco"], 2, ",", "."); ?></td>
                                    <td><?php echo (int) $produto["qtd"]; ?></td>
                                    <td class="acoes">
                                        <a href="editar.php?id=<?php echo (int) $produto["id"]; ?>">Editar</a>
                                        |
                                        <a href="excluir.php?id=<?php echo (int) $produto["id"]; ?>"
                                           onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="cadastrar.html" class="botao">Cadastrar Novo Produto</a>

        </div>

        <p class="rodape">Estoque Fácil © 2026 | Loja Gamer</p>

    </div>

</body>
</html>