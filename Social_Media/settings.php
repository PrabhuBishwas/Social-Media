<?php
include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
 ?>

 <div class="main_column column">
   <h4>Account Settings</h4>
   <?php
   echo "<img src='" . $user['profile_pic'] . "' id='small_profile_pics' />";

    ?>
    <br />
    <a href="upload.php">Upload a new profile picture</a><br><br><br>

    Modify the values and click 'Update Details'<br>

    <?php
    $user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username='$userLoggedIn'");
    $row = mysqli_fetch_array($user_data_query);

    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $email = $row['email'];
     ?>

    <form action="settings.php" method="post">
      <label class="setting_label">First Name: </label><input type="text" name="first_name" value="<?php echo $first_name; ?>" autocomplete="off" id="settings_input"><br>
      <label class="setting_label">Last Name: </label><input type="text" name="last_name" value="<?php echo $last_name; ?>" autocomplete="off" id="settings_input"><br>
      <label class="setting_label">Email: </label><input type="email" name="email" value="<?php echo $email; ?>" autocomplete="off" id="settings_input"><br>

      <?php echo $message; ?>
      <input type="submit" class="info settings_submit" name="update_details" id="save_details" value="Update Details"><br>
    </form>

    <h4>Change Password</h4>
    <form action="settings.php" method="post">
      <label class="setting_label">Old Password: </label><input type="password" name="old_password" id="settings_input"><br>
      <label class="setting_label">New Password: </label><input type="password" name="new_password_1" id="settings_input"><br>
      <label class="setting_label">New Password Again: </label><input type="password" name="new_password_2" id="settings_input"><br>
      <?php echo $password_message; ?>
      <input type="submit" class="info settings_submit" name="update_password" id="save_password" value="Update Password"><br>
    </form>

    <h4>Close Account</h4>
    <form action="settings.php" method="post">
      <input type="submit" name="close_account" id="close_account" value="Close Account" class="danger settings_submit">
    </form>
 </div>
