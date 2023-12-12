<h2>Inserisci nuovo show</h2><br/>
<?php if (isset($errors)) : ?>
    <div class="alert alert-error">
        <? echo $errors; ?>
    </div>
<?php endif ?>
Ciao <?php echo $userName; ?><br/><br/>
<table>
    <form name="addshow" method="post" onsubmit="return subAddShow()">
        <input type="hidden" name="f" value= "i">
        <tr>
            <td>data</td>
            <td>nome</td>
            <td>Posto</td>
            <td>dettagli</td>
            <td>numero posti</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td><input type="hidden" name="userid" value= "<?php echo $thisUserId; ?>">
                <input type="text" name="timestamp" value= "".date("Y-m-d h:i:s",time())."">
                <a href="javascript:show_calendar('document.addshow.timestamp', document.addshow.timestamp.value);">
                    <img src="img/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a></td>
            <td><input type="text" name="namei" value= ""></td>
            <td><input type="text" name="locationi" value= ""></td>
            <td><input type="text" name="detailsi" value= ""></td>
            <td><input type="text" name="seatsi" value= ""></td>
            <td><input type="submit" value="inserisci" /></td>
        </tr>
    </form>
</table>
<br/><br/>
<?php if (count($futureShow) > 0) { ?>
    <h2>Modifica Show</h2></br>
    <table>
        <input type="hidden" name="f" value= "i">
        <tr>
            <td>data</td>
            <td>nome</td>
            <td>Posto</td>
            <td>dettagli</td>
            <td>numero posti</td>
            <td>&nbsp;</td>
        </tr>
        <?php foreach ($futureShow as $oneShow) { ?>
            <tr>
            <form name="showdate<?php echo $oneShow['id']; ?>" method="post">
                <input type="hidden" name="id" value= "<?php echo $oneShow['id']; ?>">
                <input type="hidden" name="f" value= "u">
                <td><input type="text" name="timestamp" value= "<?php echo $oneShow['data']; ?>">
                    <a href="javascript:show_calendar('document.showdate<?php echo $oneShow['id']; ?>.timestamp', document.showdate<?php echo$oneShow['id']; ?>.timestamp.value);">
                        <img src="img/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a></td>
                <td><input type="text" name="name" value= "<?php echo $oneShow['nome']; ?>"></td>
                <td><input type="text" name="location" value= "<?php echo $oneShow['luogo']; ?>"></td>
                <td><input type="text" name="details" value= "<?php echo $oneShow['dettagli']; ?>"></td>
                <td><input type="text" name="seats" value= "<?php echo $oneShow['posti']; ?>"></td>
                <td><input type="submit" value="Salva" />
            </form>
            <form name="delete<?php echo $oneShow['id']; ?>" method="post" onsubmit="return deleteShows(<?php echo $oneShow['id']; ?>)">
                <input type="hidden" name="id" value= "<?php echo $oneShow['id']; ?>">
                <input type="hidden" name="f" value= "d">
                <input type="submit" value="elimina" />
            </form>    
        </td>    
        </tr>
    <?php } ?>
    </table>
<?php } ?>
<?php if (count($usersInScope) && count($futureShow)) { ?>  
    <h2>Inserisci nuovo utente</h2><br/>
    <table>
        <form name="adduser" method="post" onsubmit="return validateAddUser()">
            <input type="hidden" name="f" value= "au">
            <tr>
                <td>Nome per esteso</td>
                <td>login</td>
                <td>Password</td>
                <td>Ripeti Password</td>
                <td>Livello d'accesso</td>
                <td>Show Associati</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td><input type="text" name="name" value= ""></td>
                <td><input type="text" name="login" value= ""></td>
                <td><input type="password" name="password" value= ""></td>     
                <td><input type="password" name="passwordvalidate" value= ""></td>
                <td><input type="text" name="accessLevel" value= ""></td>
                <td>
                    <?php
                    $checked = "checked";
                    foreach ($futureShow as $oneShow) {
                        ?>
                        <input type="checkbox" id="insert_show_<?php echo $oneShow['id']; ?>" name="show[]" value="<?php echo $oneShow['id']; ?>" <?php echo $checked; ?> ><?php echo $oneShow['data'] . " " . $oneShow['nome']; ?><br>
                        <?php
                        $checked = "";
                    }
                    ?>
                </td>
                <td><input type="submit" value="Inserisci" /></td>
        </form>
    </table>
<?php } ?>   
    <?php if (count($usersInScope)) { ?>
        <h3>Modifica Utenti</h3></br>
        <table>
            <tr>
                <td>Nome per esteso</td>
                <td>login</td>
                <td>Aggiorna Password</td>
                <td>Ripeti Password</td>
                <td>Livello d'accesso</td>
                <td>Show Associati</td>
                <td>&nbsp;</td>
            </tr>
            <?php foreach ($usersInScope as $user) { ?>

                <form name="update_user_<?php echo $user['id']; ?>" method="post" onsubmit="return validateUpdateUser(<?php echo $user['id']; ?>)" >
                    <input type="hidden" name="id" value= "<?php echo $user['id']; ?>">
                    <input type="hidden" name="f" value= "uu"> 
                    <tr>
                        <td><input type="text" name="name" value= "<?php echo $user['name']; ?>"></td>
                        <td><input type="text" name="login" value= "<?php echo $user['user_login']; ?>"></td>
                        <td><input type="password" name="password" value= ""></td>
                        <td><input type="password" name="passwordvalidate" value= ""></td>
                        <td><?php
                            $enable = $thisUserId != $user['id'] ? "" : "readonly";
                            $disabled = $thisUserId != $user['id'] ? "" : "disabled";
                            ?> 
                            <input type="text" name="accessLevel" value= "<?php echo $user['access_level']; ?>" <?php echo $enable; ?>>
                        <td><?php
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
                        </td>
                        <td><input type="submit" value="Salva" />
                </form>
            <?php if ($thisUserId != $user['id']) { ?>
                    <form name="delete_user_<?php echo $oneShow['id']; ?>" method="post" >
                        <input type="hidden" name="id" value= "<?php echo $user['id']; ?>">
                        <input type="hidden" name="f" value= "du">
                        <input type="submit" value="elimina" />
                    </form>
            <?php } ?>
            </td>
            </tr>
            <?php
            $enable = "";
        }
        ?>
        </table>
    <?php
} else {
    ?>
    Devi prima creare uno spettacolo
<?php } ?>
