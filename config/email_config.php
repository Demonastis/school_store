<?php
// school_store/config/email_config.php

define('SMTP_HOST', 'smtp.gmail.com');                  // CORRECTED: Changed from '://gmail.com'
define('SMTP_PORT', 587);                               // 587 for TLS, 465 for SSL
define('SMTP_USER', 'yo email');   // Your actual system email address
define('SMTP_PASS', 'sadf sadi zxcz sadf');             // Your 16-character Google App Password marami need gawin sa main gmail account
define('SMTP_FROM', 'purchasing@schoolstore.edu.ph');   // Sender display email
define('SMTP_NAME', 'EduManage Purchasing Department'); // Sender display name
?>
