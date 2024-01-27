<div class="content">
    <h2>Inserisci nuovo show</h2></br>
    <form name="addshow" method="post" onsubmit="return subAddShow()">
        <input type="hidden" name="f" value= "i">
        <input type="hidden" name="userid" value= "<?php echo $thisUserId; ?>">
        <div class="foc">    
            <input type="text" name="timestamp" placeholder="Data" value= "" onclick="show_calendar('document.addshow.timestamp', document.addshow.timestamp.value)">
        </div>
        <div class="foc">
            <input type="text" name="namei" placeholder="Titolo spettacolo" value= "">
        </div>
        <div class="foc">
            <input type="text" name="locationi" placeholder="Nome teatro" value= "">
        </div>
        <div class="foc">
            <input type="text" name="detailsi" placeholder="Dettagli" value= "">
        </div>
        <div class="foc">
            <input type="text" name="seatsi" placeholder="Numero posti" value= "">
        </div>
        <div class="foc"><input type="submit" value="inserisci" /></div>
    </form>
    <?php if (count($futureShow) > 0) { ?>
        <h2>Modifica Show</h2></br>
        <?php foreach ($futureShow as $oneShow) { ?>
            <form name="showdate<?php echo $oneShow['id']; ?>" method="post">
                <input type="hidden" name="id" value= "<?php echo $oneShow['id']; ?>">
                <input type="hidden" name="f" value= "u">
                <div class="foc">
                    <input type="text" name="timestamp" value= "<?php echo $oneShow['data']; ?>" onclick="show_calendar('document.addshow.timestamp', document.addshow.timestamp.value)">
                </div>    
                <div class="foc">
                    <input type="text" name="name" value= "<?php echo $oneShow['nome']; ?>">
                </div>  
                <div class="foc">
                    <input type="text" name="location" placeholder="Nome teatro" value="<?php echo $oneShow['luogo']; ?>">
                </div>       
                <div class="foc">
                    <input type="text" name="details"  placeholder="Dettagli"  value="<?php echo $oneShow['dettagli']; ?>">
                </div>
                <div class="foc">
                    <input type="text" name="seats" value= "<?php echo $oneShow['posti']; ?>">
                </div>
                <div class="">
                    <input type="submit" value="Salva" style="display: inline-block;"/>
            </form>
            <form name="delete<?php echo $oneShow['id']; ?>" method="post" onsubmit="return deleteShows(<?php echo $oneShow['id']; ?>)" style="display: inline-block;">
                <input type="hidden" name="id" value= "<?php echo $oneShow['id']; ?>">
                <input type="hidden" name="f" value= "d">
                <input type="submit" value="Elimina" />
            </form>
        </div>
    <?php } ?>
<?php } ?>
<?php if (count($usersInScope) && count($futureShow)) { ?>  
    <h2>Inserisci nuovo utente</h2><br/>
    <form name="adduser" method="post" onsubmit="return validateAddUser()">
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
            <input type="text" name="accessLevel" placeholder="Livello d'accesso" value= "1">
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
<?php } ?>   
<?php if (count($usersInScope)) { ?>
    <h3>Modifica Utenti</h3></br>
    <?php foreach ($usersInScope as $user) { ?>
        <form name="update_user_<?php echo $user['id']; ?>" method="post" onsubmit="return validateUpdateUser(<?php echo $user['id']; ?>)" >
            <input type="hidden" name="id" value= "<?php echo $user['id']; ?>">
            <input type="hidden" name="f" value= "uu"> 
            <div class="foc">
                <input type="text" name="name" value= "<?php echo $user['name']; ?>">
            </div>
            <div class="foc">
                <input type="text" name="login" value= "<?php echo $user['user_login']; ?>">
            </div>
            <div class="foc">
                <input type="password" name="password" placeholder="Nuova Password" value= "">
            </div>
            <div class="foc">
                <input type="password" name="passwordvalidate" placeholder="Ripeti Password" value= "">
            </div>
            <div class="foc"><?php
                $enable = $thisUserId != $user['id'] ? "" : "readonly";
                $disabled = $thisUserId != $user['id'] ? "" : "disabled";
                ?> 
                <input type="text" name="accessLevel" value= "<?php echo $user['access_level']; ?>" <?php echo $enable; ?>>
            </div>
            <div class="foc"><?php
                if ($isAdmin) {

                    foreach ($futureShow as $oneShow) {
                        $showChecked = in_array($oneShow['id'], $showUserMap[$user['id']]) ? "checked" : "";
                        ?>
                        <input type="checkbox" id="show_<?php echo $disabled . $oneShow['id'] . $user['id']; ?>" name="show[]" value="<?php echo $oneShow['id']; ?>" <?php echo $showChecked; ?> <?php echo $disabled; ?>><?php echo $oneShow['data'] . " " . $oneShow['nome']; ?><br>
                        <?php
                        $showChecked = "";
                    }
                } else {
                    foreach ($futureShow as $oneShow) {
                        $showChecked = in_array($oneShow['id'], $showUserMap[$user['id']]) ? "checked" : "";
                        ?>
                        <input type="checkbox" id="show_<?php echo $oneShow['id'] . $user['id']; ?>" name="show[]" value="<?php echo $oneShow['id']; ?>" <?php echo $showChecked; ?> ><?php echo $oneShow['data'] . " " . $oneShow['nome']; ?><br>
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
            <?php if ($thisUserId != $user['id']) { ?>
                <form name="delete_user_<?php echo $oneShow['id']; ?>" method="post" >
                    <input type="hidden" name="id" value= "<?php echo $user['id']; ?>">
                    <input type="hidden" name="f" value= "du">
                    <input type="submit" value="elimina" />
                </form>
            <?php } ?>
        </div>
        <?php
        $enable = "";
    }
    ?>
    <?php
} else {
    ?>
    Devi prima creare uno spettacolo
<?php } ?>
</div>