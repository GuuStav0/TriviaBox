<?php
session_start();

// 1. Verificação de autenticação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = (int) $_SESSION['usuario_id'];
// Pega a nova senha enviada pelo formulário do profile.php
$new_password = $_POST['new-password'] ?? ''; 

$error_message = '';
$success_message = '';
$redirect_url = 'profile.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Processa a atualização apenas se o campo de nova senha NÃO estiver vazio
    if (!empty($new_password)) {
        try {
            // ✅ CAMINHO CORRIGIDO: subindo 3 níveis (pages/auth/ -> raiz)
            require_once __DIR__ . '/../../../conexao.php';

            if (isset($conexao) && $conexao instanceof mysqli) {
                
                // 2. Gerar o hash da nova senha
                $senha_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // 3. Preparar e executar a atualização da coluna 'senha_hash'
                $stmt = $conexao->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
                
                if (!$stmt) {
                    throw new Exception("Erro ao preparar query: " . $conexao->error);
                }
                
                $stmt->bind_param('si', $senha_hash, $usuario_id);
                
                if ($stmt->execute()) {
                    $success_message = "Senha atualizada com sucesso!";
                } else {
                    $error_message = "Erro ao atualizar a senha: " . $conexao->error;
                }
                
                $stmt->close();
                $conexao->close();
            } else {
                 $error_message = "Falha na conexão com o banco de dados.";
            }
        } catch (Throwable $e) {
            $error_message = "Ocorreu um erro interno. Tente novamente.";
        }
    } else {
        // Se a senha foi enviada vazia, não faz nada, mas também não dá erro.
    }
}

// 4. Redireciona de volta para a página de perfil com a mensagem
if (!empty($success_message)) {
    header("Location: {$redirect_url}?status=success&message=" . urlencode($success_message));
} elseif (!empty($error_message)) {
    header("Location: {$redirect_url}?status=error&message=" . urlencode($error_message));
} else {
    header("Location: {$redirect_url}");
}
exit();
?>