<?php if (isset($errors['login'])) : ?>
    <div class="alert alert-error">
        <?= $errors['login'] ?>
    </div>
<?php endif ?>

<div class="waper">
    <form name="login" method="post">
        <div class="foc">
            <input type="text" name="username" class="form-control" placeholder="Username" value= "">
        </div>
        <div class="foc">
            <input type="password" name="password" class="form-control"  placeholder="Password" autocomplete="off" value= "" >
        </div>
        <div class="foc" >
            <button type="submit" class="btn btn-block">Login</button>
        </div>
    </form>
</div>