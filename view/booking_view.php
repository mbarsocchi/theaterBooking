<?php if (count($allBookings)) { ?>
    <form name="insertBooking" method="post" onsubmit="return validateAddBooking()" >
        <select name="showId" style="margin-bottom: 20px;width:100%">
            <?php foreach ($allBookings as $day => $bookingData) { ?>
            <option value="<?php echo$bookingData['id']; ?>"><?php echo $bookingData['title']. ". ".$bookingData['dayOfTheWeek'] . " " . $day; ?>. Liberi: <?php echo $bookingData['freeSeats']; ?></option>
            <?php } ?>
        </select><br />
        <input type="hidden" name="f" value="b"/>
        <input type="text" name="name" size="35" autofocus style="margin-bottom: 20px;width:100%"/><br />
        <?php if ($isAdmin) { ?>
            Inserisci come: <select name="user" style="margin-bottom: 20px;">";
            <?php foreach ($usersInScope as $user) {
                if (strcasecmp($thisUserId, trim($user['id'])) == 0) { ?>
                    <option selected="selected" value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php } else { ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php }
            }?>
            </select>
        <?php } else { ?>
            <input type="hidden" name="user" value="<?php echo $thisUserId; ?>">
        <?php } ?><br />
        <input type="submit" value="Inserisci prenotazione" style="margin-bottom: 20px;width:100%"/>    
    </form>
    <?php foreach ($allBookings as $day => $bookingData) { ?>
       <table border="1" style="border:1px black;border-collapse:collapse;">
       <tr class="tableHeader">
            <td colspan="3"><?php echo $bookingData['title']. ". ".$bookingData['dayOfTheWeek'] . " " . $day; ?><br />
               Prenotati: <?php echo $bookingData['occupiedSeats']; ?><br />
               Liberi: <?php echo $bookingData['freeSeats']; ?></td>
        </tr>
        <tr class="tableHeader">
            <td style="width:2%">&nbsp;</td>
            <td style="width:14%">Nome</td>
            <td style="width:14%">Contatto</td>
        </tr>
        <?php foreach ($bookingData['bookings'] as $i => $bookingName) { ?>
                <tr>
                    <td style="width:2%"><?php echo $i + 1; ?></td>
                    <td style="width:14%"><?php echo $bookingName['name']; ?>
                    <?php if ( $isAdmin || $thisUserId == $bookingName['riferimentoId']) { ?>
                        <form name="delete_bookin_<?php echo $bookingName['id']; ?>" method="post">
                            <input type="hidden" name="id" value= "<?php echo $bookingName['id']; ?>">
                            <input type="hidden" name="f" value= "db">
                            <input type="submit" value="elimina" />
                        </form>
                    <?php }?>
                    </td>
                    <td style="width:14%"><?php echo $bookingName['riferimento']; ?></td>
                </tr>
        <?php } ?>
        </table>
    <?php } ?>
<?php } ?>
<br/><br/>
<?php if ($isAdmin) { ?>
    <a href="admin.php">Gestisci spettacoli</a>
<?php } ?>