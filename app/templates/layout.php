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
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <!--link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous"-->
    <link rel="stylesheet" href="/assets/css/noty.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/animate.css" rel="stylesheet">
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
    
    <!--script src="https://code.jquery.com/jquery-3.5.1.min.js" type="text/javascript"></script-->
    <script src="/assets/js/jquery-3.5.1.min.js" type="text/javascript"></script>
    <script>window.jQuery || document.write('<script src="/assets/js/jquery-3.5.1.min.js" type="text/javascript"><\/script>')</script>
    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous" type="text/javascript"></script-->
    <!--script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous" type="text/javascript"></script-->
    <!--script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script-->
    <!--script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.min.js" integrity="sha384-VHvPCCyXqtD5DqJeNxl2dtTyhF78xXNXdkwX1CZeRusQfRKp+tA7hAShOK/B/fQ2" crossorigin="anonymous"></script-->
    <script src="/assets/js/popper.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>

    <script src="/assets/js/vue-2.6.14.js" type="text/javascript"></script>
    <!-- https://vcalendar.io/ -->
    <script src='/assets/js/v-calendar-2.3.4.umd.min.js'></script>
    <!--script src='/assets/js/dateformat.js'></script-->
    <!-- https://moment.github.io/luxon/ -->
    <script src='/assets/js/luxon.min.js'></script>
    <script>
        var DateTime = luxon.DateTime;
    </script>
</head>

<body>
<!--[if IE]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
<![endif]-->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/">АРМы</a>
    <!--button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button-->

    <!--div class="collapse navbar-collapse" id="navbarSupportedContent"-->
<?php if(isset($navbar)): ?>
    <?php include($navbar) ?>
<?php else: ?>
    <ul class="navbar-nav mr-auto"></ul>
<?php endif; ?>
        <div class="form-inline my-2 my-lg-0" id="app-main-data">
            <ul class="navbar-nav mr-auto ml-2">
                <li class="nav-item">
                    <span class="navbar-text">{{ getDate }} / <?=$container->get('session')->getSchoolYear()['name'] ?></span> <v-date-picker
                            v-model="date"
                            @dayclick="setDate($event)"
                            :popover="{ placement: 'bottom', visibility: 'click' }">
                        <template v-slot="{ inputValue, inputEvents }">
                            <button class="btn btn-sm btn-primary" v-on="inputEvents">
                                <img src="/img/icon-calendar.svg" style="width: 1rem; height: 1rem; margin-bottom: 3px;" alt="Календарь">
                            </button>
                        </template>
                    </v-date-picker>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= $container->get('session')->getUser()->getShortName() ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/logout">Выход</a>
                    </div>
                </li>
            </ul>
        </div>
    <!--/div-->
</nav>
<div class="container-fluid">
<?=$content?>
</div>

<!-- https://github.com/needim/noty -->
<script src="/assets/js/noty.min.js" type="text/javascript"></script>

<script>
    var app_main = new Vue({
        el: '#app-main-data',
        data: {
            date: new Date('<?=$container->get('session')->getDate()->format('Y-m-d') ?>')
        },
        computed: {
            getDate: function () {
                return DateTime.fromJSDate(this.date).toFormat('dd.LL.yyyy');
            }
        },
        methods: {
            setDate: function (event) {
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'all_Date_change',
                        'params': {
                            'date': DateTime.fromJSDate(this.date).toFormat('yyyy-LL-dd')
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json',
                    success: function (data) {
                        if (typeof data.error !== "undefined") {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.error.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                        if (typeof data.result !== "undefined") {
                            if (data.result.status == 'ok') {
                                location.reload();
                            }
                            if (data.result.status == 'error') {
                                new Noty({
                                    type: 'error',
                                    timeout: 6000,
                                    text: data.result.message,
                                    animation: {
                                        open : 'animated fadeInRight',
                                        close: 'animated fadeOutRight'
                                    }
                                }).show();
                            }
                        }
                    }
                })
            }
        }
    });
</script>

</body>

</html>