<?php
include("includes/header.php");

?>

<?php

$message_obj = new Message($con, $userLoggedIn);

if(isset($_GET['profile_username'])) {
  $username = $_GET['profile_username'];
  $user_deatails_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
  $user_array = mysqli_fetch_array($user_deatails_query);
  $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

if(isset($_POST['post'])){
  $uploadOk = 1;
	$imageName = $_FILES['fileToUpload']['name'];
	$errorMessage = "";

	if($imageName != "") {
		$targetDir = "assets/images/posts/";
		$imageName = $targetDir.uniqid().basename($imageName);
		$imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

		if($_FILES['fileToUpload']['size'] > 10000000) {
			$errorMessage = "Sorry your file is too large";
			$uploadOk = 0;
		}

		if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
			$errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
			$uploadOk = 0;
		}

		if($uploadOk) {
			if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
				//image uploaded
			}
			else {
				//image did not upload
				$uploadOk = 0;
			}
		}
	}

	if($uploadOk) {
		$post = new Post($con, $userLoggedIn);
		$post->submitPost($_POST['post_text'], $username, $imageName);
	}
	else {
		echo "<div style='text-align:center;' class='alert alert-danger'>
		      $errorMessage
		</div>";
	}
}

if(isset($_POST['remove_friend'])) {
  $user = new User($con, $userLoggedIn);
  $user->removeFriend($username);
}

if(isset($_POST['add_friend'])) {
  $user = new User($con, $userLoggedIn);
  $user->sendRequest($username);
}

if(isset($_POST['respond_request'])) {
  header("Location: requests.php");
}

if(isset($_POST['post_message'])) {
  if(isset($_POST['message_body'])) {
    $body = mysqli_real_escape_string($con, $_POST['message_body']);
    $date = date("Y-m-d H-i-s");
    $message_obj->sendMessage($username, $body, $date);
  }

  $link = '#profileTabs a[href="#messages_div"]';
  echo "<script>
    $(function() {
      $('".$link."').tab('show');
    });
  </script>";
}
 ?>

 <style media="screen">
   .wrapper {
     margin-left: 0px;
     padding-left: 0px;
   }
 </style>

 <div class="profile_left">
   <img src="<?php echo $user_array['profile_pic']; ?>" alt="">
   <h5 style="margin: 0 0 10px 28px; color:#fff;"><?php echo $user_array['first_name']." ".$user_array['last_name']; ?></h5>
   <div class="profile_info">
     <p><?php echo "Posts: ".$user_array['num_posts']; ?></p>
     <p><?php echo "Likes: ".$user_array['num_likes']; ?></p>
     <p><?php echo "Friends: ".$num_friends; ?></p>
   </div>

   <form class="" action="<?php echo $username; ?>" method="post">
     <?php
     $profile_user_obj = new User($con, $username);
     if($profile_user_obj->isClosed()) {
       header("Location: user_closed.php");
     }
     $logged_in_user_obj = new User($con, $userLoggedIn);

     if($userLoggedIn != $username) {
       if($logged_in_user_obj->isFriend($username)) {
         echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend" /><br />';
       }
       else if($logged_in_user_obj->didReceiveRequest($username)) {
         echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request" /><br />';
       }
       else if($logged_in_user_obj->didSendRequest($username)) {
         echo '<input type="submit" name="" class="default" value="Request Sent" /><br />';
       }
       else {
         echo '<input type="submit" name="add_friend" class="success" value="Add Friend" /><br />';
       }
     }
     ?>

   </form>

   <?php
   if ($userLoggedIn != $username) {
     echo '<div class="profile_info_bottom">';
         echo $logged_in_user_obj->getMutualFriends($username). " Mutual friends";
     echo '</div>';
   }
   ?>
 </div>

  <div class="main_column column">
    <ul class="nav nav-tabs" role="tablist" id="profileTabs">
      <li role="presentation" class="active"><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a></li>
      <?php if($username != $userLoggedIn) { ?>
      <li role="presentation"><a href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a></li>

    <?php } ?>
    </ul>

    <div class="tab-content">
      <div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
        <form class="post_form" action="<?php echo $username; ?>" method="POST" enctype="multipart/form-data">
    			<input type="file" name="fileToUpload" id="fileToUpload" value="">
    			<textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
    			<input type="submit" name="post" id="post_button" value="Post">
    			<hr>

    		</form>
        <div class="posts_area"></div>
    		<img id="loading" src="assets/images/icons/loading.gif">
      </div>

      <div role="tabpanel" class="tab-pane fade" id="messages_div">
        <?php

        echo "<h4>You and <a href='$username'>". $profile_user_obj->getFirstAndLastName(). "</a></h4><hr /><br />";
        echo "<div class='loaded_messages' id='scroll_messages'>";
             echo $message_obj->getMessages($username);
        echo "</div>";

         ?>

         <div class="message_post">
           <form class="" action="" method="post">
              <textarea name='message_body' id='message_textarea' placeholder='Write your message...'></textarea>
              <input type='submit' name='post_message' class='info' id='message_submit' value='Send' />
           </form>
         </div>
         <script>
           var div = document.getElementById("scroll_messages");
           div.scrollTop = div.scrollHeight;
         </script>
      </div>
    </div>
  </div>

<script>
var userLoggedIn = '<?php echo $userLoggedIn; ?>';
var profileUsername = '<?php echo $username; ?>';

$(document).ready(function() {

  $('#loading').show();

  //Original ajax request for loading first posts
  $.ajax({
    url: "includes/handlers/ajax_load_profile_posts.php",
    type: "POST",
    data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
    cache:false,

    success: function(data) {
      $('#loading').hide();
      $('.posts_area').html(data);
    }
  });

  $(window).scroll(function() {
    var height = $('.posts_area').height(); //Div containing posts
    var scroll_top = $(this).scrollTop();
    var page = $('.posts_area').find('.nextPage').val();
    var noMorePosts = $('.posts_area').find('.noMorePosts').val();

    if(($(window).scrollTop() + $(window).height() > $(document).height() - 200) && noMorePosts == 'false') {
      $('#loading').show();

      var ajaxReq = $.ajax({
        url: "includes/handlers/ajax_load_profile_posts.php",
        type: "POST",
        data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
        cache:false,

        success: function(response) {
          $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
          $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage

          $('#loading').hide();
          $('.posts_area').append(response);
        }
      });

    } //End if

    return false;

  }); //End (window).scroll(function())


});

</script>
</div>
</body>
</html>
