<?php
/**
 * The file contains the class the add the admin notice for black friday sale.
 *
 * @package    miniorange-saml-20-single-sign-on
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
 }

/**
 * Adds Black Friday Sale Admin Notice for miniOrange SAML SSO Plugins
 */
class Mo_Saml_Black_Friday_Sale {
	/**
	 * Black Friday sale end date
	 *
	 * @var time
	 */
	private $sale_black_friday_end_time;

	/**
	 * Cyber Monday sale end date
	 *
	 * @var time
	 */
	private $sale_cyber_monday_end_time;

	/**
	 * End of year sale end date
	 *
	 * @var time
	 */
	private $sale_end_of_year_sale_time;

	/**
	 * Sale end date
	 *
	 * @var time
	 */
	private $sale_end_time;
	/**
	 * Initializing Sale banner
	 */
	public function __construct() {
		$this->sale_black_friday_end_time = strtotime( '2025-11-30 23:59:59 ' . wp_timezone_string() );
		$this->sale_cyber_monday_end_time = strtotime( '2025-12-07 23:59:59 ' . wp_timezone_string() );
		$this->sale_end_of_year_sale_time = strtotime( '2026-01-07 23:59:59 ' . wp_timezone_string() );
		add_action( 'admin_notices', array( $this, 'display_black_friday_sale_notice' ) );
		add_action( 'wp_ajax_mo_saml_dismiss_black_friday_sale_notice', array( $this, 'dismiss_black_friday_sale_notice' ) );
	}

	/**
	 * Show sale banner
	 *
	 * @return [html]
	 */
	public function display_black_friday_sale_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( strtotime( current_time( 'mysql' ) ) < $this->sale_black_friday_end_time ) {
			$image_url = ( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/black-friday.png';
		} elseif ( strtotime( current_time( 'mysql' ) ) > $this->sale_black_friday_end_time && strtotime( current_time( 'mysql' ) ) < $this->sale_cyber_monday_end_time ) {
			$image_url = ( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/cyber-monday.png';
		} elseif ( strtotime( current_time( 'mysql' ) ) > $this->sale_cyber_monday_end_time && strtotime( current_time( 'mysql' ) ) < $this->sale_end_of_year_sale_time ) {
			$image_url = ( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/end-of-year.png';
		} else {
			return;
		}

		if ( get_option( 'mo_saml_black_friday_sale_notice_dismissed' ) ) {
			if ( get_option( 'mo_saml_black_friday_sale_notice_dismissed_time' ) && strtotime( current_time( 'mysql' ) ) > get_option( 'mo_saml_black_friday_sale_notice_dismissed_time' ) ) {
				delete_option( 'mo_saml_black_friday_sale_notice_dismissed' );
			}
			return;
		}
		?>        
		<div class="notice notice-info mo_saml_black_friday_sale_notice" style="background-image: url(<?php echo esc_url( ( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/bfcm-banner.png' ); ?>); background-size: cover; background-position: center;">
			<div class="mo_saml_black_friday_sale_content">
				<img src="<?php echo esc_url( $image_url ); ?>" 
					alt="Black Friday Sale - miniOrange" height="51px" width="245px"
					class="mo_saml_black_friday_sale_banner_image" style="padding-left: 15px; padding-top: 5px;"
>
				<div class="mo_saml_black_friday_sale_text">
					Save Up to <span class="mo_saml_black_friday_sale_highlight">40%</span> on SAML Single Sign-On Premium Plans
					<span class="mo_saml_black_friday_sale_sparkle"><img src="<?php echo esc_url( ( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/sparkle-bfcm.png' ); ?>" 
					alt="Sparkle" style=" 
					width: 22.97px; height: 22.97px; opacity: 1; top: 20.78px; left: 896px; text-shadow: 0 0 5px #7f7f7f;"></span>
				</div>
				<a href="https://plugins.miniorange.com/year-end-sale-saml?utm_source=plugin&utm_medium=ribbon&utm_campaign=saml-bfcm" 
					target="_blank" 
					class="mo_saml_black_friday_sale_button">
					Claim Now
				</a>
			</div>
			<a href="#" 
				class="mo_saml_black_friday_sale_close" 
				id="mo_saml_black_friday_sale_notice_dismiss">
				&times;
			</a>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#mo_saml_black_friday_sale_notice_dismiss').on('click', function(e) {
				e.preventDefault();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'mo_saml_dismiss_black_friday_sale_notice',
						nonce: '<?php echo esc_attr( wp_create_nonce( 'mo_saml_black_friday_sale_notice_nonce' ) ); ?>'
					},
					success: function(response) {
						$('.mo_saml_black_friday_sale_notice').fadeOut();
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Sale banner security
	 */
	public function dismiss_black_friday_sale_notice() {
		check_ajax_referer( 'mo_saml_black_friday_sale_notice_nonce', 'nonce' );

		update_option( 'mo_saml_black_friday_sale_notice_dismissed', true );
		$dismiss_time = strtotime( current_time( 'mysql' ) ) + 259200;
		update_option( 'mo_saml_black_friday_sale_notice_dismissed_time', $dismiss_time );
		wp_send_json_success();
	}
}

new Mo_Saml_Black_Friday_Sale();
?>
