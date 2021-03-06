<?php

class cdk_options
{
    /**
     * cdk_options constructor.
     */
    public function __construct()
    {
        Kirki::add_config(
            'cdelk',
            [
                'capability'  => 'edit_theme_options',
                'option_type' => 'theme_mod',
            ]
        );

        $this->register_panel();
        $this->register_section();
        $this->register_controls();
    }

    /**
     * Registers the panel in the customizer
     */
    private function register_panel(): void
    {
        Kirki::add_panel(
            'cdelk_settings_main_panel',
            [
                'priority'    => 160,
                'title'       => esc_html__('Kiyoh Settings', 'casadelkiyoh'),
                'description' => esc_html__('These settings make sure you get the most out of the CasadelKiyoh plugin', 'casadelkiyoh'),
            ]
        );
    }

    /**
     * Register the section for the customizer.
     *
     * @return void
     */
    private function register_section(): void
    {
        Kirki::add_section(
            'cdelk_settings_main_section',
            [
                'title'       => esc_html__('Kiyoh Settings', 'casadelkiyoh'),
                'description' => esc_html__('Fill in the connector code to use this plugin', 'casadelkiyoh'),
                'panel'       => 'cdelk_settings_main_panel',
                'priority'    => 10,
            ]
        );

        Kirki::add_section(
            'cdelk_settings_deprecated_section',
            [
                'title' => esc_html__('Deprecated Kiyoh Settings', 'casadelkiyoh'),
                'description' => esc_html__('These settings are for the older variants of kiyoh reviews', 'casadelkiyoh'),
                'panel' => 'cdelk_settings_main_panel',
                'priority' => 40
            ]
        );
    }

    /**
     * Register the controls for the customizer.
     */
    private function register_controls(): void
    {
        Kirki::add_field(
            'cdelk',
            [
                'type' => 'text',
                'settings' => 'cdelk_invite_key',
                'label' => esc_html__('Klantenvertellen publicatie hash key', 'casadelkiyoh'),
                'section' => 'cdelk_settings_main_section'
            ]
        );

        /** Deprecated values */
        Kirki::add_field(
            'cdelk',
            [
                'type'          => 'text',
                'settings'      => 'cdelk_hash_code',
                'label'         => esc_html__('Kiyoh/Klantenvertellen Hash code', 'casadelkiyoh'),
                'section'       => 'cdelk_settings_deprecated_section'
            ]
        );

        Kirki::add_field(
            'cdelk',
            [
                'type'          => 'switch',
                'settings'      => 'cdelk_use_hash',
                'label'         => esc_html__('Klantenvertellen hash gebruiken', 'casadelkiyoh'),
                'section'       => 'cdelk_settings_deprecated_section',
                'default'       => '0',
            ]
        );

        Kirki::add_field(
            'cdelk',
            [
                'type'     => 'text',
                'settings' => 'cdelk_conn_code',
                'label'    => esc_html__('Kiyoh Connector Code', 'casadelkiyoh'),
                'section'  => 'cdelk_settings_deprecated_section',
                'priority' => 20,
            ]
        );

        Kirki::add_field(
            'cdelk',
            [
                'type'     => 'text',
                'settings' => 'cdelk_comp_code',
                'label'    => esc_html__('Kiyoh Company Code', 'casadelkiyoh'),
                'section'  => 'cdelk_settings_deprecated_section',
                'priority' => 20,
            ]
        );
    }
}
