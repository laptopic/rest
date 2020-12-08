<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>


<p>Главная страница</p>

<?php foreach ($news as $var): ?>
    <h3><?php echo $var['title']; ?></h3>
    <p><?php echo $var['description']; ?></p>
    <hr>
<?php endforeach; ?>


</body>
</html>