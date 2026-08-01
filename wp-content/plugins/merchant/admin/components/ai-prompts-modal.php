<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div id="merchant-ai-prompts-modal" class="merchant-ai-prompts-modal-overlay">
	<div class="merchant-ai-prompts-modal-box">

		<button type="button" class="merchant-ai-prompts-modal-close" aria-label="<?php esc_attr_e( 'Close', 'merchant' ); ?>">
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1 1L13 13M13 1L1 13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
			</svg>
		</button>

		<div class="merchant-ai-prompts-modal-header">

			<h3 class="merchant-ai-prompts-modal-title">
				<span class="merchant-ai-prompts-modal-title-text"></span>
				<span class="merchant-ai-prompts-modal-badge">
					<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M8 1L10.5 5.5L15 8L10.5 10.5L8 15L5.5 10.5L1 8L5.5 5.5Z" fill="currentColor"/>
					</svg>
					<?php esc_html_e( 'AI', 'merchant' ); ?>
				</span>
			</h3>
			<p class="merchant-ai-prompts-modal-blurb"></p>

		</div>

		<div class="merchant-ai-prompts-modal-body">

			<h4 class="merchant-ai-prompts-modal-list-title"><?php esc_html_e( 'Prompt Examples', 'merchant' ); ?></h4>

			<div class="merchant-ai-prompts-modal-list"></div>

		</div>

		<div class="merchant-ai-prompts-modal-footer">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'merchant', 'section' => 'settings' ), 'admin.php' ) ); ?>">
				<?php esc_html_e( 'Manage your AI connection', 'merchant' ); ?>
			</a>
		</div>

	</div>
</div>
