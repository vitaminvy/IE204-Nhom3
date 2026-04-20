<?php
/**
 * Template Name: Hộp Thư Mật
 * Template Post Type: page
 *
 * Secret mailbox / contact page styled as a detective intel depot.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contact_email  = get_theme_mod( 'cowm_contact_email', 'agent@comeoutwithme.com' );
$facebook_url   = get_theme_mod( 'cowm_contact_facebook', '#' );
$telegram_url   = get_theme_mod( 'cowm_contact_telegram', '#' );
$group_url      = get_theme_mod( 'cowm_contact_group', '#' );
$seal_image_url = get_theme_mod( 'cowm_contact_seal_image', '' );
$evidence_image = get_theme_mod( 'cowm_contact_evidence_image', '' );

$form_success = false;
$form_errors  = array();

// Handle form submission.
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cowm_contact_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cowm_contact_nonce'] ) ), 'cowm_contact_form' ) ) {
		$form_errors[] = __( 'Xác thực bảo mật thất bại. Vui lòng thử lại.', 'comeout-with-me' );
	} else {
		$sender_name  = isset( $_POST['cowm_sender_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cowm_sender_name'] ) ) : '';
		$sender_email = isset( $_POST['cowm_sender_email'] ) ? sanitize_email( wp_unslash( $_POST['cowm_sender_email'] ) ) : '';
		$category     = isset( $_POST['cowm_category'] ) ? sanitize_text_field( wp_unslash( $_POST['cowm_category'] ) ) : '';
		$message      = isset( $_POST['cowm_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cowm_message'] ) ) : '';

		if ( '' === $sender_name ) {
			$form_errors[] = __( 'Vui lòng nhập bí danh.', 'comeout-with-me' );
		}

		if ( '' === $sender_email || ! is_email( $sender_email ) ) {
			$form_errors[] = __( 'Vui lòng nhập email hợp lệ.', 'comeout-with-me' );
		}

		if ( '' === $message ) {
			$form_errors[] = __( 'Vui lòng nhập nội dung manh mối.', 'comeout-with-me' );
		}

		if ( empty( $form_errors ) ) {
			$admin_email = get_option( 'admin_email' );
			$subject     = sprintf(
				/* translators: 1: category, 2: sender name. */
				__( '[Hộp Thư Mật] %1$s — từ %2$s', 'comeout-with-me' ),
				$category ? $category : __( 'Báo cáo mới', 'comeout-with-me' ),
				$sender_name
			);

			$body = sprintf(
				"Bí danh: %1\$s\nEmail: %2\$s\nPhân loại: %3\$s\n\n%4\$s",
				$sender_name,
				$sender_email,
				$category ? $category : '—',
				$message
			);

			$headers = array(
				'Content-Type: text/plain; charset=UTF-8',
				'Reply-To: ' . $sender_name . ' <' . $sender_email . '>',
			);

			$sent = wp_mail( $admin_email, $subject, $body, $headers );

			if ( $sent ) {
				$form_success = true;
			} else {
				$form_errors[] = __( 'Gửi thất bại. Vui lòng thử lại sau.', 'comeout-with-me' );
			}
		}
	}
}
?>
<main id="primary" class="site-main contact-page">
	<!-- Decorative coffee stain marks -->
	<div class="contact-stain contact-stain--top" aria-hidden="true"></div>
	<div class="contact-stain contact-stain--bottom" aria-hidden="true"></div>

	<!-- Hero Section -->
	<section class="contact-hero">
		<div class="site-shell">
			<p class="section-eyebrow"><?php esc_html_e( 'Section: Communication Depot', 'comeout-with-me' ); ?></p>
			<h1 class="contact-hero__title">
				<?php esc_html_e( 'Trạm Tiếp Nhận Manh Mối', 'comeout-with-me' ); ?>
			</h1>
			<p class="contact-hero__description">
				<?php esc_html_e( 'Mọi thông tin bạn cung cấp sẽ được giữ bí mật tuyệt đối. Đặc vụ của chúng tôi sẽ phản hồi sớm nhất.', 'comeout-with-me' ); ?>
			</p>
		</div>
	</section>

	<!-- Main Content Grid -->
	<div class="site-shell contact-layout">
		<!-- Dossier Form -->
		<div class="contact-form-panel" id="contact-form">
			<header class="contact-form-panel__header">
				<div>
					<h2 class="contact-form-panel__title"><?php esc_html_e( 'Đơn Trình Báo Vụ Án', 'comeout-with-me' ); ?></h2>
					<p class="contact-form-panel__id"><?php esc_html_e( 'Form ID: CM-INF-048', 'comeout-with-me' ); ?></p>
				</div>
				<?php if ( $seal_image_url ) : ?>
					<img class="contact-form-panel__seal" src="<?php echo esc_url( $seal_image_url ); ?>" alt="<?php esc_attr_e( 'Bureau Seal', 'comeout-with-me' ); ?>" width="64" height="64" loading="lazy" />
				<?php endif; ?>
			</header>

			<?php if ( $form_success ) : ?>
				<div class="contact-alert contact-alert--success">
					<p><strong><?php esc_html_e( 'Manh mối đã được gửi thành công!', 'comeout-with-me' ); ?></strong></p>
					<p><?php esc_html_e( 'Đặc vụ sẽ xem xét và phản hồi qua kênh liên lạc bạn đã cung cấp.', 'comeout-with-me' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $form_errors ) ) : ?>
				<div class="contact-alert contact-alert--error">
					<?php foreach ( $form_errors as $error ) : ?>
						<p><?php echo esc_html( $error ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $form_success ) : ?>
			<form class="contact-form" method="post" action="#contact-form">
				<?php wp_nonce_field( 'cowm_contact_form', 'cowm_contact_nonce' ); ?>

				<div class="contact-form__row">
					<div class="contact-form__field">
						<label class="contact-form__label" for="cowm-sender-name"><?php esc_html_e( 'Bí danh / Tên đặc vụ', 'comeout-with-me' ); ?></label>
						<input class="contact-form__input" id="cowm-sender-name" name="cowm_sender_name" type="text" placeholder="<?php esc_attr_e( 'Nhập định danh...', 'comeout-with-me' ); ?>" required />
					</div>
					<div class="contact-form__field">
						<label class="contact-form__label" for="cowm-sender-email"><?php esc_html_e( 'Kênh liên lạc mật', 'comeout-with-me' ); ?></label>
						<input class="contact-form__input" id="cowm-sender-email" name="cowm_sender_email" type="email" placeholder="<?php esc_attr_e( 'example@encrypted.com', 'comeout-with-me' ); ?>" required />
					</div>
				</div>

				<div class="contact-form__field">
					<label class="contact-form__label" for="cowm-category"><?php esc_html_e( 'Phân loại hồ sơ', 'comeout-with-me' ); ?></label>
					<select class="contact-form__input contact-form__select" id="cowm-category" name="cowm_category">
						<option value="bao-cao-vu-an-moi"><?php esc_html_e( 'Báo cáo vụ án mới', 'comeout-with-me' ); ?></option>
						<option value="phat-hien-ke-ho"><?php esc_html_e( 'Phát hiện kẽ hở', 'comeout-with-me' ); ?></option>
						<option value="yeu-cau-phoi-hop"><?php esc_html_e( 'Yêu cầu phối hợp', 'comeout-with-me' ); ?></option>
						<option value="khac"><?php esc_html_e( 'Khác', 'comeout-with-me' ); ?></option>
					</select>
				</div>

				<div class="contact-form__field">
					<label class="contact-form__label" for="cowm-message"><?php esc_html_e( 'Chi tiết manh mối / Nội dung báo cáo', 'comeout-with-me' ); ?></label>
					<textarea class="contact-form__input contact-form__textarea" id="cowm-message" name="cowm_message" rows="6" placeholder="<?php esc_attr_e( 'Bắt đầu tường trình tại đây...', 'comeout-with-me' ); ?>" required></textarea>
				</div>

				<div class="contact-form__actions">
					<button class="contact-form__submit" type="submit">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
						<?php esc_html_e( 'Gửi Đi', 'comeout-with-me' ); ?>
					</button>
				</div>
			</form>
			<?php endif; ?>
		</div>

		<!-- Intelligence Sidebar -->
		<aside class="contact-sidebar">
			<!-- Intel Card -->
			<div class="contact-intel">
				<div class="contact-intel__inner">
					<h3 class="contact-intel__title">
						<svg class="contact-intel__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
						<?php esc_html_e( 'Kênh Tình Báo', 'comeout-with-me' ); ?>
					</h3>

					<div class="contact-notes">
						<!-- Sticky Note: Email -->
						<div class="contact-note contact-note--yellow">
							<span class="contact-note__label"><?php esc_html_e( 'Direct Wire:', 'comeout-with-me' ); ?></span>
							<a class="contact-note__value" href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
						</div>

						<!-- Sticky Note: Socials -->
						<div class="contact-note contact-note--pink">
							<span class="contact-note__label"><?php esc_html_e( 'Encrypted Socials:', 'comeout-with-me' ); ?></span>
							<ul class="contact-note__links">
								<li>
									<a href="<?php echo esc_url( $facebook_url ); ?>">
										<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
										<span><?php esc_html_e( 'Facebook Archive', 'comeout-with-me' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $telegram_url ); ?>">
										<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
										<span><?php esc_html_e( 'Telegram Signal', 'comeout-with-me' ); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $group_url ); ?>">
										<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
										<span><?php esc_html_e( 'Informant Group', 'comeout-with-me' ); ?></span>
									</a>
								</li>
							</ul>
						</div>

						<!-- Evidence Photo -->
						<?php if ( $evidence_image ) : ?>
						<div class="contact-evidence">
							<div class="contact-evidence__frame">
								<img class="contact-evidence__image" src="<?php echo esc_url( $evidence_image ); ?>" alt="<?php esc_attr_e( 'Bureau Archives', 'comeout-with-me' ); ?>" loading="lazy" />
								<p class="contact-evidence__caption"><?php esc_html_e( 'Evidence Item #902 — Archive Room', 'comeout-with-me' ); ?></p>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Confidential Status Badge -->
			<div class="contact-badge">
				<svg class="contact-badge__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6 10H6v-2h8v2zm4-4H6V10h12v2z"/></svg>
				<div>
					<p class="contact-badge__label"><?php esc_html_e( 'Confidential Status', 'comeout-with-me' ); ?></p>
					<p class="contact-badge__value"><?php esc_html_e( 'Encrypted / Read Only', 'comeout-with-me' ); ?></p>
				</div>
			</div>
		</aside>
	</div>
</main>
<?php
get_footer();
