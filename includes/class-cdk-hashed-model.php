<?php

class cdk_hashed_model extends cdk_model
{
    const KIYOHFEEDURL = 'https://www.kiyoh.com/v1/review/feed.xml?hash=';
    const KIYOHURL = 'https://www.kiyoh.com/reviews/%s/%s?lang=nl';

    /** @var array */
    private $company;

    protected $hash;

    protected function set_company_data(): void
    {
        $hash = get_theme_mod('cdelk_hash_code');

        if (false === $hash) {
            $this->errors->add(
                1,
                esc_html__('No Kiyoh hash supplied', 'casadelkiyoh'),
                [
                    'hash'       => $hash
                ]
            );
        }

        $this->hash = $hash;
    }

    public function set_kiyoh(): void
    {
        try {
            $this->client = new GuzzleHttp\Client();
        } catch (Exception $e) {
            wp_die($e->getMessage());
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->add_company_parse_error();
        }
        
        $res = $this->client->request('GET', self::KIYOHFEEDURL . $this->hash);
        $body = $res->getBody()->getContents();
        $this->parse_company($body);
    }
    
    private function parse_company($xml): void
    {
        try {
            $body = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            $this->add_company_parse_error();
        }

        
        $this->company['total_score'] = (string) $body->averageRating;
        $this->company['total_reviews'] = (string) $body->numberReviews;
        $this->company['company_url'] = sprintf(self::KIYOHURL, (string) $body->locationId, str_replace(' ', '_', strtolower((string) $body->locationName)));
        
        foreach ( $body->reviews as $review ) {
            foreach ($review->reviews[0]->reviewContent as $reviewContent) {
                foreach ($reviewContent as $content) {
                    if ((string) $content->questionGroup === 'DEFAULT_OPINION' ) {
                        update_option('cdelk_kiyoh_last_pro', (string) $content->rating);
                        break 2;
                    }
                }
            }
		}
    }

    protected function set_data(): void
    {
        update_option('cdelk_kiyoh_score', $this->company['total_score']);
        update_option('cdelk_kiyoh_reviews_count', $this->company['total_reviews']);
        update_option('cdelk_kiyoh_url', $this->company['company_url']);
    }

    private function add_company_parse_error(): void
    {
        $this->errors->add(
            1,
            esc_html__('Body parsing failed', 'casadelkiyoh'),
            []
        );
    }
}
