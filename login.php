<?php
require_once __DIR__ . "/init.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $pass  = trim($_POST['pass'] ?? '');

    if ($login === '' || $pass === '') {
        $error = "Введите логин и пароль.";
    } else {

        // busca usuário
        $stmt = $db->prepare("SELECT id, login, pass FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['pass'] === $pass) {
            // salva sessão CORRETAMENTE
            $_SESSION['user']    = $user['login'];
            $_SESSION['user_id'] = $user['id'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Неверный логин или пароль.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Вход</title>
<link rel="icon" href="favicon.ico">
</head>

<body link="#0000FF" alink="#0000FF" vlink="#0000FF">

<table width="700" align="center" cellpadding="5" cellspacing="0" border="1">
<tr>
<td colspan="2" bgcolor="#CCCCCC">
<b>RetroShow</b> — Upload a Video
<div align="right">
<a href="index.php">Home</a> |
<?php if (!isset($_SESSION['user'])): ?>
  <a href="register.php">Register</a> |
  <a href="login.php">Login</a>
<?php else: ?>
  <a href="channel.php?user=<?=htmlspecialchars($_SESSION['user'])?>">My Channel</a> |
  <a href="upload.php">Upload Video</a> |
  <a href="logout.php">Logout</a>
<?php endif; ?>
</div>
</td>
</tr>

<tr><td colspan="2">
<b>Вход:</b>

<?php if ($error): ?>
<br><em><?=htmlspecialchars($error)?></em><br>
<?php endif; ?>

<form method="post">
<table border="1" cellpadding="5" align="center">
<tr>
<td>Login:</td>
<td><input type="text" name="login"></td>
</tr>
<tr>
<td>Password:</td>
<td><input type="password" name="pass"></td>
</tr>
<tr>
<td colspan="2" align="center">
<input type="submit" value="Login">
</td>
</tr>
</table>
</form>

</td></tr>
</table>

</body>
</html>
