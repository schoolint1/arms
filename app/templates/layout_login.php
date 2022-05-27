<!doctype html>
<html class="no-js" lang="ru-RU">

<head>
    <meta charset="utf-8">
    <title><?=$title ?></title>
    <meta name="description" content="АРМы">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="icon" type="image/png" sizes="48x48" href="/favicon-48x48.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <?php if(isset($styles)):
        if(is_array($styles)):
            foreach ($styles AS $style): ?>
                <link rel="stylesheet" href="/assets/css/<?= $style?>.css" rel="stylesheet">
                <?php
            endforeach; ?>
            <?php
        else: ?>
            <link rel="stylesheet" href="/assets/css/<?= $styles?>.css" rel="stylesheet">
            <?php
        endif;
    endif; ?>

    <meta name="theme-color" content="#fafafa">
</head>

<body class="text-center">
<!--[if IE]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
<![endif]-->

<?=$content?>

<!--script src="https://code.jquery.com/jquery-3.5.1.min.js"></script-->
<script src="/assets/js/jquery-3.5.1.min.js"></script>
<script>window.jQuery || document.write('<script src="/assets/js/jquery-3.5.1.min.js"><\/script>')</script>
<!--script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script-->
<!--script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script-->
<script src="/assets/js/popper.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
</body>

</html>