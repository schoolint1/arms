<?php
function getSpecialistParam($config, $id, $param, $default = null) {
    if(array_key_exists($id, $config)) {
        if(array_key_exists($param, $config[$id])) {
            return $config[$id][$param];
        }
    }
    return $default;
}

function access($modul) {
    global $container;
    if($modul == 'all') {
        return true;
    }
    $session = $container->get('session');
    if($session->isLogin()) {
        if($session->status($modul)) {
            return true;
        }
    }
    return false;
}
?>
<div class="container" style="margin-top: 1rem;">
        <?php if(access('cfg')): ?>
        <div class="arm__card">
            <span class="arm__title">Настройка</span>
            <span>
                <a href="/config/personal" class="card-link">Люди</a>
                <a href="/config/positions" class="card-link">Должности</a>
                <a href="/config/years" class="card-link">Года</a>
                <a href="/config/classes" class="card-link">Классы</a>
            </span>
        </div>
        <?php endif; ?>
        <?php if(access('vcm')): ?>
        <div class="arm__card">
            <span class="arm__title">Городская комиссия</span>
            <span>
                <a href="/vcomis" class="card-link">Войти</a>
            </span>
        </div>
        <?php endif; ?>
        <?php if(access('rbl')): ?>
        <div class="arm__card">
            <span class="arm__title">Список в работу специалистам</span>
            <span>
                <a href="/rablist" class="card-link">Войти</a>
            </span>
        </div>
        <?php endif; ?>
        <?php if(access('pln')): ?>
        <div class="arm__card">
            <span class="arm__title">Расписание</span>
            <span>
                <a href="/plan" class="card-link">Расписание</a>
                <a href="/plan/add-user" class="card-link">Добавить расписание для учеников</a>
                <a href="/plan/add-class" class="card-link">Добавить расписание для классов</a>
            </span>
        </div>
        <?php endif; ?>
        <?php if(access('ink')): ?>
        <div class="arm__card">
            <span class="arm__title">Инклюзия</span>
            <span>
                <a href="/inkcom" class="card-link">Войти</a>
            </span>
        </div>
        <?php endif; ?>
    <?php if(count($specialists)): ?>
    <h3 class="text-center">АРМы специалистов</h3>
    <?php
    endif;
    $user = $container->get('session')->getUser();
    foreach ($specialists AS $index => $value): 
        if($user->isInGroup($index) && access('spc')): ?>

        <div class="arm__card"">
            <span class="arm__title"><?= getSpecialistParam($specialistsConfig, $index, 'name', $value) ?></span>
                
            <span>
                <a href="/specialist-<?= $index ?>/reports" class="card-link">Осмотры</a>
                <a href="/specialist-<?= $index ?>/register" class="card-link">Список в работу</a>
            </span>
        </div>

    <?php endif;
    endforeach; ?>
</div>

