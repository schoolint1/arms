<ul class="navbar-nav mr-auto">
    <li class="nav-item active">
        <a class="nav-link" href="/inkcom">Обучающиеся</a>
    </li>
    <li class="nav-item active">
        <a class="nav-link" href="/inkcom/classes">Классы</a>
    </li>
    <?php if($container->get('session')->getUser()->isInGroup(5)): ?>
    <li class="nav-item">
        <a class="nav-link" href="/inkcom/commissions">Комиссии</a>
    </li>
    <?php endif; ?>
</ul>