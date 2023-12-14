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
                <li><?php if (isset($thispage) && $thispage == 'admin') { ?>Gestisci spettacoli
                    <?php } else { ?><a href="admin.php">Gestisci spettacoli</a><?php } ?></li><?php } ?>
            <li><a href="logout.php">Esci</a></li>
        </ul>
    </section>
<div class="mid-spacer"></div>
<?php } ?>