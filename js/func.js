function validateAddBooking() {
    var nameOfViewer = document.forms["insertBooking"]["name"].value;
    if (nameOfViewer == null || nameOfViewer == "") {
        alert("Inserisci un nome");
        return false;
    }
    if (/\d/.test(nameOfViewer)) {
        alert("Non possono esserci numeri nel nome della prenotazione.\n\
se stai cercando di prenotare più persone con lo stesso nome (esmpio Marco x 2) devi scrivere due volte Marco\n\
altrimenti non è possibile fare il conto dei posti liberi e di quelli occupati.");
        return false;
    }
}

function confirmDeleteBooking() {
    return confirm("Sei sicuro di voler cancellare questa prenotazione?");
}

function validateAddUser() {
    var password = document.forms["adduser"]["password"].value;
    var password2 = document.forms["adduser"]["passwordvalidate"].value;
    var name = document.forms["adduser"]["name"].value;
    var login = document.forms["adduser"]["login"].value;
    return validateAll(name, password, password2, login) && validatePassword(password, password2);

}

function validateUpdateUser(index) {
    var form = document.forms["update_user_" + index];
    var password = form["password"].value;
    var password2 = form["passwordvalidate"].value;
    var name = form["name"].value;
    var login = form["login"].value;
    if (password != "" && password2 != "") {
        validatePassword(password, password2);
    }
    return validateAll(name, password, password2, login);
}
function validatePassword(password, password2) {
    if (password == null || password == "") {
        alert("Inserisci una password");
        return false;
    } else {
        if (password != password2) {
            alert("le due password devono essere uguali");
            return false;
        }
    }
}

function validateAll(name, password, password2, login) {
    if (name == null || name == "") {
        alert("Inserisci un nome");
        return false;
    }

    if (login == null || login == "") {
        alert("Inserisci una login");
        return false;
    }
}

function validatePrint(){
//     if ($('input:checkbox:checked[id^="show_' + $id + '"]').length != 0) {
//        alert("Non puoi cancellare uno show senza prima cancellare tutti gli utenti associati");
//        return false;
//    }
    return true;
}

function subAddShow() {
    var name = document.forms["addshow"]["namei"].value;
    var seat = document.forms["addshow"]["seatsi"].value;
    var data = document.forms["addshow"]["timestamp"].value;
    if (name == null || name == "") {
        alert("Inserisci un nome");
        return false;
    }
    if (seat == null || seat == "" || isNaN(seat)) {
        alert("Inserisci il numero di posti correttamente");
        return false;
    }
    if (data == null || data == "") {
        alert("Inserisci una data");
        return false;
    }

}

function deleteShows($id) {
    if ($('input:checkbox:checked[id^="show_' + $id + '"]').length != 0) {
        alert("Non puoi cancellare uno show senza prima cancellare tutti gli utenti associati");
        return false;
    }
}

function check_ticket_sub() {
    var oneLength = document.inputticket["idsofbooked[]"].checked;
    var flag = 0;
    var length = 0;
    if (typeof oneLength !== 'undefined' && oneLength) {
        flag = 1;
    } else {
        var length = document.inputticket["idsofbooked[]"].length;
        flag = 0;
        for (var i = 0; i < length; i++) {
            if (document.inputticket["idsofbooked[]"][i].checked) {
                flag = flag + 1;
            }
        }
    }


    if (flag == 0) {
        alert("Selezione un prenotato");
        return false;
    } else {
        var r = confirm("ATTENZIONE\n" +
                "Generando il biglietto non ti sara' piu' possibile modificare\n" +
                "oppure eliminare la persona prenotata.\n" +
                "Questo, teoricamente, significa che hai gia' in mano i soldi di tale persona.\n\n" +
                "In caso di problemi contatta l'amministratore\n\n" +
                "Vuoi continuare?");
        return r;
    }
}


