<!DOCTYPE html>
<html>
<header>
    <title>Simple Example - Register</title>
    <link rel="stylesheet" href="/css/style.css">
</header>
<body>
<form class="register center" action="/register" method="post">
    <div class="error"><?= $error; ?></div>
    <input type="text" name="login"  placeholder="login" required value="<?= $_POST["login"] ?? ""; ?>"><br>
    <input type="password" name="password" placeholder="Пароль"  required><br>
    <input type="text" name="birthday" placeholder="Дата рождения (дд.мм.гггг)" required value="<?= $_POST["birthday"] ?? ""; ?>">
    <button name="register">Зарегистрироваться</button>
</form>
</body>
</html>