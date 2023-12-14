<?php
$hadPageTag = false;
foreach ($allBookings as $day => $bookingData) {
    ?>
        <?php if ($hadPageTag) { ?>
        <page>
    <?php } ?>
        <table border="1" style="border:1px black;border-collapse:collapse;width: 97%">
            <tr class="tableHeader">
                <td colspan="4"><?php echo $bookingData['title'] . ". " . $bookingData['dayOfTheWeek'] . " " . $day; ?>
                </td>
            </tr>
            <tr class="tableHeader">
                <td style="width:2%">&nbsp;</td>
                <td style="width:33%">Nome</td>
                <td style="width:32%">Contatto</td>
                <td style="width:33%">Note</td>
            </tr>
    <?php foreach ($bookingData['bookings'] as $i => $bookingName) { ?>
                <tr>
                    <td style="width:2%"><?php echo $i + 1; ?></td>
                    <td style="width:33%"><?php echo $bookingName['name']; ?></td>
                    <td style="width:32%"><?php echo $bookingName['riferimento']; ?></td>
                    <td style="width:33%">&nbsp;</td>
                </tr>
        <?php } ?>
        </table>
    <?php if ($hadPageTag) { ?>
        </page>
        <?php
    } ?>
<?php $hadPageTag = true;} ?>