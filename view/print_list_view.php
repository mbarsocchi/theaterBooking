<form action="print.php" method="post" onsubmit="return validatePrint()" >
    <input type="hidden" name="f" value= "pr">
    <?php if (count($futureShow) > 0) { ?>
        <?php foreach ($futureShow as $oneShow) { 
            $date = new DateTime($oneShow['data']);
            $dateFormatted = $date->format('d/m/y h:i');?>
            <div class="foc" >
                <input type="checkbox" class="checkbox" name="showId[]" value="<?php echo $oneShow['id']; ?>"> <?php echo $dateFormatted; ?> <?php echo $oneShow['nome']; ?>
            </div>
        <?php } ?>
        <input type="submit" value="Stampa" />
    <?php } ?>
</form>