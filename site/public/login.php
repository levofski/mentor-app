<!DOCTYPE HTML>
<html dir="ltr" lang="en-US">
	<head>
		<meta http-equiv="Content-Type"
			content="text/html;charset=utf-8">
		<meta name="viewport"
			content="initial-scale=1.0">
		<title>PHP Mentoring: Log In</title>
		<link href="https://fonts.googleapis.com/css?family=Architects+Daughter"
			media="screen"
			rel="stylesheet"
			type="text/css">
		<link href="./assets/styles/style-main.css"
			media="all"
			rel="stylesheet"
			type="text/css">
	</head>
	<body>
		<!-- {% include "./assets/templates/header.twig" %} -->
		<div class="header_row primary_row row">
			<div class="column header_column primary_column">
				<div class="cell header_cell primary_cell">
					<h1 class="header heading">PHP Mentoring</h1>
					<div class="header_caption">Building strong developers</div>
				</div>
			</div>
		</div>
		<div class="primary_row row">
			<div class="column primary_column">
				<div class="cell primary_cell">
					<form action="./" class="form"
						id="login_form"
						method="post">
						<fieldset class="fieldset">
							<legend class="legend">Account Conduits</legend>
							<p class="button_control_container form_control_container"><button class="button conduit_button wide_button"
								tabindex="1" type="submit"><img alt="Twitter Logo" class="image" height="32" src="./assets/images/twitter/white_32px.png" width="39"> Twitter</button></p>
							<p class="button_control_container form_control_container"><button class="button conduit_button wide_button"
								tabindex="1" type="submit"><img alt="GitHub Logo" class="image" height="32" src="./assets/images/github/32px.png" width="32"> GitHub</button></p>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
		<script src="./assets/js/backbone-min.js"
			type="text/javascript"></script>
	</body>
</html>
