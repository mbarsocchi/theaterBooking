<?php include_once 'part_meta_head.php';?>
<?php if (isset($isLogged) && $isLogged) { ?>
    <section class="top-nav">
        <input id="menu-toggle" type="checkbox" />
        <label class='menu-button-container' for="menu-toggle">
            <div class='menu-button'></div>
        </label>
        <ul class="menu">
            <li><?php if (isset($thispage) && $thispage == 'booking') { ?>Prenotazioni
                <?php } else { ?><a href="index.php">Prenotazioni</a><?php } ?></li>
            <li><?php if (isset($thispage) && $thispage == 'print') { ?>Stampa
                <?php } else { ?><a href="print.php">Stampa</a><?php } ?></li>
            <?php if ($isAdmin) { ?>
                <li><?php if (isset($thispage) && $thispage == 'company') { ?>Gestisci compagnie
                    <?php } else { ?><a href="company.php">Gestisci compagnie</a><?php } ?></li>      
            <?php } ?>
            <?php if ($isAdmin || $isCompanyAdmin) { ?>
                <li><?php if (isset($thispage) && $thispage == 'shows') { ?>Gestisci spettacoli
                    <?php } else { ?><a href="shows.php">Gestisci spettacoli</a><?php } ?></li>
                <li><?php if (isset($thispage) && $thispage == 'user') { ?>Gestisci utenti
                    <?php } else { ?><a href="users.php">Gestisci utenti</a><?php } ?></li>        
            <?php } ?>
            <li><a href="logout.php">Esci</a></li>
        </ul>
    </section>
<div class="mid-spacer"></div>
<?php } ?>