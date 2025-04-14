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
        <h1>Bem-vindo ao Gerador de Relatórios</h1>
        <p>Selecione uma opção no menu.</p>
    </div>
</body>
</html>
