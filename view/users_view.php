<div class="content">
    <h2><?php if (count($usersInScope)) { ?>
        Modifica:
            <?php foreach ($usersInScope as $user) { ?>
                <?php if (isset($userToModify['id']) && $userToModify['id'] == $user['id']) { ?>
                    <?php echo $user['name']; ?>
                <?php } else { ?>
                    <a href="?ui=<?php echo $user['id']; ?>"><?php echo $user['name']; ?></a>                
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </h2>
    <?php if (count($usersInScope) && !isset($userToModify)) { ?>
        <h2>Inserisci nuovo utente</h2>
        <form name="adduser" method="post" onsubmit="return validateAddUser()">
            <input type="hidden" name="said" value= "<?php echo $thisUserId; ?>">
            <div class="foc">
                <input type="hidden" name="f" value= "au">
            </div>
            <div class="foc">
                <input type="text" name="name" placeholder="Nome per esteso" value= "">
            </div>
            <div class="foc">
                <input type="text" name="login" placeholder="Login" value= "">
            </div>
            <div class="foc">
                <input type="password" name="password" placeholder="Password" value= "">  
            </div> 
            <div class="foc">
                <input type="password" name="passwordvalidate" placeholder="Ripeti Password" value= "">
            </div>
            <div class="foc">
                <?php foreach ($companies as $companyId => $compData) { ?>
                    Amministratore della compagnia <?php echo $compData['name']; ?><input type="checkbox" id="iscompanyadmin_<?php echo $companyId; ?>" name="iscompanyadminArr[]" value="0" >
                <?php } ?>
            </div>
            <div class="foc">
                <?php
                $checked = "checked";
                foreach ($futureShow as $oneShow) {
                    ?>
                    <input type="checkbox" id="insert_show_<?php echo $oneShow['id']; ?>" name="show[]" value="<?php echo $oneShow['id']; ?>" <?php echo $checked; ?> ><?php echo $oneShow['data'] . " " . $oneShow['nome']; ?><br>
                    <?php
                    $checked = "";
                }
                ?>
            </div>
            <div class="foc">
                <input type="submit" value="Inserisci" />
            </div>
        </form>
    <?php } else if (isset($userToModify)) { ?>
        <a href="users.php"><h2>Inserisci nuovo utente</h2></a>
        <form name="update_user_<?php echo $userToModify['id']; ?>" method="post" onsubmit="return validateUpdateUser(<?php echo $userToModify['id']; ?>)" >
            <input type="hidden" name="said" value= "<?php echo $thisUserId; ?>">
            <input type="hidden" name="id" value= "<?php echo $userToModify['id']; ?>">
            <input type="hidden" name="f" value= "uu"> 
            <div class="foc">
                <input type="text" name="name" value= "<?php echo $userToModify['name']; ?>">
            </div>
            <div class="foc">
                <input type="text" name="login" value= "<?php echo $userToModify['user_login']; ?>">
            </div>
            <div class="foc">
                <input type="password" name="password" placeholder="Nuova Password" value= "">
            </div>
            <div class="foc">
                <input type="password" name="passwordvalidate" placeholder="Ripeti Password" value= "">
            </div>
            <div class="foc"><?php
                $enable = $thisUserId != $userToModify['id'] ? "" : "readonly";
                $disabled = $thisUserId != $userToModify['id'] ? "" : "disabled";
                foreach ($userToModify['company'] as $companyId => $compData) {
                    $isCompanyAdminChecked = $compData['isCompanyAdmin'] ? "checked" : "";
                    ?>
                    Amministratore della compagnia <?php echo $compData['name']; ?><input type="checkbox" id="iscompanyadmin_<?php echo $companyId; ?>" name="iscompanyadminArr[]" value="<?php echo $companyId; ?>" <?php echo $isCompanyAdminChecked; ?> <?php echo $disabled; ?>>
                <?php } ?>
            </div>
            <div class="foc"><?php
                if ($isAdmin || $isCompanyAdmin) {
                    foreach ($futureShow as $oneShow) {
                        $showChecked = isset($showUserMap[$userToModify['id']]) && in_array($oneShow['id'], $showUserMap[$userToModify['id']]) ? "checked" : "";
                        ?>
                        <input type="checkbox" id="show_<?php echo $disabled . $oneShow['id'] . $userToModify['id']; ?>" name="show[]" value="<?php echo $oneShow['id']; ?>" <?php echo $showChecked; ?> <?php echo $disabled; ?>><?php echo $oneShow['data'] . " " . $oneShow['nome']; ?><br>
                        <?php
                        $showChecked = "";
                    }
                } else {
                    foreach ($futureShow as $oneShow) {
                        $showChecked = in_array($oneShow['id'], $showUserMap[$userToModify['id']]) ? "checked" : "";
                        ?>
                        <input type="checkbox" id="show_<?php echo $oneShow['id'] . $userToModify['id']; ?>" name="show[]" value="<?php echo $oneShow['id']; ?>" <?php echo $showChecked; ?> ><?php echo $oneShow['data'] . " " . $oneShow['nome']; ?><br>
                        <?php
                        $showChecked = "";
                    }
                    $enable = "";
                }
                ?>
            </div>
            <div class="foc">
                <input type="submit" value="Salva" />
            </div>
        </form>
        <div class="foc">
            <?php if ($thisUserId != $userToModify['id']) { ?>
                <form name="delete_user_<?php echo $oneShow['id']; ?>" method="post" >
                    <input type="hidden" name="id" value= "<?php echo $userToModify['id']; ?>">
                    <input type="hidden" name="f" value= "du">
                    <input type="submit" value="elimina" />
                </form>
            <?php } ?>
        </div>
    <?php } ?>
</div>