<div class="content">
    <h2><?php if (count($companies)) { ?>
        Modifica:
            <?php foreach ($companies as $key => $company) { ?>
                <?php if (isset($companyToModify['id']) && $companyToModify['id'] == $company['id']) { ?>
                    <?php echo $company['name']; ?>
                <?php } else { ?>
                    <a href="?cu=<?php echo $company['id']; ?>"><?php echo $company['name']; ?></a>                
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </h2>
    <?php if (!isset($companyToModify)) { ?>
        <h2>Inserisci nuova compagnia</h2>
        <form name="adduser" method="post" onsubmit="return validateAddUser()">
            <input type="hidden" name="said" value= "<?php echo $thisUserId; ?>">
            <div class="foc">
                <input type="hidden" name="f" value= "ac">
            </div>
            <div class="foc">
                <input type="text" name="name" placeholder="Nome compagnia" value= "">
            </div>
            <?php foreach ($users as $user) { ?>
                <div class="foc">
                    <label><?php echo $user['name']; ?><input type="checkbox" id="insert_user_<?php echo $user['id']; ?>" name="user[]" value="<?php echo $user['id']; ?>" ></label>
                    <label>Amministratore compagnia <input type="checkbox" id="insert_user_<?php echo $user['id']; ?>" name="companyAdmin[]" value="<?php echo $user['id']; ?>" >
                    </label>
                </div>
            <?php } ?>
            <div class="foc">
                <input type="submit" value="Inserisci" />
            </div>
        </form>
    <?php } else if (isset($companyToModify)) { ?>
        <a href="company.php"><h2>Inserisci nuova compagnia</h2></a>
        <form name="update_company_<?php echo $companyToModify['id']; ?>" method="post" onsubmit="return validateUpdateUser(<?php echo $companyToModify['id']; ?>)" >
            <input type="hidden" name="said" value= "<?php echo $thisUserId; ?>">
            <input type="hidden" name="id" value= "<?php echo $companyToModify['id']; ?>">
            <input type="hidden" name="f" value= "uc"> 
            <div class="foc">
                <input type="text" name="name" value= "<?php echo $companyToModify['name']; ?>">
            </div>
            <?php foreach ($users as $user) { 
                $checked = isset($user['company'][$companyToModify['id']])?"checked":"";
                $adminchecked = isset($user['company'][$companyToModify['id']]) && $user['company'][$companyToModify['id']]['is_company_admin']?"checked":"";
                ?>
                <div class="foc">
                    <label><?php echo $user['name']; ?><input type="checkbox" id="insert_user_<?php echo $user['id']; ?>" name="user[]" value="<?php echo $user['id']; ?>"  <?php echo $checked; ?>></label>
                    <label>Amministratore compagnia <input type="checkbox" id="insert_user_<?php echo $user['id']; ?>" name="companyAdmin[]" value="<?php echo $user['id']; ?>"  <?php echo $adminchecked; ?>>
                    </label>
                </div>
            <?php } ?>
            <div class="foc">
                <input type="submit" value="Salva" />
            </div>
        </form>
        <div class="foc">
            <form name="delete_company_<?php echo $companyToModify['id']; ?>" method="post" >
                <input type="hidden" name="id" value= "<?php echo $companyToModify['id']; ?>">
                <input type="hidden" name="f" value= "dc">
                <input type="submit" value="elimina" />
            </form>
        </div>
    <?php } ?>
</div>