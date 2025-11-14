<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "quiz_site"; 


$conexao = new mysqli($servidor, $usuario, $senha, $banco);


if ($conexao->connect_error) {
    die("Falha na Conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8");

?>