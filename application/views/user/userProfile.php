<!DOCTYPE html>
<html>
<head>
	<title>Law Calendar</title>
	<!-- Global Css using Helper -->
	<?php 
			globalCss(); 
	?>
	<!--/ Global Css using Helper -->
	<?php
	//session_start();

	require_once('google-calendar-api.php');
	require_once('settings.php');
	$capi = new GoogleCalendarApi();
	$isGooogleConnected = false;
	$error = "";
	// Google passes a parameter 'code' in the Redirect Url
	if(isset($_GET['code'])) {
		try {
			// Get the access token 
			$data = $capi->GetAccessToken(CLIENT_ID, CLIENT_REDIRECT_URL, CLIENT_SECRET, $_GET['code']);
			
			// Refresh Token
			if(array_key_exists('refresh_token', $data))
			$_SESSION['refresh_token'] = $data['refresh_token'];

			// Save the access token expiry timestamp
			$_SESSION['access_token_expiry'] = time() + $data['expires_in'];


			// Save the access token as a session variable
			$_SESSION['access_token'] = $data['access_token'];
			
			// Redirect to the page where user can create event
			header('Location: userProfile');
			exit();

		}
		catch(Exception $e) {
			echo $e->getMessage();
			exit();
		}
	}

	if (isset($_SESSION['refresh_token'])) {
	if(time() > $_SESSION['access_token_expiry']) {
	// Get a new access token using the refresh token
	$data = $capi->GetRefreshedAccessToken(CLIENT_ID, $_SESSION['refresh_token'], CLIENT_SECRET);
	
	// Again save the expiry time of the new token
	$_SESSION['access_token_expiry'] = time() + $data['expires_in'];

	// The new access token
	$_SESSION['access_token'] = $data['access_token'];
	}
	}

	if(isset($_SESSION['access_token'])) {
	$isGooogleConnected = true;
	}

		//Login Url
		$login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=offline&prompt=consent';

	?>
