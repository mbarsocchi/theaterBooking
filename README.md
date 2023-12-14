# theaterBooking
A WebApp to manage Theater booking for amateur companies

This is a child project of the wordpress plugin that do similar stuff. The 
webapp is able to 

- Create shows
- Create users
- Associate users to shows
- Every user can add, modify and delete only people previously added
- Seats are a constrain to booking add, so no more than X people will be booked

# Install
Install html2pdf with composer, run this command in the root of this project

```bash
composer require spipu/html2pdf'
```

configure config_default.php and save it as config.php