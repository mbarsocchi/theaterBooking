<form action="print.php" method="post" onsubmit="return validatePrint()" >
    <input type="hidden" name="f" value= "pr">
    <?php if (count($futureShow) > 0) { ?>
        <?php
        $checked = "checked";
        foreach ($futureShow as $oneShow) {
            $date = new DateTime($oneShow['data']);
            $dateFormatted = $date->format('d/m/y H:i');
            ?>
            <div class="foc" >
                <input type="checkbox" class="checkbox" name="showId[]" value="<?php echo $oneShow['id']; ?>" <?php echo $checked; ?>> <?php echo $dateFormatted; ?> <?php echo $oneShow['nome']; ?>
            </div>
            <?php $checked = "";
        } ?>
        <input type="submit" value="Stampa" />
<?php } ?>
</form>