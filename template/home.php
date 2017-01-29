<!DOCTYPE html>
<html>
<header>
    <title>Simple Example - Main</title>
    <link rel="stylesheet" href="/css/style.css">
</header>
<body>
<form class="home center" action="/" method="post">
    <div id="counter"><?= $user->counter; ?></div>
    <input type="hidden" name="<?= $nameKey ?>" value="<?= $name ?>">
    <input type="hidden" name="<?= $valueKey ?>" value="<?= $value ?>">
    <button>+1</button> <button name="logout">Выход</button>
</form>
</body>
</html>