function show_calendar(str_target, str_datetime) {
    var arr_months = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    var week_days = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
    var n_weekstart = 1; // day week starts from (normally 0 or 1)
    var dt_datetime = (str_datetime == null || str_datetime == "" ? new Date() : str2dt(str_datetime));
    var dt_prev_month = new Date(dt_datetime);
    dt_prev_month.setMonth(dt_datetime.getMonth() - 1);
    var dt_next_month = new Date(dt_datetime);
    dt_next_month.setMonth(dt_datetime.getMonth() + 1);
    var dt_firstday = new Date(dt_datetime);
    dt_firstday.setDate(1);
    dt_firstday.setDate(1 - (7 + dt_firstday.getDay() - n_weekstart) % 7);
    var dt_lastday = new Date(dt_next_month);
    dt_lastday.setDate(0);

    // html generation (feel free to tune it for your particular application)
    // print calendar header
    var str_buffer = new String(
            "<html>\n" +
            "<head>\n" +
            "	<title>Calendar</title>\n" +
            "</head>\n" +
            "<body bgcolor=\"White\">\n" +
            "<table class=\"clsOTable\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n" +
            "<tr><td bgcolor=\"#4682B4\">\n" +
            "<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\" width=\"100%\">\n" +
            "<tr>\n	<td bgcolor=\"#4682B4\"><a href=\"javascript:window.opener.show_calendar('" +
            str_target + "', '" + dt2dtstr(dt_prev_month) + "'+document.cal.time.value);\">" +
            "<<</a></td>\n" +
            "	<td bgcolor=\"#4682B4\" colspan=\"5\">" +
            "<font color=\"white\" face=\"tahoma, verdana\" size=\"2\">"
            + arr_months[dt_datetime.getMonth()] + " " + dt_datetime.getFullYear() + "</font></td>\n" +
            "	<td bgcolor=\"#4682B4\" align=\"right\"><a href=\"javascript:window.opener.show_calendar('"
            + str_target + "', '" + dt2dtstr(dt_next_month) + "'+document.cal.time.value);\">" +
            ">></a></td>\n</tr>\n"
            );

    var dt_current_day = new Date(dt_firstday);
    // print weekdays titles
    str_buffer += "<tr>\n";
    for (var n = 0; n < 7; n++)
        str_buffer += "	<td bgcolor=\"#87CEFA\">" +
                "<font color=\"white\" face=\"tahoma, verdana\" size=\"2\">" +
                week_days[(n_weekstart + n) % 7] + "</font></td>\n";
    // print calendar table
    str_buffer += "</tr>\n";
    while (dt_current_day.getMonth() == dt_datetime.getMonth() ||
            dt_current_day.getMonth() == dt_firstday.getMonth()) {
        // print row heder
        str_buffer += "<tr>\n";
        for (var n_current_wday = 0; n_current_wday < 7; n_current_wday++) {
            if (dt_current_day.getDate() == dt_datetime.getDate() &&
                    dt_current_day.getMonth() == dt_datetime.getMonth())
                // print current date
                str_buffer += "	<td bgcolor=\"#FFB6C1\" align=\"right\">";
            else if (dt_current_day.getDay() == 0 || dt_current_day.getDay() == 6)
                // weekend days
                str_buffer += "	<td bgcolor=\"#DBEAF5\" align=\"right\">";
            else
                // print working days of current month
                str_buffer += "	<td bgcolor=\"white\" align=\"right\">";

            if (dt_current_day.getMonth() == dt_datetime.getMonth())
                // print days of current month
                str_buffer += "<a href=\"javascript:window.opener." + str_target +
                        ".value='" + dt2dtstr(dt_current_day) + "'+document.cal.time.value; window.close();\">" +
                        "<font color=\"black\" face=\"tahoma, verdana\" size=\"2\">";
            else
                // print days of other months
                str_buffer += "<a href=\"javascript:window.opener." + str_target +
                        ".value='" + dt2dtstr(dt_current_day) + "'+document.cal.time.value; window.close();\">" +
                        "<font color=\"gray\" face=\"tahoma, verdana\" size=\"2\">";
            str_buffer += dt_current_day.getDate() + "</font></a></td>\n";
            dt_current_day.setDate(dt_current_day.getDate() + 1);
        }
        // print row footer
        str_buffer += "</tr>\n";
    }
    // print calendar footer
    str_buffer +=
            "<form name=\"cal\">\n<tr><td colspan=\"7\" bgcolor=\"#87CEFA\">" +
            "<font color=\"White\" face=\"tahoma, verdana\" size=\"2\">" +
            "Time: <input type=\"text\" name=\"time\" value=\"" + dt2tmstr(dt_datetime) +
            "\" size=\"8\" maxlength=\"8\"></font></td></tr>\n</form>\n" +
            "</table>\n" +
            "</tr>\n</td>\n</table>\n" +
            "</body>\n" +
            "</html>\n";

    var vWinCal = window.open("", "Calendar",
            "width=200,height=250,status=no,resizable=yes,top=200,left=200");
    vWinCal.opener = self;
    var calc_doc = vWinCal.document;
    calc_doc.write(str_buffer);
    calc_doc.close();
}
// datetime parsing and formatting routimes. modify them if you wish other datetime format
function str2dt(str_datetime) {
    var re_date = /^(\d+)\-(\d+)\-(\d+)\s+(\d+)\:(\d+)\:(\d+)$/;
    if (!re_date.exec(str_datetime))
        return alert("Invalid Datetime format: " + str_datetime);
    return (new Date(RegExp.$1, RegExp.$2 - 1, RegExp.$3, RegExp.$4, RegExp.$5, RegExp.$6));
}
function dt2dtstr(dt_datetime) {
    return (new String(
            dt_datetime.getFullYear() + "-" + (dt_datetime.getMonth() + 1) + "-" + dt_datetime.getDate() + " "));
}
function dt2tmstr(dt_datetime) {
    var currentHours = (dt_datetime.getHours() < 10 ? "0" : "") + dt_datetime.getHours();
    var currentMinutes = (dt_datetime.getMinutes() < 10 ? "0" : "") + dt_datetime.getMinutes();
    var currentSecond = (dt_datetime.getSeconds() < 10 ? "0" : "") + dt_datetime.getSeconds();

    return (new String(
            currentHours + ":" + currentMinutes + ":" + currentSecond));
}

