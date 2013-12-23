<!DOCTYPE HTML>
<html dir="ltr" lang="en-US">
	<head>
		<meta http-equiv="Content-Type"
			content="text/html;charset=utf-8">
		<meta name="viewport"
			content="initial-scale=1.0">
		<title>PHP Mentoring: Register&mdash;Step Two</title>
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
						id="register_form"
						method="post">
						<fieldset class="fieldset">
							<legend class="legend">Public Status</legend>
							<p>I am &mdash;</p>
							<div class="checkbox_control_pair form_control_pair">
								<input class="checkbox_control form_control"
									id="register_mentor_available" name="register_mentor_available"
									tabindex="1" type="checkbox">
								<label for="register_mentor_available">a mentor</label>, and
							</div>
							<div class="checkbox_control_pair form_control_pair">
								<input class="checkbox_control form_control"
									id="register_apprentice_available" name="register_apprentice_available"
									tabindex="1" type="checkbox">
								<label for="register_apprentice_available">an apprentice.</label>
							</div>
						</fieldset>
						<fieldset class="fieldset">
							<legend class="legend">Public Skills</legend>
							<p>I would like to (comma-separated)&mdash;</p>
							<div class="form_control_pair">
								<label for="register_teaching_skills">teach the following skills:</label>
								<div class="form_control_container textarea_control_container">
									<textarea class="form_control textarea_control"
										cols="70"
										id="register_teaching_skills" name="register_teaching_skills"
										placeholder="One, two three."
										rows="2"
										tabindex="1"></textarea>
								</div>
							</div>
							<div class="form_control_pair">
								<label for="register_learning_skills">learn the following skills:</label>
								<div class="form_control_container textarea_control_container">
									<textarea class="form_control textarea_control"
										cols="70"
										id="register_teaching_skills" name="register_teaching_skills"
										placeholder="Four, five, six."
										rows="2"
										tabindex="1"></textarea>
								</div>
							</div>
						</fieldset>
						<fieldset class="fieldset">
							<legend class="legend">Send</legend>
							<p><button class="button large_button"
								tabindex="1" type="submit">Register</button></p>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
		<script src="./assets/js/backbone-min.js"
			type="text/javascript"></script>
	</body>
</html>
