<?php

use JKetelaar\Kiyoh\Kiyoh;
use JKetelaar\Kiyoh\Model\Company;
use JKetelaar\Kiyoh\Model\Review;

class cdk_model
{

    /**
     * The JJKetelaar Kiyoh instance.
     *
     * @var Kiyoh
     */
    protected $client;

    /**
     * The current company info.
     *
     * @var Company
     */
    private $company;

    /**
     * Holds kiyoh reviews
     *
     * @var Review[]
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
    public function __construct()
    {
        $this->errors = new WP_Error();
    }

    /**
     * Get a new \stdClass with data for Kiyoh.
     *
     * @return stdClass|void
     */
    public function get()
    {
        $this->set_company_data();
        if ($this->has_errors()) {
            return;
        }

        if (true || ! $this->set_or_get_transient()) {
            $this->set_kiyoh();
            $this->set_data();
        }

        $opt = $this->options_array();

        $kiyoh                = new stdClass();
        $kiyoh->total_score   = $opt['total_score'];
        $kiyoh->total_reviews = $opt['total_reviews'];
        $kiyoh->kiyoh_url     = $opt['kiyoh_url'];
        $kiyoh->reviews       = new stdClass();
        $kiyoh->reviews->pro  = $opt['reviews']['pro'];

        return $kiyoh;
    }

    /**
     * Hook for the api.
     */
    public function hook_api()
    {
        add_action('wp_ajax_fetch_kiyoh', [ $this, 'fetch' ]);
        add_action('wp_ajax_nopriv_fetch_kiyoh', [ $this, 'fetch' ]);
    }

    /**
     * Fetches the data for Rest.
     */
    public function fetch()
    {
        $this->set_company_data();
        $this->fetch_data();
    }

    /**
     * Sets the company data if it exists.
     *
     * @return void
     */
    protected function set_company_data(): void
    {
        $conn_code = get_theme_mod('cdelk_invite_key');

        if (false === $conn_code) {
            $this->errors->add(
                1,
                esc_html__('No connector code or company code supplied', 'casadelkiyoh'),
                [
                    'conn' => $conn_code,
                ]
            );
        }

        $this->conn_code    = $conn_code;
        $this->company_code = false;
    }

    /**
     * Start fetching data.
     *
     * @return void
     */
    private function fetch_data(): void
    {
        if ($this->has_errors()) {
            return;
        }

        if (! $this->set_or_get_transient()) {
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
    protected function set_data(): void
    {
        $kiyoh_name = strtolower(str_replace([' ', '.'], '_', $this->company->getLocationName()));

        $kiyoh_url = sprintf(
            'https://www.kiyoh.com/reviews/%s/%s?lang=nl',
            $this->company->getLocationId(),
            $kiyoh_name
        );

        update_option('cdelk_kiyoh_score', $this->company->getAverageRating());
        update_option('cdelk_kiyoh_reviews_count', $this->company->getNumberReviews());
        update_option('cdelk_kiyoh_url', $kiyoh_url);

        foreach ($this->reviews as $review) {
            if ($review->getComment()) {
                update_option('cdelk_kiyoh_last_pro', $review->getComment());
                break;
            }
        }
    }

    /**
     * Returns the options array.
     *
     * @return array
     */
    private function options_array(): array
    {
        return [
            'total_score'   => get_option('cdelk_kiyoh_score'),
            'total_reviews' => get_option('cdelk_kiyoh_reviews_count'),
            'kiyoh_url'     => get_option('cdelk_kiyoh_url'),
            'reviews'       => [
                'pro' => get_option('cdelk_kiyoh_last_pro'),
            ],
        ];
    }

    /**
     * Send out response as ajax.
     *
     * @return void
     */
    private function send_response(): void
    {
        wp_send_json_success($this->options_array());
    }

    /**
     * Either sets or gets the transient.
     *
     * @return mixed
     */
    protected function set_or_get_transient()
    {
        if (! ( $tran = get_transient('cdelk_kiyoh_fetch') )) {
            set_transient('cdelk_kiyoh_fetch', 1, 43200);
        }

        return $tran;
    }

    /**
     * Sets the kiyoh data.
     *
     * @return void
     */
    protected function set_kiyoh(): void
    {
        $this->client = new Kiyoh($this->conn_code, 1);

        $this->company = $this->client->getCompany();
        $this->reviews = $this->company->getReviews();
    }

    /**
     * Check for errors.
     *
     * @return bool
     */
    private function has_errors(): bool
    {
        if ($this->errors->has_errors()) {
            add_action('admin_notices', [ $this, 'add_error_to_admin' ]);
            return true;
        }

        return false;
    }

    /**
     * Adds errors to the admin backend.
     *
     * @return void
     */
    public function add_error_to_admin(): void
    {
        foreach ($this->errors->get_error_messages() as $message) { ?>
            <div class="notice notice-error is-dismissible">
                <p><?php print esc_html($message); ?></p>
            </div>
            <?php
        }
    }
}
