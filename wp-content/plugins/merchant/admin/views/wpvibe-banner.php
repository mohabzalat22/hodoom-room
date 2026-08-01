<?php
/**
 * WPVibe integration banner partial.
 *
 * Rendered on the Global Settings page. Shows an install prompt when WPVibe
 * is not active, and a connected state with the MCP toggle when it is.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpvibe_plugin_file  = 'vibe-ai/vibe-ai.php';
$wpvibe_is_active    = is_plugin_active( $wpvibe_plugin_file );
$wpvibe_settings_url = admin_url( 'admin.php?page=vibe-ai' );

$mcp_write_enabled = Merchant_Option::get( 'global-settings', 'wpvibe_mcp_write_access', false );
?>

<div class="merchant-wpvibe-banner">

	<div class="merchant-wpvibe-banner-content">

		<p class="merchant-wpvibe-banner-eyebrow">
			<?php esc_html_e( 'WordPress Abilities API + Merchant', 'merchant' ); ?>
		</p>

		<div class="merchant-wpvibe-banner-title">
			<?php esc_html_e( 'Use Merchant With Your Favorite AI', 'merchant' ); ?>
		</div>

		<p class="merchant-wpvibe-banner-desc">
			<?php
			$wpvibe_link = '<a href="' . esc_url( 'https://wpvibe.ai' ) . '" target="_blank" rel="noopener noreferrer">WPVibe.ai</a>';
			$docs_link = '<a href="' . esc_url( 'https://docs.athemes.com/article/abilities-in-merchant' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Abilities API Documentation', 'merchant' ) . '</a>';
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WPVibe.ai hyperlink */
					__( 'Connect your WordPress site and Merchant to AI assistants like Claude, ChatGPT, Cursor, and more. Ask them to find submissions, build forms, or edit fields in plain English. No copy-pasting, no exports. Connect them with the free %s plugin. %s', 'merchant' ),
					$wpvibe_link, 
					$docs_link
				)
			);
			?>
		</p>

		<div class="merchant-wpvibe-banner-actions">

			<?php if ( $wpvibe_is_active ) : ?>

				<a href="<?php echo esc_url( $wpvibe_settings_url ); ?>" class="merchant-wpvibe-active-link">
					<span class="merchant-wpvibe-active-check">&#10003;</span>
					<?php esc_html_e( 'WPVibe Active — Manage Settings', 'merchant' ); ?>
				</a>

			<?php else : ?>

				<button
					type="button"
					class="merchant-install-plugin merchant-wpvibe-install-btn"
					data-type="wporg"
					data-plugin-slug="vibe-ai"
					data-plugin-name="<?php echo esc_attr( $wpvibe_plugin_file ); ?>"
				>
					<?php esc_html_e( 'Install & Activate WPVibe', 'merchant' ); ?>
				</button>

			<?php endif; ?>

			<div class="merchant-wpvibe-toggle-wrap <?php echo $wpvibe_is_active ? '' : 'merchant-wpvibe-toggle-disabled'; ?>">
				<div class="merchant-module-page-setting-field-switcher">
				<?php if ( $wpvibe_is_active ) : ?>
					<?php
					Merchant_Field_Registry::instance()->create(
						'switcher',
						array(
							'id'      => 'wpvibe_mcp_write_access',
							'type'    => 'switcher',
							'default' => false,
						),
						$mcp_write_enabled ? 1 : 0,
						'global-settings'
					)->render();
					?>
				<?php else : ?>
					<div class="merchant-toggle-switch">
						<input type="checkbox" id="wpvibe_mcp_write_access" value="1" <?php checked( $mcp_write_enabled, true ); ?> class="toggle-switch-checkbox" disabled/>
						<label class="toggle-switch-label" for="wpvibe_mcp_write_access">
							<span class="toggle-switch-inner"></span>
							<span class="toggle-switch-switch"></span>
						</label>
					</div>
				<?php endif; ?>
				</div>
				<span class="merchant-wpvibe-toggle-label">
					<?php esc_html_e( 'Enable MCP Write Access', 'merchant' ); ?>
				</span>
			</div>

		</div>

	</div>

	<div class="merchant-wpvibe-banner-badges">
		<div class="merchant-wpvibe-badge">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 4C0 1.79086 1.79086 0 4 0H20C22.2091 0 24 1.79086 24 4V20C24 22.2091 22.2091 24 20 24H4C1.79086 24 0 22.2091 0 20V4Z" fill="white"/><g clip-path="url(#clip0_claude)"><path d="M7.136 14.64L10.288 12.88L10.336 12.72L10.288 12.64H10.128L9.6 12.608L7.808 12.56L6.24 12.48L4.72 12.4L4.336 12.32L4 11.84L4.032 11.6L4.352 11.392L4.816 11.424L5.824 11.504L7.344 11.6L8.448 11.664L10.08 11.856H10.336L10.368 11.744L10.288 11.68L10.224 11.616L8.64 10.56L6.944 9.44L6.048 8.784L5.568 8.464L5.328 8.144L5.232 7.472L5.664 6.992L6.256 7.04L6.4 7.072L6.992 7.536L8.272 8.512L9.92 9.76L10.16 9.952L10.256 9.888L10.272 9.84L10.16 9.664L9.28 8L8.32 6.336L7.888 5.648L7.776 5.232C7.728 5.072 7.712 4.912 7.712 4.752L8.192 4.08L8.48 4L9.152 4.096L9.408 4.32L9.824 5.28L10.48 6.768L11.52 8.784L11.84 9.392L12 9.936L12.048 10.096H12.16V10.016L12.24 8.864L12.4 7.472L12.56 5.68L12.608 5.168L12.864 4.56L13.344 4.24L13.76 4.416L14.08 4.88L14.032 5.168L13.856 6.4L13.44 8.336L13.2 9.648H13.344L13.504 9.472L14.16 8.608L15.264 7.232L15.744 6.672L16.32 6.08L16.688 5.792H17.376L17.872 6.544L17.648 7.328L16.944 8.224L16.352 8.976L15.504 10.112L14.992 11.024L15.04 11.088H15.152L17.072 10.672L18.096 10.496L19.312 10.288L19.872 10.544L19.936 10.8L19.712 11.344L18.4 11.664L16.864 11.984L14.576 12.512L14.544 12.528L14.576 12.576L15.6 12.672L16.048 12.704H17.136L19.152 12.864L19.68 13.184L19.984 13.616L19.936 13.936L19.12 14.352L18.032 14.096L15.472 13.488L14.608 13.28H14.48V13.344L15.216 14.064L16.544 15.264L18.24 16.816L18.32 17.2L18.112 17.52L17.888 17.488L16.416 16.368L15.84 15.888L14.56 14.8H14.48V14.912L14.768 15.344L16.336 17.696L16.416 18.416L16.304 18.64L15.888 18.8L15.456 18.704L14.528 17.424L13.568 15.984L12.816 14.672L12.736 14.736L12.272 19.568L12.064 19.808L11.584 20L11.184 19.68L10.96 19.2L11.184 18.208L11.44 16.928L11.648 15.904L11.84 14.64L11.952 14.224V14.192H11.84L10.88 15.52L9.44 17.488L8.288 18.704L8.016 18.816L7.536 18.576L7.584 18.128L7.84 17.76L9.44 15.712L10.4 14.448L11.04 13.712L11.024 13.632H10.976L6.752 16.384L6 16.48L5.68 16.16L5.712 15.68L5.872 15.52L7.152 14.64H7.136Z" fill="#D97757"/></g><defs><clipPath id="clip0_claude"><rect width="16" height="16" fill="white" transform="translate(4 4)"/></clipPath></defs></svg>
			<span><?php esc_html_e( 'Claude', 'merchant' ); ?></span>
		</div>
		<div class="merchant-wpvibe-badge">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M0 4C0 1.79086 1.79086 0 4 0H20C22.2091 0 24 1.79086 24 4V20C24 22.2091 22.2091 24 20 24H4C1.79086 24 0 22.2091 0 20V4Z" fill="white"/>
				<path d="M18.8527 10.5483C18.9879 10.1422 19.0568 9.71707 19.0569 9.28914C19.0568 8.58103 18.8682 7.8857 18.5104 7.27464C17.7915 6.02316 16.4578 5.25052 15.0143 5.25052C14.7304 5.25052 14.4459 5.28052 14.1683 5.34011C13.7943 4.91874 13.3353 4.58144 12.8214 4.35044C12.3075 4.11943 11.7505 3.99999 11.1871 3.99997H11.1618C11.1593 4.00006 11.1553 4.00006 11.1524 4.00006C9.40413 4.00006 7.8538 5.12812 7.31634 6.79119C6.1909 7.02128 5.21936 7.72708 4.65133 8.72488C4.29464 9.33965 4.10667 10.0377 4.10645 10.7485C4.10659 11.7473 4.47738 12.7106 5.14703 13.4517C5.01185 13.8578 4.94291 14.2829 4.94286 14.7108C4.94292 15.4189 5.13152 16.1143 5.48928 16.7253C6.20816 17.9772 7.54198 18.7494 8.98555 18.7494C9.26977 18.7494 9.5532 18.7194 9.8311 18.6598C10.2051 19.0812 10.6642 19.4185 11.1782 19.6495C11.6921 19.8805 12.2491 19.9999 12.8125 20H12.8378L12.8481 19.9999C14.5973 19.9999 16.1471 18.8719 16.6846 17.2073C17.81 16.9771 18.7816 16.2713 19.3496 15.2735C19.7059 14.6593 19.8935 13.9618 19.8934 13.2517C19.8933 12.2529 19.5225 11.2896 18.8529 10.5485L18.8527 10.5483ZM12.8389 18.9539H12.8348C12.1348 18.9537 11.4571 18.7082 10.9195 18.26C10.9514 18.2429 10.983 18.225 11.0141 18.2064L14.1999 16.3662C14.2794 16.321 14.3455 16.2555 14.3915 16.1764C14.4375 16.0973 14.4617 16.0075 14.4617 15.916V11.4216L15.8083 12.1992C15.8226 12.2063 15.8324 12.2202 15.8345 12.236V15.9556C15.8326 17.6092 14.4926 18.9506 12.8389 18.9539ZM6.39666 16.2025C6.13352 15.7475 5.99489 15.2312 5.99472 14.7056C5.99472 14.5342 6.0097 14.3623 6.03886 14.1934C6.06253 14.2076 6.10386 14.2328 6.1335 14.2498L9.31928 16.09C9.39873 16.1363 9.48905 16.1607 9.581 16.1607C9.67296 16.1607 9.76326 16.1363 9.84269 16.0899L13.7322 13.8441V15.3992C13.7326 15.4071 13.7311 15.415 13.7277 15.4222C13.7244 15.4294 13.7193 15.4357 13.7129 15.4404L10.4925 17.2999C10.0367 17.5621 9.52021 17.7002 8.99443 17.7004C8.46811 17.7003 7.95107 17.5619 7.49513 17.299C7.0392 17.036 6.66039 16.6579 6.3967 16.2024L6.39666 16.2025ZM5.55858 9.24777C5.90846 8.64 6.46094 8.17464 7.11932 7.93314C7.11932 7.96059 7.11774 8.00916 7.11774 8.04293V11.7233L7.1177 11.7263C7.11771 11.8177 7.14191 11.9075 7.18784 11.9865C7.23377 12.0655 7.29978 12.1309 7.37918 12.1761L11.2687 14.4216L9.92214 15.1992C9.9155 15.2035 9.90787 15.2062 9.89995 15.2069C9.89202 15.2076 9.88405 15.2064 9.87673 15.2032L6.65595 13.3422C6.20063 13.0785 5.82262 12.6997 5.55982 12.2438C5.29702 11.788 5.15865 11.271 5.15858 10.7448C5.15879 10.2194 5.29678 9.7033 5.55875 9.2479L5.55858 9.24777ZM16.6217 11.8223L12.7322 9.57645L14.0788 8.79924C14.0854 8.79485 14.0931 8.79218 14.101 8.79146C14.1089 8.79074 14.1169 8.79199 14.1242 8.79511L17.345 10.6545C18.2721 11.1901 18.844 12.1805 18.844 13.2515C18.844 14.5077 18.0601 15.6318 16.8815 16.0656V12.2752C16.8817 12.2738 16.8817 12.2723 16.8817 12.271C16.8817 12.0857 16.7824 11.9144 16.6217 11.8223ZM17.962 9.80512C17.9383 9.79059 17.897 9.76569 17.8674 9.74865L14.6816 7.90846C14.6021 7.86216 14.5119 7.83775 14.4199 7.8377C14.328 7.8377 14.2377 7.86221 14.1583 7.90846L10.2688 10.1543V8.59924L10.2687 8.59656C10.2687 8.58906 10.2705 8.58166 10.2738 8.57495C10.2772 8.56824 10.2821 8.56241 10.2881 8.55791L13.5085 6.70006C13.9641 6.43744 14.4807 6.29918 15.0065 6.29914C16.6623 6.29914 18.0052 7.64201 18.0052 9.29788C18.0051 9.4678 17.9907 9.63741 17.962 9.8049V9.80512ZM9.53672 12.5767L8.18986 11.7992C8.18279 11.7957 8.1767 11.7905 8.17214 11.784C8.16757 11.7776 8.16466 11.7701 8.16368 11.7623V8.04284C8.16438 6.38781 9.50725 5.04609 11.1624 5.04609C11.8634 5.04624 12.5423 5.2918 13.0811 5.74016C13.0492 5.75743 13.0177 5.77532 12.9865 5.79383L9.80071 7.63397C9.72122 7.6792 9.65513 7.74467 9.60915 7.82372C9.56317 7.90277 9.53894 7.99259 9.53892 8.08404V8.08702L9.53672 12.5767ZM10.2682 10.9996L12.0005 9.99911L13.7328 10.999V12.9994L12.0005 13.9993L10.2682 12.9994V10.9996Z" fill="black"/>
			</svg>
			<span><?php esc_html_e( 'ChatGPT', 'merchant' ); ?></span>
		</div>
		<div class="merchant-wpvibe-badge">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 4C0 1.79086 1.79086 0 4 0H20C22.2091 0 24 1.79086 24 4V20C24 22.2091 22.2091 24 20 24H4C1.79086 24 0 22.2091 0 20V4Z" fill="white"/><g clip-path="url(#clip0_cursor)"><path d="M18.7376 7.78698L12.3324 4.08896C12.1267 3.97018 11.8729 3.97018 11.6672 4.08896L5.26232 7.78698C5.08941 7.88681 4.98267 8.07144 4.98267 8.27141V15.7285C4.98267 15.9285 5.08941 16.1131 5.26232 16.2129L11.6676 19.9109C11.8732 20.0297 12.127 20.0297 12.3327 19.9109L18.7379 16.2129C18.9108 16.1131 19.0176 15.9285 19.0176 15.7285V8.27141C19.0176 8.07144 18.9108 7.88681 18.7379 7.78698H18.7376ZM18.3353 8.57031L12.152 19.2801C12.1102 19.3522 11.9998 19.3228 11.9998 19.2392V12.2265C11.9998 12.0864 11.925 11.9568 11.8035 11.8864L5.73051 8.38026C5.65834 8.33847 5.68781 8.22811 5.7714 8.22811H18.138C18.3136 8.22811 18.4234 8.41845 18.3356 8.57061H18.3353V8.57031Z" fill="#26251E"/></g><defs><clipPath id="clip0_cursor"><rect width="14.0346" height="16" fill="white" transform="translate(4.98267 4)"/></clipPath></defs></svg>
			<span><?php esc_html_e( 'Cursor', 'merchant' ); ?></span>
		</div>
		<p class="merchant-wpvibe-any-client">
			<?php esc_html_e( '+ Any MCP Client', 'merchant' ); ?>
		</p>
	</div>

</div>
