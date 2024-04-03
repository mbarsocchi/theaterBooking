<div class="content">
    <h2><?php if (count($companies)) { ?>
        Modifica:
            <?php foreach ($companies as $key => $company) { ?>
                <?php if (isset($companyToModify['id']) && $companyToModify['id'] == $key) { ?>
                    <?php echo $company['name']; ?>
                <?php } else { ?>
                    <a href="?cu=<?php echo $key; ?>"><?php echo $company['name']; ?></a>                
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </h2>
    <?php if (count($companies) && !isset($companyToModify)) { ?>
        <h2>Inserisci nuova compagnia</h2>
        <form name="adduser" method="post" onsubmit="return validateAddUser()">
            <input type="hidden" name="said" value= "<?php echo $thisUserId; ?>">
            <div class="foc">
                <input type="hidden" name="f" value= "au">
            </div>
            <div class="foc">
                <input type="text" name="name" placeholder="Nome compagnia" value= "">
            </div>
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
                <input type="text" name="name" value= "<?php echo $companyToModify['nome']; ?>">
            </div>
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