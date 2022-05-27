<ul class="navbar-nav mr-auto">
    <li class="nav-item<?= ($routeName == 'plan-index'?' active':'') ?>">
        <a class="nav-link" href="/plan">Расписание</a> 
    </li>
    <li class="nav-item<?= ($routeName == 'plan-adduser'?' active':'') ?>">
        <a class="nav-link" href="/plan/add-user">Добавить расписание для учеников</a>
    </li>
    <li class="nav-item<?= ($routeName == 'plan-addclass'?' active':'') ?>">
        <a class="nav-link" href="/plan/add-class">Добавить расписание для классов</a>
    </li>
</ul>