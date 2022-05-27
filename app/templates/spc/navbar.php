<span class="navbar-brand" href="#"><?= $specialist['name'] ?></span>
<ul class="navbar-nav mr-auto">
    <li class="nav-item<?= ($routeName == 'specialist-register'?' active':'') ?>">
        <a class="nav-link" href="/specialist-<?= $specialistId ?>/register">Список</a>
        
    </li>
    <li class="nav-item<?= ($routeName == 'specialist-reports'?' active':'') ?>">
        <a class="nav-link" href="/specialist-<?= $specialistId ?>/reports">Осмотры</a>
    </li>
</ul>