<form class="form-signin" method="post" action="/login">
    <img class="mb-4" src="/img/logo.png" alt="" width="184" height="129">
    <h1 class="h3 mb-3 font-weight-normal">Пожалуйста войдите</h1>
    <?php if($errorMessage != null): ?>
    <div class="alert alert-danger" role="alert">
        <?=$errorMessage ?>
    </div>
    <?php endif; ?>
    <label for="inputLogin" class="sr-only">Имя пользователя</label>
    <input type="text" id="inputLogin" name="username" class="form-control" placeholder="Имя пользователя" required autofocus>
    <label for="inputPassword" class="sr-only">Пароль</label>
    <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Пароль" required>
    <!--div class="checkbox mb-3">
        <label>
            <input type="checkbox" value="remember-me"> Запомнить меня
        </label>
    </div-->
    <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
</form>