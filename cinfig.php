<?php
// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o diretório de relatórios existe, se não, criar
if (!file_exists('relatorios')) {
    mkdir('relatorios', 0777, true);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerador de Relatórios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        .menu {
            width: 200px;
            background-color: #333;
            color: white;
            padding: 20px;
        }

        .menu a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 0;
        }

        .menu a:hover {
            color: #4CAF50;
        }

        .conteudo {
            flex: 1;
            padding: 20px;
            background-color: white;
        }

        .tabelas-campos {
            display: flex;
            margin-top: 20px;
            gap: 20px;
        }

        .tabelas, .campos {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .tabelas {
            width: 30%;
        }

        .campos {
            width: 70%;
        }

        input[type="submit"], button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        input[type="submit"]:hover, button:hover {
            background-color: #45a049;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }

        .campo-query {
            margin-top: 20px;
        }
        
        .campo-parametro {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        
        .parametros-query {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
    <script>
        function autoSubmit() {
            document.forms[0].submit();
        }
    </script>
</head>
<body>
    <div class="menu">
        <a href="config.php">Configurar DB</a>
        <a href="gerar_relatorio.php">Gerar Relatório</a>
    </div>
    <div class="conteudo">
    <h1>Configurar Banco de Dados</h1>
    <form method="post" action="config.php">

        <table>
            <tr>
                <td>Host:</td>
                <td><input type="text" name="host" required></td>
            </tr>
            <tr>
                <td>Porta:</td>
                <td><input type="number" name="porta" required></td>
            </tr>
            <tr>
                <td>Nome do Banco:</td>
                <td><input type="text" name="banco" required></td>
            </tr>
            <tr>
                <td>Usuário:</td>
                <td><input type="text" name="usuario" required></td>
            </tr>
            <tr>
                <td>Senha:</td>
                <td><input type="password" name="senha" required></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Salvar"></td>
            </tr>
        </table>
        
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['host'];
        $porta = $_POST['porta'];
        $banco = $_POST['banco'];
        $usuario = $_POST['usuario'];
        $senha = $_POST['senha'];

        $config = "<?php\n";
        $config .= "\$host = '$host';\n";
        $config .= "\$porta = '$porta';\n";
        $config .= "\$banco = '$banco';\n";
        $config .= "\$usuario = '$usuario';\n";
        $config .= "\$senha = '$senha';\n";
        file_put_contents('config_db.php', $config);
        echo "<p>Configurações salvas com sucesso!</p>";
    }
    ?>
</body>
</html>
