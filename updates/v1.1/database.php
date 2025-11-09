<?php
session_start();
error_reporting(1);

$db_config_path = '../application/config/database.php';

if (!isset($_SESSION["license_code"])) {
    $_SESSION["error"] = "Invalid purchase code!";
    header("Location: index.php");
    exit();
}

if (isset($_POST["btn_admin"])) {

    $_SESSION["db_host"] = $_POST['db_host'];
    $_SESSION["db_name"] = $_POST['db_name'];
    $_SESSION["db_user"] = $_POST['db_user'];
    $_SESSION["db_password"] = $_POST['db_password'];


    /* Database Credentials */
    defined("DB_HOST") ? null : define("DB_HOST", $_SESSION["db_host"]);
    defined("DB_USER") ? null : define("DB_USER", $_SESSION["db_user"]);
    defined("DB_PASS") ? null : define("DB_PASS", $_SESSION["db_password"]);
    defined("DB_NAME") ? null : define("DB_NAME", $_SESSION["db_name"]);

    /* Connect */
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $connection->query("SET CHARACTER SET utf8");
    $connection->query("SET NAMES utf8");

    /* check connection */
    if (mysqli_connect_errno()) {
        $error = 0;
    } else {
        
        mysqli_query($connection, "UPDATE settings SET version = '1.1' WHERE id = 1;");

        mysqli_query($connection, "ALTER TABLE `sessions` ADD `image` VARCHAR(255) NULL AFTER `is_default`, ADD `thumb` VARCHAR(255) NULL AFTER `image`, ADD `intro_video` VARCHAR(255) NULL AFTER `thumb`;");

        mysqli_query($connection, "ALTER TABLE `users` ADD `intro_video` VARCHAR(255) NULL AFTER `portfolio`;");

        mysqli_query($connection, "ALTER TABLE `sessions` ADD `enable_group_booking` INT NULL AFTER `is_default`, ADD `group_booking_slot` INT NULL AFTER `enable_group_booking`;");

        mysqli_query($connection, "ALTER TABLE `users` ADD `facebook_profile` VARCHAR(255) NULL AFTER `linkedin_profile`, ADD `instagram_profile` VARCHAR(255) NULL AFTER `facebook_profile`, ADD `x_profile` VARCHAR(255) NULL AFTER `instagram_profile`;");

        mysqli_query($connection, "ALTER TABLE `assign_time` ADD `person_per_slot` INT NULL AFTER `end`;");
        mysqli_query($connection, "ALTER TABLE `sessions` CHANGE `enable_group_booking` `enable_group_booking` INT NULL DEFAULT '0';");

        mysqli_query($connection, "ALTER TABLE `users` ADD `kyc_verified` VARCHAR(5) NULL DEFAULT '0' AFTER `phone_verified`;");
        mysqli_query($connection, "ALTER TABLE `settings` ADD `enable_kyc` VARCHAR(5) NULL DEFAULT '0' AFTER `enable_coupon`;");
        mysqli_query($connection, "ALTER TABLE `settings` ADD `custom_css` LONGTEXT NULL DEFAULT NULL AFTER `google_analytics`;");
        mysqli_query($connection, "ALTER TABLE `settings` ADD `pwa_logo` VARCHAR(155) NULL AFTER `link`, ADD `enable_pwa` INT NULL DEFAULT '0' AFTER `pwa_logo`;");


        // import database table
        $query = '';
          $sqlScript = file('sql/kyc_verifications.sql');
          foreach ($sqlScript as $line) {
            
            $startWith = substr(trim($line), 0 ,2);
            $endWith = substr(trim($line), -1 ,1);
            
            if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
              continue;
            }
              
            $query = $query . $line;
            if ($endWith == ';') {
              mysqli_query($connection, $query) or die('<div class="error-response sql-import-response">Problem in executing the SQL query <b>' . $query. '</b></div>');
              $query= '';   
            }
        }


        mysqli_query($connection, "INSERT INTO `lang_values` (`type`, `label`, `keyword`, `english`) VALUES
        ('user', 'Intro Video', 'intro-video', 'Intro Video'),
        ('user', 'Intro Video Url', 'intro-video-url', 'Intro Video Url ( embedded )'),
        ('user', 'Facebook Profile', 'facebook-profile', 'Facebook Profile'),
        ('user', 'Instagram Profile', 'instagram-profile', 'Instagram Profile'),
        ('user', 'X(Twitter) profile', 'x-profile', 'X(Twitter) profile'),
        ('user', 'Kyc', 'kyc', 'KYC'),
        ('user', 'Document', 'document', 'Document'),
        ('user', 'Document Number', 'document-number', 'Document Number'),
        ('user', 'Document Photo', 'document-photo', 'Document Photo'),
        ('user', 'Reject Reason', 'reject-reason', 'Reject Reason'),
        ('user', 'Calendar', 'calendar', 'Calendar'),
        ('user', 'National Id', 'national-id', 'National Id'),
        ('user', 'Passport', 'passport', 'Passport'),
        ('user', 'Driving License', 'driving-license', 'Driving License'),
        ('user', 'KYC Verification', 'kyc-verification', ' KYC Verification'),
        ('user', 'Requires a valid government issue ID (National ID, Passport, Drivers license)', 'kyc-document-requirments', 'Requires a valid government issue ID (National ID, Passport, Drivers license)'),
        ('user', 'Upload a proof of your Identity', 'upload-a-proof-of-your-identity', 'Upload a proof of your Identity'),
        ('user', 'Issuing Country/Region ', 'issuing-countryregion', 'Issuing Country/Region '),
        ('user', 'Document Type ', 'document-type', 'Document Type '),
        ('user', 'Front Side of Your', 'front-side-of-your', 'Front Side of Your'),
        ('user', 'Back Side of Your', 'back-side-of-your', 'Back Side of Your'),
        ('user', 'Passport Photo', 'passport-photo', 'Passport Photo'),
        ('user', 'Selfiee with', 'selfiee-with', 'Selfiee with'),
        ('user', 'Make sure your document and face in this same frame.', 'make-sure-your-document-and-face-in-this-same-frame', 'Make sure your document and face in this same frame.'),
        ('user', 'File accept: JPEG/JPG/PNG (Max size: 5mb)', 'file-accept-type', 'File accept: JPEG/JPG/PNG (Max size: 5mb)'),
        ('user', 'Face must be clear visible.', 'face-must-be-clear-visible', 'Face must be clear visible.'),
        ('user', 'Document should be good condition & valid period.', 'document-should-be-good-condition-valid-period', 'Document should be good condition & valid period.'),
        ('user', 'Personal Information', 'personal-information', 'Personal Information'),
        ('user', 'First Name', 'first-name', 'First Name'),
        ('user', '(As on Document)', 'as-on-document', '(As on Document)'),
        ('user', 'Last Name', 'last-name', 'Last Name'),
        ('user', 'Date of Birth', 'date-of-birth', 'Date of Birth'),
        ('user', 'By clicking Confirm, you acknowledge and grant consent for us to securely store and process your information.', 'acknowledge-checkbox-title', 'By clicking Confirm, you acknowledge and grant consent for us to securely store and process your information.'),
        ('user', 'Back to Previous', 'back-to-previous', 'Back to Previous'),
        ('user', 'Front Side Of', 'front-side-of', 'Front Side Of'),
        ('user', 'Back Side Of', 'back-side-of', 'Back Side Of'),
        ('user', 'Image of', 'image-of', 'Image of'),
        ('user', 'Slot for group booking', 'slot-for-group-booking', 'Allow number of persons per slot'),
        ('user', 'Group Booking Info', 'group-booking-info', 'Group Booking Info'),
        ('user', 'Your KYC information is currently being reviewed. Please wait for further updates.', 'kyc-pending-status', 'Your KYC information is currently being reviewed. Please wait for further updates.'),
        ('user', 'Congratulations! Your KYC information has been successfully verified and approved. You can now proceed with your intended actions or services.', 'kyc-approve-status', 'Your KYC information has been successfully verified and approved. You can now proceed with your intended actions or services.'),
        ('user', 'We regret to inform you that your KYC information has been rejected. Please review the provided guidelines and resubmit your information accordingly.', 'kyc-reject-status', 'We regret to inform you that your KYC information has been rejected. Please review the provided guidelines and resubmit your information accordingly.'),
        ('user', 'KYC Reject Reason', 'kyc-reject-reason-title', 'KYC Reject Reason'),
        ('user', 'Resubmitted at', 'resubmitted-at', 'Resubmitted at'),
        ('user', 'Enable to allow your Mentors to verify KYC documents', 'enable-kyc-title', 'Enable to allow your Mentors to verify KYC documents'),
        ('user', 'We kindly remind you that it is mandatory to submit your KYC (Know Your Customer) documents for account verification. Failure to submit these documents may result in restricted access to your account or services.', 'kyc-verify-alert-user', 'We kindly remind you that it is mandatory to submit your KYC (Know Your Customer) documents for account verification. Failure to submit these documents may result in restricted access to your account or services.'),
        ('user', 'Custom CSS', 'custom-css', 'Custom CSS'),
        ('user', 'Add your own css code here', 'add-your-own-css-code-here', 'Add your own css code here'),
        ('user', 'Learn that new skill, launch that project, land your dream career.', 'learn-that-new-skill-launch-that-project', 'Learn that new skill, launch that project, land your dream career.'),
        ('user', 'Browse Mentors by Categories', 'browse-mentors-by-categories', 'Browse Mentors by Categories'),
        ('user', 'PWA Settings', 'pwa-settings', 'PWA Settings'),
        ('user', 'Enable PWA (Progressive Web Apps)', 'enable-pwa', 'Enable PWA (Progressive Web Apps)'),
        ('user', 'Enable to allow your users to install PWA on their phone', 'pwa-enable-title', 'Enable to allow your users to install PWA on their phone'),
        ('user', 'You have reached the limit of free sessions! To continue, please add a price for your session.', 'free-session-limit', 'You have reached the limit of free sessions! To continue, please add a price for your session.'),
        ('user', 'Set price 0 for free session', 'set-price-0-for-free-session', 'Set price 0 for free session'),
        ('user', 'How many individuals will be permitted for booking per time slot', 'individuals-per-slot-booking', 'How many individuals will be permitted for booking per time slot'),
        ('user', 'Individual Booking', 'individual-booking', 'Individual Booking');");

            
      /* close connection */
      mysqli_close($connection);

      $redir = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
      $redir .= "://" . $_SERVER['HTTP_HOST'];
      $redir .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
      $redir = str_replace('updates/v1.1/', '', $redir);
      header("refresh:5;url=" . $redir);
      $success = 1;
    }



}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mentorship &bull; Update Installer</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/libs/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,500,600,700&display=swap" rel="stylesheet">
    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-12 col-md-offset-2">

                <div class="row">
                    <div class="col-sm-12 logo-cnt">
                        <p>
                           <img src="assets/img/logo.png" alt="">
                        </p>
                       <h1>Welcome to the update installer</h1>
                   </div>
               </div>

               <div class="row">
                <div class="col-sm-12">

                    <div class="install-box">

                        <div class="steps">
                            <div class="step-progress">
                                <div class="step-progress-line" data-now-value="100" data-number-of-steps="3" style="width: 100%;"></div>
                            </div>
                            <div class="step" style="width: 50%">
                                <div class="step-icon"><i class="fa fa-arrow-circle-right"></i></div>
                                <p>Start</p>
                            </div>
                            <div class="step active" style="width: 50%">
                                <div class="step-icon"><i class="fa fa-database"></i></div>
                                <p>Database</p>
                            </div>
                        </div>

                        <div class="messages">
                            <?php if (isset($message)) { ?>
                            <div class="alert alert-danger">
                                <strong><?php echo htmlspecialchars($message); ?></strong>
                            </div>
                            <?php } ?>
                            <?php if (isset($success)) { ?>
                            <div class="alert alert-success">
                                <strong>Completing Updates ... <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Please wait 5 second </strong>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="step-contents">
                            <div class="tab-1">
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                    <div class="tab-content">
                                        <div class="tab_1">
                                            <h1 class="step-title">Database</h1>
                                            <div class="form-group">
                                                <label for="email">Host</label>
                                                <input type="text" class="form-control form-input" name="db_host" placeholder="Host"
                                                value="<?php echo isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : 'localhost'; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Database Name</label>
                                                <input type="text" class="form-control form-input" name="db_name" placeholder="Database Name" value="<?php echo @$_SESSION["db_name"]; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Username</label>
                                                <input type="text" class="form-control form-input" name="db_user" placeholder="Username" value="<?php echo @$_SESSION["db_user"]; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Password</label>
                                                <input type="password" class="form-control form-input" name="db_password" placeholder="Password" value="<?php echo @$_SESSION["db_password"]; ?>">
                                            </div>

                                        </div>
                                    </div>

                                    <div class="buttons">
                                        <a href="index.php" class="btn btn-success btn-custom pull-left">Prev</a>
                                        <button type="submit" name="btn_admin" class="btn btn-success btn-custom pull-right">Finish</button>
                                    </div>
                                </form>
                            </div>
                        </div>


                    </div>
                </div>
            </div>


        </div>


    </div>


</div>

<?php

unset($_SESSION["error"]);
unset($_SESSION["success"]);

?>

</body>
</html>

