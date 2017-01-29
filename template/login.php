<!DOCTYPE html>
<html>
<header>
    <title>Simple Example - Login</title>
    <link rel="stylesheet" href="/css/style.css">
</header>
<body>
    <form class="login center" action="/login" method="post">
        <div class="error"><?= $error; ?></div>
        <input type="text" name="login"  placeholder="Логин"><br>
        <input type="password" name="password" placeholder="Пароль"><br>
        <button>Войти</button>
        <button name="register">Зарегистрироваться</button>
    </form>
</body>
</html>