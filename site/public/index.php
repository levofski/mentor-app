<!DOCTYPE HTML>
<html dir="ltr" lang="en-US">
	<head>
		<meta http-equiv="Content-Type"
			content="text/html;charset=utf-8">
		<meta name="viewport"
			content="initial-scale=1.0">
		<title>PHP Mentoring</title>
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
						id="register_form"
						method="post">
						<fieldset class="fieldset">
							<legend class="legend">Credentials</legend>
							<div class="form_control_pair">
								<label class="label" for="register_name">Name:</label>
								<div class="form_control_container text_control_container">
									<input class="form_control text_control"
										id="register_name" name="register_name"
										placeholder="John Smith" required
										tabindex="1" type="text">
								</div>
							</div>
							<div class="form_control_pair">
								<label class="label" for="register_email">Email:</label>
								<div class="form_control_container text_control_container">
									<input class="form_control text_control"
										id="register_email" name="register_email"
										placeholder="null@null.invalid" required
										tabindex="1" type="email">
								</div>
							</div>
							<p><button class="button large_button"
								tabindex="1" type="submit">Send</button></p>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
		<script src="./assets/js/backbone-min.js"
			type="text/javascript"></script>
	</body>
</html>
