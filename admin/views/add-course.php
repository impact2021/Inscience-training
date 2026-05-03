<?php
/**
 * Add / Edit Course admin view.
 *
 * @package InScience_Training
 * Variables: $course_id, $course, $meta
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$editing = ! empty( $course );
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-plus-alt2"></span>
		<?php echo $editing ? esc_html__( 'Edit Course', 'inscience-training' ) : esc_html__( 'Add New Course', 'inscience-training' ); ?>
	</h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="inscience-form">
		<?php wp_nonce_field( 'inscience_save_course_action', 'inscience_nonce' ); ?>
		<input type="hidden" name="action" value="inscience_save_course">
		<?php if ( $editing ) : ?>
		<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>">
		<?php endif; ?>

		<div class="inscience-form-grid">
			<!-- Left column -->
			<div class="inscience-form-main">
				<div class="inscience-card">
					<h2><?php esc_html_e( 'Course Information', 'inscience-training' ); ?></h2>

					<p>
						<label for="course_title"><strong><?php esc_html_e( 'Course Title', 'inscience-training' ); ?> <span class="required">*</span></strong></label>
						<input type="text" id="course_title" name="course_title" class="widefat" required
							value="<?php echo esc_attr( $editing ? $course->post_title : '' ); ?>">
					</p>

					<p>
						<label for="course_us_codes"><strong><?php esc_html_e( 'Unit Standard Codes (e.g. US32327 &amp; 32328)', 'inscience-training' ); ?></strong></label>
						<input type="text" id="course_us_codes" name="course_us_codes" class="widefat"
							value="<?php echo esc_attr( $meta['course_us_codes'] ?? '' ); ?>">
					</p>

					<p>
						<label for="course_description"><strong><?php esc_html_e( 'Course Description', 'inscience-training' ); ?></strong></label>
						<textarea id="course_description" name="course_description" class="widefat" rows="6"><?php echo esc_textarea( $editing ? $course->post_content : '' ); ?></textarea>
					</p>
				</div>

				<div class="inscience-card">
					<h2><?php esc_html_e( 'Schedule', 'inscience-training' ); ?></h2>

					<div class="inscience-two-col">
						<p>
							<label for="course_date"><strong><?php esc_html_e( 'Start Date', 'inscience-training' ); ?> <span class="required">*</span></strong></label>
							<input type="date" id="course_date" name="course_date" required
								value="<?php echo esc_attr( $meta['course_date'] ?? '' ); ?>">
						</p>
						<p>
							<label for="course_end_date"><strong><?php esc_html_e( 'End Date', 'inscience-training' ); ?></strong></label>
							<input type="date" id="course_end_date" name="course_end_date"
								value="<?php echo esc_attr( $meta['course_end_date'] ?? '' ); ?>">
						</p>
					</div>

					<div class="inscience-two-col">
						<p>
							<label for="course_start_time"><strong><?php esc_html_e( 'Start Time', 'inscience-training' ); ?></strong></label>
							<input type="time" id="course_start_time" name="course_start_time"
								value="<?php echo esc_attr( $meta['course_start_time'] ?? '' ); ?>">
						</p>
						<p>
							<label for="course_end_time"><strong><?php esc_html_e( 'End Time', 'inscience-training' ); ?></strong></label>
							<input type="time" id="course_end_time" name="course_end_time"
								value="<?php echo esc_attr( $meta['course_end_time'] ?? '' ); ?>">
						</p>
					</div>
				</div>
			</div>

			<!-- Right column -->
			<div class="inscience-form-sidebar">
				<div class="inscience-card">
					<h2><?php esc_html_e( 'Publish', 'inscience-training' ); ?></h2>
					<p>
						<label>
							<input type="checkbox" name="course_published" value="on"
								<?php checked( ! $editing || 'publish' === $course->post_status ); ?>>
							<?php esc_html_e( 'Publish this course (visible on calendar)', 'inscience-training' ); ?>
						</label>
					</p>
					<p>
						<label for="course_status"><strong><?php esc_html_e( 'Enrolment Status', 'inscience-training' ); ?></strong></label>
						<select id="course_status" name="course_status">
							<option value="open" <?php selected( $meta['course_status'] ?? 'open', 'open' ); ?>><?php esc_html_e( 'Open', 'inscience-training' ); ?></option>
							<option value="full" <?php selected( $meta['course_status'] ?? '', 'full' ); ?>><?php esc_html_e( 'Full', 'inscience-training' ); ?></option>
							<option value="cancelled" <?php selected( $meta['course_status'] ?? '', 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'inscience-training' ); ?></option>
						</select>
					</p>
					<p>
						<button type="submit" class="button button-primary button-large">
							<?php echo $editing ? esc_html__( 'Update Course', 'inscience-training' ) : esc_html__( 'Add Course', 'inscience-training' ); ?>
						</button>
					</p>
					<?php if ( $editing ) : ?>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-courses' ) ); ?>" class="button">
							<?php esc_html_e( '← Back to Courses', 'inscience-training' ); ?>
						</a>
					</p>
					<?php endif; ?>
				</div>

				<div class="inscience-card">
					<h2><?php esc_html_e( 'Course Type &amp; Location', 'inscience-training' ); ?></h2>

					<p>
						<label for="course_type"><strong><?php esc_html_e( 'Delivery Method', 'inscience-training' ); ?> <span class="required">*</span></strong></label>
						<select id="course_type" name="course_type" required onchange="inscienceToggleCity(this.value)">
							<option value=""><?php esc_html_e( '— Select —', 'inscience-training' ); ?></option>
							<?php foreach ( InScience_Course_CPT::TYPES as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $meta['course_type'] ?? '', $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<div id="inscience-city-wrap" style="<?php echo ( ( $meta['course_type'] ?? '' ) !== 'classroom' ) ? 'display:none' : ''; ?>">
						<p>
							<label for="course_city"><strong><?php esc_html_e( 'City', 'inscience-training' ); ?></strong></label>
							<select id="course_city" name="course_city">
								<option value=""><?php esc_html_e( '— Select —', 'inscience-training' ); ?></option>
								<?php foreach ( InScience_Course_CPT::NZ_CITIES as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $meta['course_city'] ?? '', $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
					</div>

					<p>
						<label for="course_location"><strong><?php esc_html_e( 'Venue / Location Details', 'inscience-training' ); ?></strong></label>
						<input type="text" id="course_location" name="course_location" class="widefat"
							placeholder="<?php esc_attr_e( 'e.g. Auckland Training Centre, 123 Main St', 'inscience-training' ); ?>"
							value="<?php echo esc_attr( $meta['course_location'] ?? '' ); ?>">
					</p>
				</div>

				<div class="inscience-card">
					<h2><?php esc_html_e( 'Capacity &amp; Pricing', 'inscience-training' ); ?></h2>

					<p>
						<label for="course_capacity"><strong><?php esc_html_e( 'Max Capacity', 'inscience-training' ); ?></strong></label>
						<input type="number" id="course_capacity" name="course_capacity" min="1" class="small-text"
							value="<?php echo esc_attr( $meta['course_capacity'] ?? '' ); ?>">
					</p>

					<p>
						<label for="course_price"><strong><?php esc_html_e( 'Price (NZD)', 'inscience-training' ); ?></strong></label>
						<input type="number" id="course_price" name="course_price" min="0" step="0.01" class="small-text"
							value="<?php echo esc_attr( $meta['course_price'] ?? '' ); ?>">
					</p>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
function inscienceToggleCity(type) {
	var wrap = document.getElementById('inscience-city-wrap');
	wrap.style.display = (type === 'classroom') ? '' : 'none';
}
</script>
