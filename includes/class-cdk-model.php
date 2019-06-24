<?php

use JKetelaar\Kiyoh\Kiyoh;
use JKetelaar\Kiyoh\Models\Company;
use JKetelaar\Kiyoh\Models\Review;

class cdk_model {

	/**
	 * @var Kiyoh
	 */
	private $client;

	/**
	 * The current company info.
	 *
	 * @var Company
	 */
	private $company;

	/**
	 * Holds kiyoh reviews
	 *
	 * @var Review
	 */
	private $reviews;

	/**
	 * The connector code.
	 *
	 * @var string
	 */
	private $conn_code;

	/**
	 * The company code.
	 *
	 * @var string
	 */
	private $company_code;

	/**
	 * ErrorBag
	 *
	 * @var WP_Error;
	 */
	public $errors;

	/**
	 * Cdk_model constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_fetch_kiyoh', [ $this, 'fetch' ] );
		add_action( 'wp_ajax_nopriv_fetch_kiyoh', [ $this, 'fetch' ] );
	}

	public function fetch() {
		$this->errors = new WP_Error();
		$this->set_company_data();
		$this->fetch_data();
	}

	/**
	 * Sets the company data if it exists.
	 *
	 * @return void
	 */
	private function set_company_data() : void {
		$conn_code = get_theme_mod( 'cdelk_conn_code' );
		$comp_code = get_theme_mod( 'cdelk_comp_code' );

		if ( false === $conn_code || false === $comp_code ) {
			$this->errors->add(
				1,
				esc_html__( 'No connector code or company code supplied', 'casadelkiyoh' ),
				[
					'conn' => $conn_code,
					'comp' => $comp_code,
				]
			);
		}

		$this->conn_code    = $conn_code;
		$this->company_code = $comp_code;
	}

	/**
	 * Start fetching data.
	 *
	 * @return void
	 */
	private function fetch_data(): void {
		if ( $this->has_errors() ) {
			return;
		}

		if ( ! $this->set_or_get_transient() ) {
			$this->set_kiyoh();
			$this->set_data();
		}

		$this->send_response();
	}

	/**
	 * Sets Kiyoh data.
	 *
	 * @return void
	 */
	private function set_data(): void {
		update_option( 'cdelk_kiyoh_score', $this->company->getTotalScore() );
		update_option( 'cdelk_kiyoh_reviews_count', $this->company->getTotalReviews() );
		update_option( 'cdelk_kiyoh_url', $this->company->getUrl() );

		foreach ( $this->reviews as $review ) {
			if ( $review->getPros() ) {
				update_option( 'cdelk_kiyoh_last_pro', $review->getPros() );
				break;
			}
		}
	}

	/**
	 * Send out response as ajax.
	 *
	 * @return void
	 */
	private function send_response(): void {
		wp_send_json_success(
			[
				'total_score'   => get_option( 'cdelk_kiyoh_score' ),
				'total_reviews' => get_option( 'cdelk_kiyoh_reviews_count' ),
				'kiyoh_url'     => get_option( 'cdelk_kiyoh_url' ),
				'reviews'       => [
					'pro' => get_option( 'cdelk_kiyoh_last_pro' ),
				],
			]
		);
	}

	/**
	 * Either sets or gets the transient.
	 *
	 * @return mixed
	 */
	private function set_or_get_transient() {
		if ( ! ( $tran = get_transient( 'cdelk_kiyoh_fetch' ) ) ) {
			set_transient( 'cdelk_kiyoh_fetch', 1, 43200 );
		}

		return $tran;
	}

	/**
	 * Sets the kiyoh data.
	 *
	 * @return void
	 */
	private function set_kiyoh() : void {
		$this->client = new Kiyoh(
			$this->conn_code,
			$this->company_code
		);

		$this->company = $this->client->getCompany();
		$this->reviews = $this->client->getReviews();
	}

	/**
	 * Check for errors.
	 *
	 * @return bool
	 */
	private function has_errors() : bool {
		if ( $this->errors->has_errors() ) {
			add_action( 'admin_notices', [ $this, 'add_error_to_admin' ] );
			return true;
		}

		return false;
	}

	/**
	 * Adds errors to the admin backend.
	 *
	 * @return void
	 */
	public function add_error_to_admin(): void {
		foreach ( $this->errors->get_error_messages() as $message ) { ?>
			<div class="notice notice-error is-dismissible">
				<p><?php print esc_html( $message ); ?></p>
			</div>
			<?php
		}
	}
}
