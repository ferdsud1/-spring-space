<?php
session_start();

// CONFIGURAÇÕES
$senha_correta = "admin123"; // ALTERE SUA SENHA AQUI
$arquivo_dados = "js/dados.json";

// LOGIN
if (isset($_POST['login'])) {
    if ($_POST['senha'] === $senha_correta) {
        $_SESSION['admin'] = true;
    } else {
        $erro = "Senha incorreta!";
    }
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// SE NÃO ESTIVER LOGADO, MOSTRA FORM DE LOGIN
if (!isset($_SESSION['admin'])) {
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Admin · Spring Space</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #faf7f2; }
        .login-box { background: #fff; padding: 40px; border-radius: 14px; box-shadow: 0 14px 40px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; }
        .btn { width: 100%; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Painel Admin</h2>
        <?php if(isset($erro)) echo "<p style='color:red'>$erro</p>"; ?>
        <form method="POST">
            <input type="password" name="senha" placeholder="Digite a senha" required>
            <button type="submit" name="login" class="btn btn-solid"><span>Entrar</span></button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// PROCESSA O UPLOAD DE NOVO EVENTO
if (isset($_POST['criar_evento'])) {
    $dados = json_decode(file_get_contents($arquivo_dados), true);
    
    $novo_id = 1;
    foreach ($dados['events'] as $ev) {
        if ($ev['id'] >= $novo_id) $novo_id = $ev['id'] + 1;
    }
    
    $pasta = "evento" . $novo_id;
    if (!is_dir($pasta)) mkdir($pasta, 0777, true);
    
    $count = 0;
    if (isset($_FILES['fotos'])) {
        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
            $count++;
            $nome_arquivo = $count . ".jpg";
            move_uploaded_file($tmp_name, $pasta . "/" . $nome_arquivo);
        }
    }
    
    $novo_evento = [
        "id" => $novo_id,
        "slug" => "evento-" . $novo_id,
        "title" => $_POST['titulo'],
        "category" => $_POST['categoria'],
        "badge" => ucfirst($_POST['categoria']),
        "count" => $count,
        "caption" => $_POST['descricao']
    ];
    
    $dados['events'][] = $novo_evento;
    file_put_contents($arquivo_dados, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $sucesso = "Evento criado com sucesso!";
}

// PROCESSA O UPLOAD DE VÍDEO
if (isset($_POST['add_video'])) {
    $dados = json_decode(file_get_contents($arquivo_dados), true);
    
    $pasta_videos = "videos";
    if (!is_dir($pasta_videos)) mkdir($pasta_videos, 0777, true);
    
    $nome_video = $_FILES['video_file']['name'];
    move_uploaded_file($_FILES['video_file']['tmp_name'], $pasta_videos . "/" . $nome_video);
    
    $novo_video = [
        "file" => $nome_video,
        "poster" => $_POST['video_poster'] ?: "evento1/1.jpg",
        "title" => $_POST['video_titulo'],
        "category" => $_POST['video_categoria']
    ];
    
    $dados['videos'][] = $novo_video;
    file_put_contents($arquivo_dados, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $sucesso = "Vídeo adicionado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin · Spring Space</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #faf7f2; padding: 40px; font-family: sans-serif; }
        .container-admin { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        h1, h2 { color: #6b2737; }
        form { margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid #eee; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; }
        .logout { float: right; color: #6b2737; text-decoration: none; }
        .msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container-admin">
        <a href="?logout=1" class="logout">Sair</a>
        <h1>Painel de Controle</h1>
        <p>Gerencie as fotos e vídeos do site.</p>

        <?php if(isset($sucesso)) echo "<div class='msg'>$sucesso</div>"; ?>

        <section>
            <h2>Novo Evento (Fotos)</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Título do Evento</label>
                <input type="text" name="titulo" placeholder="Ex: Casamento de Maria e João" required>
                
                <label>Categoria</label>
                <select name="categoria">
                    <option value="casamento">Casamento</option>
                    <option value="aniversario">Aniversário</option>
                    <option value="festa">Festa</option>
                </select>
                
                <label>Descrição/Legenda</label>
                <textarea name="descricao" rows="3" placeholder="Uma breve descrição..."></textarea>
                
                <label>Fotos (Selecione várias de uma vez)</label>
                <input type="file" name="fotos[]" multiple required>
                
                <button type="submit" name="criar_evento" class="btn btn-solid"><span>Criar Evento e Salvar Fotos</span></button>
            </form>
        </section>

        <section>
            <h2>Novo Vídeo</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Título do Vídeo</label>
                <input type="text" name="video_titulo" placeholder="Ex: Melhores momentos" required>
                
                <label>Categoria</label>
                <select name="video_categoria">
                    <option value="casamento">Casamento</option>
                    <option value="aniversario">Aniversário</option>
                    <option value="festa">Festa</option>
                </select>

                <label>Arquivo do Vídeo (MP4)</label>
                <input type="file" name="video_file" required>

                <label>Capa do Vídeo (Caminho da imagem)</label>
                <input type="text" name="video_poster" placeholder="Ex: evento1/1.jpg" value="evento1/1.jpg">
                
                <button type="submit" name="add_video" class="btn btn-solid"><span>Salvar Vídeo</span></button>
            </form>
        </section>
        
        <p><a href="index.html" target="_blank">Ver site →</a></p>
    </div>
</body>
</html>