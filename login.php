<?php
    session_start();
    // Se já estiver logado, redireciona para a página do sistema
    if(isset($_SESSION['email']) && isset($_SESSION['senha'])) {
        header('Location: sistema.php');
        exit;
    }

    // Define a mensagem de erro se existir
    $errorMessage = isset($_SESSION['errorMessage']) ? $_SESSION['errorMessage'] : "";
    // Limpa a variável de sessão da mensagem de erro
    unset($_SESSION['errorMessage']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Login</title>
    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            background-image: url("ceuazul.jpg");
        }

        .tela-login{
            background-color: rgba(0, 0, 0, 0.9);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 80px;
            border-radius: 15px;
            color: white;
        }

        input{
            padding: 15px;
            border: none;
            outline: none;
            font-size: 15px;
        }

        .inputSubmit{
            background-color: dodgerblue;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }
        .inputSubmit:hover{
            background-color: deepskyblue;
        }
        .botao-cadastro{
            background-color: dodgerblue;
            border: none;
            padding: 15px 71px;
            width: 100%;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
        }

        .botao-cadastro:hover{
            background-color: deepskyblue;
        }

        .botao-voltar {
            background-color: red;
            color: white;
            border: 2px solid red;
            padding: 7px 20px;
            text-decoration: none;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .botao-voltar:hover {
            background-color: darkred;
        }




    </style>
</head>
<body>
    <a href="index.php" class="botao-voltar">☚ Voltar</a>
    <div class = "tela-login">
        <h1>Login</h1>
        <form action="testLogin.php" method="POST">
        <input type="text" name="email" placeholder="Email">
        <br><br>
        <input type="password" name = "senha" placeholder="Senha">
        <br><br>
        <input class = "inputSubmit" type ="submit" name="submit" value="Logar">
        <br><br><br>
        <a href="formulario.php" class = "botao-cadastro" type ="submit" name="cadastrar-se">Cadastrar-se</a>
        </form>
        <br> <br>
        <?php if(isset($errorMessage)) { ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php } ?>
    </div>
</body>
</html>