</head>
<body>
	<!-- Navbar -->
		<?php include 'navbar.php'?>
	<!--/ Navbar -->
		<?php 
		if(isset($_SESSION['access_token'])){
		try{
		$user_info = $capi->GetUserProfileInfo($_SESSION['access_token']);
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}}
	?>
	<!-- Search Bar -->
		<div class="container-fluid margin-top-25">
			<div class="container">
				<ol class="breadcrumb">
				  <li class="breadcrumb-item"><a href="home">Home</a></li>
				  <li class="breadcrumb-item active">Settings</li>
				 </ol>
				 
				  <?php
				  if (isset($user_info['email'])) {
				  	?>
				  	Connected Account: <strong style="font-weight: 900"><?= $user_info['email']?></strong>
				  	<?php
				  }
				  else{
				  	echo $error;
				  }
				  ?>
		    </div>
		</div>
	<!--/ Search Bar -->

	<div class="container-fluid table categories-table">
		<div class="message container">
			<div class="row">
				<div class="col-5">
					<?php if($success = $this->session->flashdata('success')):?>
				    	<div class="alert alert-success">
				    		<?= $success; ?>
				    	</div>
				    <?php endif;?>

				    <?php if($error = $this->session->flashdata('error')):?>
				    	<div class="alert alert-danger">
				    		<?= $error; ?>
				    	</div>
				    <?php endif;?>
				    <?php if($warning = $this->session->flashdata('warning')):?>
				    	<div class="alert alert-warning">
				    		<?= $warning; ?>
				    	</div>
				    <?php endif;?>
				</div>
				<div class="col-7"></div>
			</div>
		</div>
		<div class="container">
			  <div class="tab-pane fade show" id="profile">
			    <div><label>Google Account</label> 
				</div>
				<?php if(!$isGooogleConnected){?>
					<div><a href="<?= $login_url ?>" class="btn btn-primary">Connect</a>
			   		 <small class="form-text text-muted">Connected Google Account will be used to save deadlines into Google Calendar</small></div>
			   		 <?php } else {?>
			   		 	<div><a href="<?= base_url('user/MainController/googleDisconnect')?>" class="btn btn-primary">Disconnect</a>
			   		 <small class="form-text text-muted">Disconnect From Connected Google</small></div>
			   		 <?php }?>
			   		 <hr>
			   		 <ul class="nav nav-tabs">
					  <li class="nav-item">
					    <a class="nav-link active" data-toggle="tab" href="#holidays">Holidays</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link" data-toggle="tab" href="#setting">Settings</a>
					  </li>
					</ul>
					<div id="myTabContent" class="tab-content">
					  <div class="tab-pane fade active show" id="holidays">
					  	<div class="row">
					   <div class="col-sm-4">
						<?= form_open('user/MainController/addHoliday'); ?>
						  <fieldset>
						    <legend>Add Holidays</legend>
						    <div class="form-group">
						      <label for="holdayTitle">Title*</label>
						      <?= form_input(['placeholder'=>'Title','name'=>'holidayTitle','class'=>'form-control','id'=>'holidayTitle','aria-describedby'=>'holidayTitle']); ?>
							  <?= form_error('holidayTitle');?>
						  	</div>

						    <div class="form-group">
						      <label for="holdayDate">Date*</label>
						      <?= form_input(['placeholder'=>'Date','name'=>'holidayDate','class'=>'form-control','id'=>'holidayDate','type'=>'date']); ?>
						      <small id="newPassword" class="form-text text-muted"></small>
							  <?php echo form_error('holidayDate');?>
						  	</div>

						    <div class="form-group">
						  	</div>
						    <?= form_submit(['value'=>'Add','class'=>'btn btn-primary']); ?>
						  </fieldset>
						  <?= form_close();?>
						</div>
						<div class="col-sm-1"></div>
						<div class="col-sm-7" class="table">
							<?php
								if($holidays) {
									?>
									<div><h3>Holidays</h3></div>
									<?php
									echo "<table>";
									foreach ($holidays as $holiday) {
										?>
											<tr>
											<td style="font-weight: 900"><?= $holiday->title?></td>
											<td><?= date("F d , Y", strtotime($holiday->date)) ?></td>
											<td><a href="user/MainController/deleteHoliday/<?= $holiday->ID ?>" class="delete" style="color: #e70808; font-weight: 900"> Delete</a></td>
											</tr>
										<?php
									}
									echo "</table>";
								}
								else{
									echo "No holiday to show";
								}
							?>
						</div>
						</div>
					  </div>
					  <div class="tab-pane fade" id="setting">
					   
					  	<div class="col-sm-6">
						<?= form_open('user/MainController/changePassword'); ?>
						  <fieldset>
						    <legend>Change Password</legend>
						    <div class="form-group">
						      <label for="password">Current Password*</label>
						      <?= form_input(['placeholder'=>'Current Password','name'=>'currentPassword','class'=>'form-control','id'=>'currentPassword','aria-describedby'=>'currentPassword']); ?>
							  <?= form_error('currentPassword');?>
						  	</div>

						    <div class="form-group">
						      <label for="password">New Password*</label>
						      <?= form_input(['placeholder'=>'New Password','name'=>'newPassword','class'=>'form-control','id'=>'newPassword','aria-describedby'=>'newPassword']); ?>
						      <small id="newPassword" class="form-text text-muted"></small>
							  <?php echo form_error('newPassword');?>
						  	</div>

						    <div class="form-group">
						  	</div>
						    <?= form_submit(['value'=>'Change','class'=>'btn btn-primary']); ?>
						  </fieldset>
						  <?= form_close(); ?>
						</div>
					  </div>
					</div>
			</div>
		</div>
	</div>
</body>
	<?php 
			globalJs(); 
	?>
</script>
</html>
