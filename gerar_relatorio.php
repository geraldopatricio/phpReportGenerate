<?php
// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o diretório de relatórios existe, se não, criar
if (!file_exists('relatorios')) {
    mkdir('relatorios', 0777, true);
}

function safe_html($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
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
        <h1>Gerador de Relatórios</h1>
        
        <form method="post" action="gerar_relatorio.php">
            <h2>Escolha o método gerador:</h2>
            <input type="radio" name="metodo" value="tabela" id="tabela" 
                <?php echo (isset($_POST['metodo']) && $_POST['metodo'] === 'tabela' ? 'checked' : ''); ?>
                onclick="setTimeout(autoSubmit, 100)">
            <label for="tabela">Tabela</label><br>
            
            <input type="radio" name="metodo" value="query" id="query" 
                <?php echo (isset($_POST['metodo']) && $_POST['metodo'] === 'query' ? 'checked' : '' ); ?>
                onclick="setTimeout(autoSubmit, 100)">
            <label for="query">Query</label><br>

            <?php
            // Etapa 1: Mostrar tabelas se método for 'tabela'
            if (isset($_POST['metodo']) && $_POST['metodo'] === 'tabela') {
                include 'config_db.php';
                $conn = new mysqli($host, $usuario, $senha, $banco, $porta);
                
                if ($conn->connect_error) {
                    die("Erro de conexão: " . $conn->connect_error);
                }
                
                echo '<div class="tabelas-campos">';
                echo '<div class="tabelas">';
                echo '<h3>Escolha a tabela:</h3>';
                
                $result = $conn->query("SHOW TABLES");
                if (!$result) {
                    die("Erro ao listar tabelas: " . $conn->error);
                }
                
                while ($row = $result->fetch_row()) {
                    /*
                    echo '<input type="radio" name="tabela" value="' . htmlspecialchars($row[0]) . '" 
                          id="t_' . htmlspecialchars($row[0]) . '" 
                          ' . (isset($_POST['tabela']) && $_POST['tabela'] === $row[0] ? 'checked' : '') . '
                          onclick="autoSubmit()">';
                    echo '<label for="t_' . htmlspecialchars($row[0]) . '">' . htmlspecialchars($row[0]) . '</label><br>';
                    */
                    echo '<input type="radio" name="tabela" value="' . htmlspecialchars($row[0] ?? '') . '" 
                        id="t_' . htmlspecialchars($row[0] ?? '') . '" 
                        ' . (isset($_POST['tabela']) && $_POST['tabela'] === $row[0] ? 'checked' : '') . '
                        onclick="autoSubmit()">';
                    echo '<label for="t_' . htmlspecialchars($row[0] ?? '') . '">' . htmlspecialchars($row[0] ?? '') . '</label><br>';
                }
                
                echo '</div>';
                
                // Etapa 2: Mostrar campos se tabela foi selecionada
                if (isset($_POST['tabela'])) {
                    $tabela = $conn->real_escape_string($_POST['tabela']);
                    $result = $conn->query("DESCRIBE $tabela");
                    
                    if (!$result) {
                        die("Erro ao descrever tabela: " . $conn->error);
                    }
                    
                    echo '<div class="campos">';
                    echo '<h3>Selecione os campos para filtrar (máx. 4):</h3>';
                    
                    // Lista todos os campos
                    $campos_tabela = array();
                    while ($row = $result->fetch_assoc()) {
                        $campos_tabela[] = $row['Field'];
                    }
                    
                    // Mostra todos os campos da tabela
                    echo '<h4>Campos da tabela:</h4>';
                    echo '<ul>';
                    foreach ($campos_tabela as $campo) {
                        echo '<li>' . htmlspecialchars($campo) . '</li>';
                    }
                    echo '</ul>';
                    
                    // Mostra opções para selecionar até 4 campos como parâmetros
                    echo '<h4>Selecione até 4 campos para usar como filtros:</h4>';
                    for ($i = 0; $i < 4; $i++) {
                        echo '<div class="campo-parametro">';
                        echo '<select name="campos[]">';
                        echo '<option value="">-- Selecione um campo --</option>';
                        foreach ($campos_tabela as $campo) {
                            echo '<option value="' . htmlspecialchars($campo) . '" ' . 
                                 (isset($_POST['campos'][$i]) && $_POST['campos'][$i] === $campo ? 'selected' : '') . '>' . 
                                 htmlspecialchars($campo) . '</option>';
                        }
                        echo '</select> ';
                        
                        echo '<select name="operadores[]">';
                        echo '<option value="=" ' . (isset($_POST['operadores'][$i]) && $_POST['operadores'][$i] === '=' ? 'selected' : '') . '>= (Igual)</option>';
                        echo '<option value="LIKE" ' . (isset($_POST['operadores'][$i]) && $_POST['operadores'][$i] === 'LIKE' ? 'selected' : '') . '>LIKE (Contém)</option>';
                        echo '<option value=">" ' . (isset($_POST['operadores'][$i]) && $_POST['operadores'][$i] === '>' ? 'selected' : '') . '>> (Maior que)</option>';
                        echo '<option value="<" ' . (isset($_POST['operadores'][$i]) && $_POST['operadores'][$i] === '<' ? 'selected' : '') . '>< (Menor que)</option>';
                        echo '</select>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                echo '</div>'; // fecha tabelas-campos
                
                if (isset($_POST['tabela'])) {
                    echo '<input type="submit" name="mostrar_campos" value="Continuar">';
                }
            }
            
            // Etapa 1: Mostrar campo para query se método for 'query'
elseif (isset($_POST['metodo']) && $_POST['metodo'] === 'query') {
                echo '<div class="campo-query">';
                echo '<h3>Insira sua consulta SQL:</h3>';
                echo '<p>Use <strong>?</strong> para indicar parâmetros que serão transformados em campos de filtro</p>';
                echo '<textarea name="query" rows="6" cols="80" placeholder="Exemplo: SELECT * FROM clientes WHERE nome LIKE ? AND cidade = ?">' . 
                     // (isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '') . '</textarea><br>';
                     (isset($_POST['query']) ? htmlspecialchars($_POST['query'] ?? '') : '') . '</textarea><br>';
                echo '<input type="submit" name="enviar_query" value="Continuar">';
                echo '</div>';
                
                // Se já foi enviada uma query, verificar se precisa de parâmetros
                if (isset($_POST['enviar_query']) && !empty($_POST['query'])) {
                    // Contar quantos parâmetros (?) existem na query
                    $num_parametros = substr_count($_POST['query'], '?');
                    
                    if ($num_parametros > 0) {
                        // Verificar se a query já tem operadores definidos (LIKE, =, etc.)
                        $query = strtolower($_POST['query']);
                        $has_operators = (strpos($query, ' like ?') !== false || 
                                         strpos($query, ' = ?') !== false ||
                                         strpos($query, ' > ?') !== false ||
                                         strpos($query, ' < ?') !== false);
                        
                        if ($has_operators) {
                            // Se a query já tem operadores, só pedir os nomes dos parâmetros
                            echo '<div class="parametros-query">';
                            echo '<h3>Nomeie os parâmetros:</h3>';
                            
                            for ($i = 1; $i <= $num_parametros; $i++) {
                                echo '<div class="campo-parametro">';
                                echo '<label for="param_nome_' . $i . '">Nome do parâmetro ' . $i . ':</label> ';
                                echo '<input type="text" name="param_nomes[]" id="param_nome_' . $i . '" required placeholder="Ex: nome_cliente">';
                                echo '</div>';
                            }
                            
                            // Adicionar campos hidden com operadores padrão (serão ignorados)
                            for ($i = 0; $i < $num_parametros; $i++) {
                                echo '<input type="hidden" name="param_operadores[]" value="=">';
                            }
                        } else {
                            // Se não tem operadores, pedir ambos (nomes e operadores)
                            echo '<div class="parametros-query">';
                            echo '<h3>Configurar parâmetros:</h3>';
                            
                            for ($i = 1; $i <= $num_parametros; $i++) {
                                echo '<div class="campo-parametro">';
                                echo '<label for="param_nome_' . $i . '">Nome do parâmetro ' . $i . ':</label> ';
                                echo '<input type="text" name="param_nomes[]" id="param_nome_' . $i . '" required placeholder="Ex: nome_cliente"> ';
                                
                                echo '<label for="param_operador_' . $i . '">Operador:</label> ';
                                echo '<select name="param_operadores[]" id="param_operador_' . $i . '">';
                                echo '<option value="=">= (Igual)</option>';
                                echo '<option value="LIKE">LIKE (Contém)</option>';
                                echo '<option value=">">> (Maior que)</option>';
                                echo '<option value="<">< (Menor que)</option>';
                                echo '</select>';
                                echo '</div>';
                            }
                        }
                    } else {
                        echo '<p>Sua query não contém parâmetros. Será gerado um relatório sem filtros.</p>';
                    }
                }
            }
            ?>

            <?php
            // Etapa final: Gerar relatório
            if (isset($_POST['mostrar_campos']) || (isset($_POST['enviar_query']) && (!isset($_POST['query']) || !empty($_POST['query'])))) {
                echo '<h3>Configurações finais</h3>';
                echo '<label for="nome_relatorio">Nome do relatório:</label><br>';
                echo '<input type="text" name="nome_relatorio" id="nome_relatorio" required><br><br>';
                echo '<input type="submit" name="gerar_relatorio" value="Gerar Relatório">';
            }
            
            // Processar geração do relatório
            if (isset($_POST['gerar_relatorio'])) {
                $nome_relatorio = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['nome_relatorio']);
                $conteudo_php = "<?php\n";
                $conteudo_php .= "include '../config_db.php';\n";
                $conteudo_php .= "\$conn = new mysqli(\$host, \$usuario, \$senha, \$banco, \$porta);\n";
                $conteudo_php .= "if (\$conn->connect_error) die(\"Erro de conexão: \" . \$conn->connect_error);\n\n";
                
                if (isset($_POST['tabela']) && isset($_POST['campos'])) {
                    // Código para gerar relatório a partir de tabela (como antes)
                    $tabela = $_POST['tabela'];
                    $campos = $_POST['campos'];
                    $operadores = $_POST['operadores'];
                    
                    // Lista todos os campos da tabela para o SELECT
                    $conn = new mysqli($host, $usuario, $senha, $banco, $porta);
                    $result = $conn->query("DESCRIBE $tabela");
                    $todos_campos = array();
                    while ($row = $result->fetch_assoc()) {
                        $todos_campos[] = $row['Field'];
                    }
                    $lista_campos = implode(', ', $todos_campos);
                    
                    $conteudo_php .= "\$sql = \"SELECT $lista_campos FROM $tabela\";\n";
                    $conteudo_php .= "\$where = array();\n";
                    
                    foreach ($campos as $i => $campo) {
                        if (empty($campo)) continue;
                        
                        $operador = $operadores[$i];
                        $conteudo_php .= "if (!empty(\$_GET['$campo'])) {\n";
                        $conteudo_php .= "    \$valor = \$conn->real_escape_string(\$_GET['$campo']);\n";
                        
                        if ($operador === 'LIKE') {
                            $conteudo_php .= "    \$where[] = \"$campo LIKE '%\$valor%'\";\n";
                        } else {
                            $conteudo_php .= "    \$where[] = \"$campo $operador '\$valor'\";\n";
                        }
                        
                        $conteudo_php .= "}\n";
                    }
                    
                    $conteudo_php .= "if (!empty(\$where)) {\n";
                    $conteudo_php .= "    \$sql .= \" WHERE \" . implode(\" AND \", \$where);\n";
                    $conteudo_php .= "}\n";
                    
                } elseif (isset($_POST['query'])) {
                    // Código para gerar relatório a partir de query personalizada
                    $query = $_POST['query'];
                    
                    // Extrair a parte antes do WHERE (query base)
                    $query_base = preg_replace('/\s+WHERE\s+.*$/i', '', $query);
                    
                    // Extrair condições WHERE
                    preg_match('/WHERE\s+(.*)$/i', $query, $matches);
                    $where_conditions = $matches[1] ?? '';
                    
                    // Extrair parâmetros (campos com ?)
                    preg_match_all('/(\w+)\s+(LIKE|=|>|<)\s+\?/i', $where_conditions, $param_matches, PREG_SET_ORDER);
                    
                    $conteudo_php .= "// Query base\n";
                    $conteudo_php .= "\$sql = \"$query_base\";\n";
                    $conteudo_php .= "\$where = array();\n";
                    
                    foreach ($param_matches as $match) {
                        $campo = $match[1];
                        $operador = strtoupper($match[2]);
                        $param_name = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $campo));
                        
                        $conteudo_php .= "if (!empty(\$_GET['$param_name'])) {\n";
                        $conteudo_php .= "    \$valor = \$conn->real_escape_string(\$_GET['$param_name']);\n";
                        
                        if ($operador === 'LIKE') {
                            $conteudo_php .= "    \$where[] = \"$campo LIKE '%\$valor%'\";\n";
                        } else {
                            $conteudo_php .= "    \$where[] = \"$campo $operador '\$valor'\";\n";
                        }
                        
                        $conteudo_php .= "}\n";
                    }
                    
                    $conteudo_php .= "if (!empty(\$where)) {\n";
                    $conteudo_php .= "    \$sql .= \" WHERE \" . implode(\" AND \", \$where);\n";
                    $conteudo_php .= "}\n";
                }
                
                // Restante do código para gerar a interface com DataTables (igual ao anterior)
                $conteudo_php .= "\n// Executa a consulta\n";
                $conteudo_php .= "\$result = \$conn->query(\$sql);\n";
                $conteudo_php .= "if (!\$result) die(\"Erro na consulta: \" . \$conn->error);\n\n";
                
                $conteudo_php .= "// HTML inicial com DataTables\n";
                $conteudo_php .= "echo '<!DOCTYPE html>';\n";
                $conteudo_php .= "echo '<html>';\n";
                $conteudo_php .= "echo '<head>';\n";
                $conteudo_php .= "echo '    <title>Relatório: $nome_relatorio</title>';\n";
                $conteudo_php .= "echo '    <link rel=\"stylesheet\" type=\"text/css\" href=\"https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css\">';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js\"></script>';\n";
                $conteudo_php .= "echo '    <script type=\"text/javascript\" src=\"https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js\"></script>';\n";

                $conteudo_php .= "echo '<style>';\n";
                $conteudo_php .= "echo 'body {';\n";
                $conteudo_php .= "echo '    font-family: \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;';\n";
                $conteudo_php .= "echo '    font-size: 12px;';\n";
                $conteudo_php .= "echo '    font-weight: 300;';\n";
                $conteudo_php .= "echo '    line-height: 1.5;';\n";
                $conteudo_php .= "echo '    color: #333;';\n";
                $conteudo_php .= "echo '    padding: 20px;';\n";
                $conteudo_php .= "echo '    background-color: #f8fafc;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'h1 {';\n";
                $conteudo_php .= "echo '    font-size: 14px;';\n";
                $conteudo_php .= "echo '    font-weight: bold;';\n";
                $conteudo_php .= "echo '    color: #2c3e50;';\n";
                $conteudo_php .= "echo '    margin-bottom: 20px;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'form {';\n";
                $conteudo_php .= "echo '    background: white;';\n";
                $conteudo_php .= "echo '    padding: 20px;';\n";
                $conteudo_php .= "echo '    border-radius: 4px;';\n";
                $conteudo_php .= "echo '    box-shadow: 0 2px 4px rgba(0,0,0,0.05);';\n";
                $conteudo_php .= "echo '    margin-bottom: 20px;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'label {';\n";
                $conteudo_php .= "echo '    display: block;';\n";
                $conteudo_php .= "echo '    margin-bottom: 5px;';\n";
                $conteudo_php .= "echo '    font-weight: 400;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'input[type=\"text\"] {';\n";
                $conteudo_php .= "echo '    padding: 8px 12px;';\n";
                $conteudo_php .= "echo '    border: 1px solid #ddd;';\n";
                $conteudo_php .= "echo '    border-radius: 2px;';\n";
                $conteudo_php .= "echo '    width: 200px;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'input[type=\"submit\"] {';\n";
                $conteudo_php .= "echo '    background-color:rgb(69, 69, 70);';\n";
                $conteudo_php .= "echo '    color: white;';\n";
                $conteudo_php .= "echo '    padding: 8px 16px;';\n";
                $conteudo_php .= "echo '    border: none;';\n";
                $conteudo_php .= "echo '    border-radius: 8px;';\n";
                $conteudo_php .= "echo '    cursor: pointer;';\n";
                $conteudo_php .= "echo '    font-size: 12px;';\n";
                $conteudo_php .= "echo '    transition: background-color 0.3s;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'input[type=\"submit\"]:hover {';\n";
                $conteudo_php .= "echo '    background-color: #2980b9;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'table.dataTable {';\n";
                $conteudo_php .= "echo '    border-collapse: collapse;';\n";
                $conteudo_php .= "echo '    width: 100%;';\n";
                $conteudo_php .= "echo '    margin-top: 10px;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'table.dataTable th {';\n";
                $conteudo_php .= "echo '    background-color:rgb(69, 69, 70);';\n";
                $conteudo_php .= "echo '    color: white;';\n";
                $conteudo_php .= "echo '    padding: 10px;';\n";
                $conteudo_php .= "echo '    text-align: left;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'table.dataTable td {';\n";
                $conteudo_php .= "echo '    padding: 8px 10px;';\n";
                $conteudo_php .= "echo '    border-bottom: 1px solid #eee;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'table.dataTable tr:nth-child(even) {';\n";
                $conteudo_php .= "echo '    background-color: #f8fafc;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo 'table.dataTable tr:hover {';\n";
                $conteudo_php .= "echo '    background-color: #f1f5f9;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo '.dt-buttons .dt-button {';\n";
                $conteudo_php .= "echo '    background-color:rgb(131, 129, 129) !important;';\n";
                $conteudo_php .= "echo '    color: white !important;';\n";
                $conteudo_php .= "echo '    padding: 10px;';\n";
                $conteudo_php .= "echo '    border-radius: 4px !important;';\n";
                $conteudo_php .= "echo '    border: none !important;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo '.dataTables_wrapper .dataTables_paginate .paginate_button {';\n";
                $conteudo_php .= "echo '    border-radius: 5px !important;';\n";
                $conteudo_php .= "echo '    background-color: #eaeaea !important;';\n";
                $conteudo_php .= "echo '    border: 1px solid #d1d1d1 !important;';\n";
                $conteudo_php .= "echo '    margin: 0 3px;';\n";
                $conteudo_php .= "echo '    padding: 5px 10px;';\n";
                $conteudo_php .= "echo '    transition: all 0.3s ease;';\n";
                $conteudo_php .= "echo '}';\n";
                
                $conteudo_php .= "echo '.dataTables_wrapper .dataTables_paginate .paginate_button.current {';\n";
                $conteudo_php .= "echo '    background-color:rgb(180, 180, 180) !important;';\n";
                $conteudo_php .= "echo '    color: white !important;';\n";
                $conteudo_php .= "echo '    border-radius: 5px !important;';\n";
                $conteudo_php .= "echo '    border: 1px solid #333333 !important;';\n";
                $conteudo_php .= "echo '}';\n";
                
                $conteudo_php .= "echo '.dataTables_wrapper .dataTables_paginate .paginate_button:hover {';\n";
                $conteudo_php .= "echo '    background-color: #d5d5d5 !important;';\n";
                $conteudo_php .= "echo '    color: #686 !important;';\n";
                $conteudo_php .= "echo '}';\n";
                
                $conteudo_php .= "echo '.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {';\n";
                $conteudo_php .= "echo '    background-color: #2980b9 !important;';\n";
                $conteudo_php .= "echo '    color: white !important;';\n";
                $conteudo_php .= "echo '}';\n";
                $conteudo_php .= "echo '</style>';\n";

                $conteudo_php .= "echo '</head>';\n";
                $conteudo_php .= "echo '<body>';\n";
                
                $conteudo_php .= "// Formulário de filtros\n";
                $conteudo_php .= "echo '<h1>Relatório: $nome_relatorio</h1>';\n";
                $conteudo_php .= "echo '<form method=\"get\" action=\"\">';\n";
                
                if (isset($_POST['tabela']) && isset($_POST['campos'])) {
                    // Campos de filtro para relatório baseado em tabela
                    foreach ($_POST['campos'] as $i => $campo) {
                        if (empty($campo)) continue;
                        $conteudo_php .= "echo '<div style=\"margin-bottom:10px;\">';\n";
                        $conteudo_php .= "echo '<label for=\"$campo\">' . ucfirst(str_replace('_', ' ', '$campo')) . ':</label>';\n";
                        $conteudo_php .= "echo '<input type=\"text\" name=\"$campo\" id=\"$campo\" value=\"' . (isset(\$_GET['$campo']) ? htmlspecialchars(\$_GET['$campo']) : '') . '\">';\n";
                        $conteudo_php .= "echo '</div>';\n";
                    }
                } elseif (isset($_POST['param_nomes'])) {
                    // Campos de filtro para relatório baseado em query
                    foreach ($_POST['param_nomes'] as $i => $nome_param) {
                        $conteudo_php .= "echo '<div style=\"margin-bottom:10px;\">';\n";
                        $conteudo_php .= "echo '<label for=\"$nome_param\">' . ucfirst(str_replace('_', ' ', '$nome_param')) . ':</label>';\n";
                        $conteudo_php .= "echo '<input type=\"text\" name=\"$nome_param\" id=\"$nome_param\" value=\"' . (isset(\$_GET['$nome_param']) ? htmlspecialchars(\$_GET['$nome_param']) : '') . '\">';\n";
                        $conteudo_php .= "echo '</div>';\n";
                    }
                }
                
                $conteudo_php .= "echo '<input type=\"submit\" value=\"Filtrar\">';\n";
                $conteudo_php .= "echo '</form>';\n";
                $conteudo_php .= "echo '<hr>';\n";
                
                // Restante do código DataTables (igual ao anterior)
                $conteudo_php .= "// Tabela de resultados\n";
                $conteudo_php .= "echo '<table id=\"tabelaRelatorio\" class=\"display\" style=\"width:100%\">';\n";
                $conteudo_php .= "echo '<thead>';\n";
                $conteudo_php .= "echo '    <tr>';\n";
                $conteudo_php .= "while (\$field = \$result->fetch_field()) {\n";
                $conteudo_php .= "    echo '        <th>' . \$field->name . '</th>';\n";
                $conteudo_php .= "}\n";
                $conteudo_php .= "echo '    </tr>';\n";
                $conteudo_php .= "echo '</thead>';\n";
                $conteudo_php .= "echo '<tbody>';\n";
                $conteudo_php .= "\$result->data_seek(0); // Volta para o início dos resultados\n";
                $conteudo_php .= "while (\$row = \$result->fetch_assoc()) {\n";
                $conteudo_php .= "    echo '<tr>';\n";
                $conteudo_php .= "    foreach (\$row as \$value) {\n";
                // $conteudo_php .= "        echo '<td>' . htmlspecialchars(\$value) . '</td>';\n";
                $conteudo_php .= "        echo '<td>' . htmlspecialchars(\$value ?? '') . '</td>';\n";
                $conteudo_php .= "    }\n";
                $conteudo_php .= "    echo '</tr>';\n";
                $conteudo_php .= "}\n";
                $conteudo_php .= "echo '</tbody>';\n";
                $conteudo_php .= "echo '</table>';\n";
                
                $conteudo_php .= "// Script para inicializar DataTables\n";
                $conteudo_php .= "echo '<script>';\n";
                $conteudo_php .= "echo '$(document).ready(function() {';\n";
                $conteudo_php .= "echo '    $(\"#tabelaRelatorio\").DataTable({';\n";
                $conteudo_php .= "echo '        dom: \"Bfrtip\",';\n";
                $conteudo_php .= "echo '        buttons: [';\n";
                $conteudo_php .= "echo '            \"copy\", \"csv\", \"excel\", \"pdf\", \"print\"';\n";
                $conteudo_php .= "echo '        ],';\n";
                $conteudo_php .= "echo '        pageLength: 5,';\n";
                $conteudo_php .= "echo '        lengthMenu: [10, 25, 50, 100]';\n";
                $conteudo_php .= "echo '    });';\n";
                $conteudo_php .= "echo '});';\n";
                $conteudo_php .= "echo '</script>';\n";
                
                $conteudo_php .= "echo '</body>';\n";
                $conteudo_php .= "echo '</html>';\n";
                $conteudo_php .= "\$conn->close();\n";
                $conteudo_php .= "?>";
                
                $caminho_arquivo = 'relatorios/' . $nome_relatorio . '.php';
                if (file_put_contents($caminho_arquivo, $conteudo_php)) {
                    echo '<div style="margin-top:20px; padding:10px; background-color:#dff0d8; color:#3c763d; border:1px solid #d6e9c6;">';
                    echo '<p>Relatório gerado com sucesso!</p>';
                    echo '<p><a href="' . $caminho_arquivo . '" target="_blank">Acessar Relatório</a></p>';
                    echo '</div>';
                } else {
                    echo '<p style="color:red;">Erro ao gerar o relatório. Verifique as permissões do diretório.</p>';
                }
            }
            ?>
        </form>
    </div>
</body>
</html>
