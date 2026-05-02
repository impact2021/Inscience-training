<?php
/**
 * Email templates admin view.
 *
 * @package InScience_Training
 * Variables: $templates, $edit_slug, $edit_template
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-email-alt"></span>
		<?php esc_html_e( 'Email Templates', 'inscience-training' ); ?>
	</h1>

	<div class="inscience-form-grid">
		<div class="inscience-form-main">
			<?php if ( $edit_template ) : ?>
			<!-- Edit form -->
			<div class="inscience-card">
				<h2><?php echo esc_html( $edit_template->label ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'You can use the following placeholders in the subject and body:', 'inscience-training' ); ?>
					<code>{given_names}</code>, <code>{last_name}</code>, <code>{course_title}</code>, <code>{course_date}</code>,
					<code>{course_type}</code>, <code>{course_location}</code>, <code>{enrolment_id}</code>,
					<code>{payment_method}</code>, <code>{payment_instructions}</code>, <code>{email}</code>,
					<code>{phone}</code>, <code>{employer}</code>, <code>{admin_url}</code>,
					<code>{enrol_url}</code>, <code>{unsubscribe_url}</code>, <code>{name}</code>.
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'inscience_save_email_template_action', 'inscience_nonce' ); ?>
					<input type="hidden" name="action" value="inscience_save_email_template">
					<input type="hidden" name="template_slug" value="<?php echo esc_attr( $edit_template->slug ); ?>">

					<p>
						<label for="template_subject"><strong><?php esc_html_e( 'Subject', 'inscience-training' ); ?></strong></label>
						<input type="text" id="template_subject" name="template_subject" class="widefat"
							value="<?php echo esc_attr( $edit_template->subject ); ?>">
					</p>

					<p>
						<label for="template_body"><strong><?php esc_html_e( 'Body', 'inscience-training' ); ?></strong></label>
						<textarea id="template_body" name="template_body" class="widefat" rows="16"><?php echo esc_textarea( $edit_template->body ); ?></textarea>
					</p>

					<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Template', 'inscience-training' ); ?></button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-emails' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'inscience-training' ); ?></a>
					</p>
				</form>
			</div>
			<?php else : ?>
			<!-- Template list -->
			<div class="inscience-card">
				<p><?php esc_html_e( 'Select a template below to customise the subject and body. Placeholders in curly braces will be replaced with real data when the email is sent.', 'inscience-training' ); ?></p>
				<table class="wp-list-table widefat fixed striped inscience-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Template', 'inscience-training' ); ?></th>
							<th><?php esc_html_e( 'Subject', 'inscience-training' ); ?></th>
							<th style="width:100px"><?php esc_html_e( 'Last Updated', 'inscience-training' ); ?></th>
							<th style="width:80px"><?php esc_html_e( 'Action', 'inscience-training' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $templates as $t ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $t->label ); ?></strong><br><small><?php echo esc_html( $t->slug ); ?></small></td>
						<td><?php echo esc_html( $t->subject ); ?></td>
						<td><?php echo esc_html( $t->updated_at ? gmdate( 'd M Y', strtotime( $t->updated_at ) ) : '—' ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-emails&edit=' . $t->slug ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'inscience-training' ); ?>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		</div>

		<div class="inscience-form-sidebar">
			<div class="inscience-card">
				<h2><?php esc_html_e( 'About Email Templates', 'inscience-training' ); ?></h2>
				<p><?php esc_html_e( 'These templates control all automated emails sent by the InScience Training plugin.', 'inscience-training' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Enrolment Confirmation — sent to attendee on sign-up.', 'inscience-training' ); ?></li>
					<li><?php esc_html_e( 'New Enrolment Notification — sent to admin on sign-up.', 'inscience-training' ); ?></li>
					<li><?php esc_html_e( 'Payment Received — sent to attendee when payment is confirmed.', 'inscience-training' ); ?></li>
					<li><?php esc_html_e( 'New Course Notification — sent to subscribers when a course is published.', 'inscience-training' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Configure your From name, From email address and logo in', 'inscience-training' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-settings' ) ); ?>"><?php esc_html_e( 'Settings', 'inscience-training' ); ?></a>.</p>
			</div>
		</div>
	</div>
</div>
