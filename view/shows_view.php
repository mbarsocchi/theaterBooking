<div class="content">
    <h2><?php if (count($futureShow) > 0) { ?>
        Modifica:
            <?php foreach ($futureShow as $oneShow) { ?>
                <?php if (isset($showToModify['id']) && $showToModify['id'] == $oneShow['id']) { ?>
                    <?php echo $oneShow['nome'] . " " . $oneShow['data']; ?>
                <?php } else { ?>
                    <a href="shows.php?si=<?php echo $oneShow['id']; ?>"><?php echo $oneShow['nome'] . " " . $oneShow['data']; ?></a>                
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </h2>
    <link rel="stylesheet" type="text/css" href="css/DateTimePicker.min.css" />
    <script type="text/javascript" src="js/DateTimePicker.min.js"></script>

    <!--[if lt IE 9]>
     <link rel="stylesheet" type="text/css" href="DateTimePicker-ltie9.css" />
     <script type="text/javascript" src="DateTimePicker-ltie9.js"></script>
    <![endif]-->

    <!-- For i18n Support -->
    <script type="text/javascript" src="js/i18n/DateTimePicker-i18n.js"></script>
    <?php if (!isset($showToModify)) { ?>       
        <h2>Inserisci nuovo show</h2>
        <form name="addshow" method="post" onsubmit="return subAddShow()">
            <input type="hidden" name="f" value= "i">
            <input type="hidden" name="userid" value= "<?php echo $thisUserId; ?>">
            <div class="foc">
                <script>$(document).ready(function () {
                        $("#dtBox").DateTimePicker();
                    });
                </script>
                <input type="text" name="timestamp" data-field="datetime" placeholder="Data" readonly>
                <div id="dtBox"></div>
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
    <?php } else if (isset($showToModify)) { ?>
        <a href="shows.php"><h2>Inserisci nuovo show</h2></a>
        <form name="showdate<?php echo $showToModify['id']; ?>" method="post">
            <input type="hidden" name="id" value= "<?php echo $showToModify['id']; ?>">
            <input type="hidden" name="f" value= "u">
            <div class="foc">
                <script>$(document).ready(function () {
                        $("#dtBox1").DateTimePicker();
                    });
                </script>
                <input type="text" name="timestamp" data-field="datetime" placeholder="Data" value= "<?php echo $showToModify['data']; ?>" readonly>
                <div id="dtBox1"></div>
            </div>    
            <div class="foc">
                <input type="text" name="name" value= "<?php echo $showToModify['nome']; ?>">
            </div>  
            <div class="foc">
                <input type="text" name="location" placeholder="Nome teatro" value="<?php echo $showToModify['luogo']; ?>">
            </div>       
            <div class="foc">
                <input type="text" name="details"  placeholder="Dettagli"  value="<?php echo $showToModify['dettagli']; ?>">
            </div>
            <div class="foc">
                <input type="text" name="seats" value= "<?php echo $showToModify['posti']; ?>">
            </div>
            <div class="foc">
                <input type="submit" value="Salva" style="display: inline-block;"/>             
            </div>
        </form>
    <form name="delete<?php echo $showToModify['id']; ?>" method="post" onsubmit="return deleteShows(<?php echo $showToModify['id']; ?>)">
        <input type="hidden" name="id" value= "<?php echo $showToModify['id']; ?>">
        <input type="hidden" name="f" value= "d">
        <div class="foc">
            <input type="submit" value="Elimina" style="display: inline-block;" />
        </div>
    </form>
<?php } ?>
</div>
