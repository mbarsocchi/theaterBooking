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
    return validateAll(name, login) && validatePassword(password, password2);

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
    return validateAll(name, login);
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

function validateAll(name, login) {
    if (name == null || name == "") {
        alert("Inserisci un nome");
        return false;
    }

    if (login == null || login == "") {
        alert("Inserisci una login");
        return false;
    }
    var pat = /^[a-z0-9]+$/;
    if (pat.test(login) == false) {
        alert('login può contenere solo caratteri minuscoli e numeri. No spazi, no simboli');
        return false;
    }
}

function validateAddCompany() {
    var nameOfCompany = document.forms["addcompany"]["companyName"].value;
    if (nameOfCompany == null || nameOfCompany == "") {
        alert("Inserisci un nome della Compagnia");
        return false;
    }
}

function validatePrint() {
    if ($('input:checkbox:checked').length == 0) {
        alert("Seleziona almeno uno spettacolo da stampare");
        return false;
    }
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

