<?php
/**
 * Public enrolment form view.
 *
 * @package InScience_Training
 * Variables: $courses (array), $course_id (int, 0 = not pre-selected)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="inscience-enrolment-wrap">
	<form id="inscience-enrolment-form" class="inscience-form" novalidate>
		<?php wp_nonce_field( 'inscience_enrolment', 'inscience_enrolment_nonce', true, true ); ?>

		<!-- STEP 1: Course Selection -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Course Selection', 'inscience-training' ); ?></h3>

			<div class="inscience-field inscience-field-required">
				<label for="course_id"><?php esc_html_e( 'Course you are enrolling for', 'inscience-training' ); ?> <span class="required">*</span></label>
				<select id="course_id" name="course_id" required>
					<option value=""><?php esc_html_e( '— Select a course —', 'inscience-training' ); ?></option>
					<?php foreach ( $courses as $c ) :
						if ( 'cancelled' === $c['status'] ) continue;
						$label = '';
						if ( $c['type'] ) {
							$label .= strtoupper( $c['type'] ) . ' – ';
						}
						$label .= $c['title'];
						if ( $c['date'] ) {
							$label .= ' (' . gmdate( 'd M Y', strtotime( $c['date'] ) ) . ')';
						}
						if ( 'classroom' === $c['type'] && $c['city'] ) {
							$city = InScience_Course_CPT::NZ_CITIES[ $c['city'] ] ?? ucfirst( $c['city'] );
							$label .= ' – ' . $city;
						} elseif ( 'zoom' === $c['type'] ) {
							$label .= ' – Online';
						}
					?>
					<option value="<?php echo esc_attr( $c['id'] ); ?>"
						data-type="<?php echo esc_attr( $c['type'] ); ?>"
						<?php selected( $course_id, $c['id'] ); ?>
						<?php if ( 'full' === $c['status'] ) : ?>disabled<?php endif; ?>>
						<?php echo esc_html( $label ); ?>
						<?php if ( 'full' === $c['status'] ) : ?>(<?php esc_html_e( 'Full', 'inscience-training' ); ?>)<?php endif; ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="inscience-field inscience-field-required">
				<label><?php esc_html_e( 'This registration is for…', 'inscience-training' ); ?> <span class="required">*</span></label>
				<div class="inscience-radio-group">
					<label><input type="radio" name="enrolment_type" value="new" checked> <?php esc_html_e( 'A new course registration', 'inscience-training' ); ?></label>
					<label><input type="radio" name="enrolment_type" value="refresher"> <?php esc_html_e( 'A refresher course', 'inscience-training' ); ?></label>
				</div>
			</div>
		</section>

		<!-- STEP 2: Employer -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Employer Details', 'inscience-training' ); ?></h3>

			<div class="inscience-field">
				<label for="employer"><?php esc_html_e( 'Your current employer', 'inscience-training' ); ?></label>
				<input type="text" id="employer" name="employer" class="inscience-input">
			</div>

			<div class="inscience-field">
				<label for="branch"><?php esc_html_e( 'Which branch?', 'inscience-training' ); ?></label>
				<input type="text" id="branch" name="branch" class="inscience-input">
				<p class="inscience-help"><?php esc_html_e( 'If your current employer has different branches around the country, let us know which branch you are enquiring about.', 'inscience-training' ); ?></p>
			</div>

			<div class="inscience-field">
				<label for="group_email"><?php esc_html_e( "Group organiser's email address", 'inscience-training' ); ?></label>
				<input type="email" id="group_email" name="group_email" class="inscience-input">
				<p class="inscience-help"><?php esc_html_e( 'This is only required if you are attending as part of a group from the same company.', 'inscience-training' ); ?></p>
			</div>
		</section>

		<!-- STEP 3: Attendee Details -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Attendee Details', 'inscience-training' ); ?></h3>
			<p class="inscience-help"><?php esc_html_e( 'Please note that the name entered below will appear on your NZQA Record of Learning and certificates. Please bracket your preferred name if different, for use in your hard copy certificate. For example: Jonathan Dennis (Jono) Smith', 'inscience-training' ); ?></p>

			<div class="inscience-field-row">
				<div class="inscience-field inscience-field-required">
					<label for="given_names"><?php esc_html_e( 'Given name(s)', 'inscience-training' ); ?> <span class="required">*</span></label>
					<input type="text" id="given_names" name="given_names" required class="inscience-input">
					<p class="inscience-help"><?php esc_html_e( '(to match the existing NZQA records)', 'inscience-training' ); ?></p>
				</div>
				<div class="inscience-field inscience-field-required">
					<label for="last_name"><?php esc_html_e( 'Last name', 'inscience-training' ); ?> <span class="required">*</span></label>
					<input type="text" id="last_name" name="last_name" required class="inscience-input">
				</div>
			</div>

			<div class="inscience-field inscience-field-required">
				<label for="street_address"><?php esc_html_e( 'Permanent residential address', 'inscience-training' ); ?> <span class="required">*</span></label>
				<input type="text" id="street_address" name="street_address" required class="inscience-input" placeholder="<?php esc_attr_e( 'Street Address', 'inscience-training' ); ?>">
				<p class="inscience-help"><?php esc_html_e( 'This should be your permanent address, NOT your work address or a temporary address used while attending a teaching institution.', 'inscience-training' ); ?></p>
			</div>

			<div class="inscience-field-row">
				<div class="inscience-field inscience-field-required">
					<label for="city"><?php esc_html_e( 'City', 'inscience-training' ); ?> <span class="required">*</span></label>
					<input type="text" id="city" name="city" required class="inscience-input">
				</div>
				<div class="inscience-field inscience-field-required">
					<label for="postcode"><?php esc_html_e( 'Postcode', 'inscience-training' ); ?> <span class="required">*</span></label>
					<input type="text" id="postcode" name="postcode" required class="inscience-input" maxlength="10">
				</div>
			</div>

			<div class="inscience-field inscience-field-required">
				<label for="email"><?php esc_html_e( 'Email address of attendee', 'inscience-training' ); ?> <span class="required">*</span></label>
				<input type="email" id="email" name="email" required class="inscience-input">
			</div>

			<div class="inscience-field inscience-field-required">
				<label for="date_of_birth"><?php esc_html_e( 'Date of birth of attendee', 'inscience-training' ); ?> <span class="required">*</span></label>
				<input type="date" id="date_of_birth" name="date_of_birth" required class="inscience-input">
			</div>

			<div class="inscience-field inscience-field-required">
				<label for="phone"><?php esc_html_e( 'Contact number of attendee', 'inscience-training' ); ?> <span class="required">*</span></label>
				<input type="tel" id="phone" name="phone" required class="inscience-input">
			</div>
		</section>

		<!-- STEP 4: Ethnic Group -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Ethnic Group', 'inscience-training' ); ?> <span class="required">*</span></h3>
			<p class="inscience-help"><?php esc_html_e( 'Tick box(es) next to the ethnic group(s) you feel you belong to (for statistical purposes only).', 'inscience-training' ); ?></p>
			<div class="inscience-checkbox-group">
				<?php foreach ( InScience_Enrolment::ETHNIC_GROUPS as $value => $label ) : ?>
				<label class="inscience-checkbox-label">
					<input type="checkbox" name="ethnic_group[]" value="<?php echo esc_attr( $value ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
				<?php endforeach; ?>
			</div>
			<input type="hidden" id="ethnic_group_hidden" name="ethnic_group" value="">
		</section>

		<!-- STEP 5: Gender -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Attendee is…', 'inscience-training' ); ?> <span class="required">*</span></h3>
			<p class="inscience-help"><?php esc_html_e( 'Tick appropriate box (for statistical purposes only)', 'inscience-training' ); ?></p>
			<div class="inscience-radio-group">
				<label><input type="radio" name="gender" value="male" required> <?php esc_html_e( 'Male', 'inscience-training' ); ?></label>
				<label><input type="radio" name="gender" value="female"> <?php esc_html_e( 'Female', 'inscience-training' ); ?></label>
				<label><input type="radio" name="gender" value="rather_not_say"> <?php esc_html_e( 'Rather not say', 'inscience-training' ); ?></label>
			</div>
		</section>

		<!-- STEP 6: Payment -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Payment Method', 'inscience-training' ); ?> <span class="required">*</span></h3>
			<div class="inscience-radio-group inscience-payment-options">
				<label class="inscience-payment-option">
					<input type="radio" name="payment_method" value="stripe" required>
					<span class="inscience-payment-icon inscience-payment-icon-stripe">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4C2.89 4 2 4.89 2 6v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
					</span>
					<span><?php esc_html_e( 'Credit / Debit Card (Stripe)', 'inscience-training' ); ?></span>
				</label>
				<label class="inscience-payment-option">
					<input type="radio" name="payment_method" value="bank_transfer">
					<span class="inscience-payment-icon inscience-payment-icon-bank">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.5 1L2 6v2h19V6L11.5 1zM4 10v7h3v-7H4zm6 0v7h3v-7h-3zm6 0v7h3v-7h-3zm4 9H2v2h19v-2z"/></svg>
					</span>
					<span><?php esc_html_e( 'Bank Transfer', 'inscience-training' ); ?></span>
				</label>
				<label class="inscience-payment-option">
					<input type="radio" name="payment_method" value="on_account">
					<span class="inscience-payment-icon inscience-payment-icon-account">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
					</span>
					<span><?php esc_html_e( 'On Account (account holders only)', 'inscience-training' ); ?></span>
				</label>
			</div>
		</section>

		<!-- STEP 7: Declaration -->
		<section class="inscience-form-section">
			<h3><?php esc_html_e( 'Declaration', 'inscience-training' ); ?> <span class="required">*</span></h3>
			<div class="inscience-declaration-text">
				<p><?php esc_html_e( 'I declare that I agree to comply with the Code of Practice of InScience Ltd with regard to this training and I note that fluent written and spoken English are required for entry to InScience Ltd Courses.', 'inscience-training' ); ?></p>
				<p><?php esc_html_e( 'I understand that on submission of this enrolment form, an invoice will be issued for prior payment (unless you are an account holder).', 'inscience-training' ); ?></p>
				<p id="inscience-zoom-declaration" style="display:none"><?php esc_html_e( 'If enrolling in a Zoom course, I agree to comply with the requirement for a video and audio connection, with video on at all times.', 'inscience-training' ); ?></p>
			</div>
			<label class="inscience-checkbox-label inscience-declaration-check">
				<input type="checkbox" id="declaration" name="declaration" value="1" required>
				<?php esc_html_e( 'I agree to the above declaration.', 'inscience-training' ); ?>
				<span class="required">*</span>
			</label>
		</section>

		<div class="inscience-form-submit">
			<div id="inscience-form-error" class="inscience-notice inscience-error" style="display:none"></div>
			<button type="submit" id="inscience-submit-btn" class="inscience-btn inscience-btn-submit">
				<span class="inscience-btn-text"><?php esc_html_e( 'Submit Enrolment', 'inscience-training' ); ?></span>
				<span class="inscience-btn-loading" style="display:none"><?php esc_html_e( 'Processing…', 'inscience-training' ); ?></span>
			</button>
			<p class="inscience-help"><?php esc_html_e( '* Required fields', 'inscience-training' ); ?></p>
		</div>
	</form>
</div>
