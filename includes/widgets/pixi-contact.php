<?php
namespace Pixi_contactform;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class pixi_contactform extends \Elementor\Widget_Base {


	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 */
	public function get_name() {
		return 'pixi-cf7';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve  widget title.
	 */
	public function get_title() {
		return esc_html__( 'Pixi Contact Form', 'pixi-contactform' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve  widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the  widget belongs to.
	 *
	 */
	public function get_categories() {
		return [ 'pixi-form' ];
	}

    public function get_style_depends() {
		return [
			'pixi-contact',
		];
	}

    public function is_cf7_activated() {
        return class_exists( 'WPCF7' );
    }

    //  Main function
    public function get_cf7_forms() {
        $forms = [ '' => esc_html__( 'None', 'pixi-contactform' ) ];
        
        if ( $this->is_cf7_activated() ) {
            $cf7_forms = get_posts( [
                'post_type'      => 'wpcf7_contact_form',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ] );

            if ( ! empty( $cf7_forms ) ) {
                $forms = wp_list_pluck( $cf7_forms, 'post_title', 'ID' );
            }
        }
        return $forms;
    }



    protected function register_controls()
    {
        /**
         * Contact Form 7 General Settings
        */ 
        $this->start_controls_section(
            'pxel_cf7_general_settings',
            [
                'label'     => $this->is_cf7_activated() ? esc_html__( 'General Settings', 'pixi-contactform' ) : esc_html__( 'Missing Notice', 'pixi-contactform' ),
                'tab'       => Controls_Manager::TAB_CONTENT,
            ]
        );

        if ( ! $this->is_cf7_activated() ) {
            $this->add_control(
                'pxel_cf7_missing_notice',
                [
                    'type'  => Controls_Manager::RAW_HTML,
                    'raw'   => sprintf(
                        __( 'Hello, looks like %1$s is missing in your site. Please click on the link below and install/activate %1$s. Make sure to refresh this page after installation or activation.', 'pixi-contactform' ),
                        '<a href="'.esc_url( admin_url( 'plugin-install.php?s=Contact+Form+7&tab=search&type=term' ) )
                        .'" target="_blank" rel="noopener">Contact Form 7</a>'
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
                ]
            );

            $this->add_control(
                'pxel_cf7_install',
                [
                    'type'  => Controls_Manager::RAW_HTML,
                    'raw'   => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=Contact+Form+7&tab=search&type=term' ) ).'" target="_blank" rel="noopener">Click to install or activate Contact Form 7</a>',
                ]
            );
            $this->end_controls_section();
            return;
        }

        $this->add_control(
            'pxel_cf7_form_id',
            [
                'label'     => esc_html__( 'Select Your Form', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SELECT,
                'label_block' => true,
                'options'   => $this->get_cf7_forms(),
            ]
        );

        $this->add_control(
            'pxel_cf7_html_class',
            [
                'label'         => esc_html__( 'HTML Class', 'mega-elements-addons-for-elementor' ),
                'type'          => Controls_Manager::TEXT,
                'label_block'   => true,
                'description'   => esc_html__( 'Add "column-2" custom class to break the input fields.', 'mega-elements-addons-for-elementor' ),
            ]
        );

        $this->end_controls_section();

        /**
         * Contact Form 7 Form Fields Style
        */ 
        $this->start_controls_section(
            'pxel_cf7_input_style',
            [
                'label'     => esc_html__( 'Form Input', 'pixi-contactform' ),
                'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'pxel_cf7_input_width',
            [
                'label'     => esc_html__( 'Width', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SLIDER,
                'default'   => [
                    'unit' => '%',
                    'size' => '100',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => [ '%', 'px' ],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'pxel_cf7_input_height',
            [
                'label'     => esc_html__( 'Height', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SLIDER,
                'default'   => [
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => [  'px' ,'%'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'pxel_cf7_input_margin',
            [
                'label'     => esc_html__( 'Bottom Spacing', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'pxel_cf7_input_padding',
            [
                'label'     => esc_html__( 'Padding', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'pexl_cf7_input_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
			'textarea_heading',
			[
				'label' => esc_html__( 'Textarea', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_responsive_control(
            'pxel_cf7_textarea_height',
            [
                'label'     => esc_html__( 'Height', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SLIDER,
                'default'   => [
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => [ 'px','%' ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control.wpcf7-textarea' => 'height: {{SIZE}}{{UNIT}}',
                ],

            ]
        );


        $this->add_responsive_control(
            'pxel_cf7_textarea_padding',
            [
                'label'     => esc_html__( 'Padding', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control.wpcf7-textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );



        // Tabs start

        $this->start_controls_tabs( 'pxel_cf7_tabs_form_input' );

            $this->start_controls_tab(
                'pxel_cf7_tab_form_input_normal',
                [
                    'label'     => esc_html__( 'Normal', 'pixi-contactform' ),
                ]
            );

            $this->add_control(
                'pxel_cf7_input_text_color',
                [
                    'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'color: {{VALUE}}',
                    ],
                ]
            );
            $this->add_control(
                'pxel_cf7_input_bg_color',
                [
                    'label'     => esc_html__( 'Background', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'background-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name'      => 'pxel_cf7_input_border',
                    'selector'  => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name'      => 'pxel_cf7_input_box_shadow',
                    'selector'  => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
                ]
            );

        $this->end_controls_tab();

        // Hover
        $this->start_controls_tab(
            'pxel_cf7_tab_form_input_hover',
            [
                'label'     => esc_html__( 'Hover', 'pixi-contactform' ),
            ]
        );


            $this->add_control(
                'pxel_cf7_input_hover_bg_color',
                [
                    'label'     => esc_html__( 'Background', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):hover' => 'background-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'pxel_cf7_input_hover_text_color',
                [
                    'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):hover' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'pxel_cf7_input_hover_border_color',
                [
                    'label'     => esc_html__( 'Border Color', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):hover' => 'border-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name'      => 'pxel_cf7_input_hover_box_shadow',
                    'exclude'   => [
                        'box_shadow_position',
                    ],
                    'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):hover',
                ]
            );

        $this->end_controls_tab();


        // focus

        $this->start_controls_tab(
            'pxel_cf7_tab_form_input_focus',
            [
                'label'     => esc_html__( 'Focus', 'pixi-contactform' ),
            ]
        );


            $this->add_control(
                'pxel_cf7_input_focus_bg_color',
                [
                    'label'     => esc_html__( 'Background', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus' => 'background-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'pxel_cf7_input_focus_text_color',
                [
                    'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'pxel_cf7_input_focus_border_color',
                [
                    'label'     => esc_html__( 'Border Color', 'pixi-contactform' ),
                    'type'      =>  \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus' => 'border-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name'      => 'pxel_cf7_input_focus_box_shadow',
                    'exclude'   => [
                        'box_shadow_position',
                    ],
                    'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus',
                ]
            );


        $this->end_controls_tab();


        $this->end_controls_tabs();
       

        $this->end_controls_section();

        /**
         * Contact Form 7 Form Fields Label Style
        */
        $this->start_controls_section(
            'pxel_cf7_input_label_style',
            [
                'label'     => esc_html__( 'Label & Placeholder ', 'pixi-contactform' ),
                'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );



        $this->add_responsive_control(
            'pxel_cf7_label_margin',
            [
                'label'     => esc_html__( 'Label Bottom Spacing', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'      => 'pxel_cf7_label_typography',
                'label'     => esc_html__( 'Typography', 'pixi-contactform' ),
                'selector'  => '{{WRAPPER}} label',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pxel_cf7_label_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label' => 'color: {{VALUE}}',
                ],
            ]
        );


        $this->add_control(
			'placeholder_heading',
			[
				'label' => esc_html__( 'Placeholder', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'pxel_cf7_input_typography',
                'label'     => esc_html__( 'Typography', 'pixi-contactform' ),
                'selector'  => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
            ]
        );

        $this->add_control(
            'pxel_cf7_input_placeholder_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ::-webkit-input-placeholder' => 'color: {{VALUE}}',
                    '{{WRAPPER}} ::-moz-placeholder' => 'color: {{VALUE}}',
                    '{{WRAPPER}} ::-ms-input-placeholder' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * Contact Form 7 Submit Button Style
        */
        $this->start_controls_section(
            'pxel_cf7_btn_style',
            [
                'label'     => esc_html__( 'Submit Button', 'pixi-contactform' ),
                'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'pxel_cf7_btn_width',
            [
                'label'     => esc_html__( 'Button Width', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SLIDER,
                'default'   => [
                    'unit' => '%',
                    'size' => '100',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => [  'px' ,'%'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'bcssbs_cf7_btn_margin',
            [
                'label'     => esc_html__( 'Margin', 'pixi-contactform' ),
                'type'       =>  \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'bcssbs_cf7_btn_padding',
            [
                'label'     => esc_html__( 'Padding', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'      => 'pxel_cf7_btn_typography',
                'selector'  => '{{WRAPPER}} .wpcf7-submit',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'      => 'pxel_cf7_btn_border',
                'selector'  => '{{WRAPPER}} .wpcf7-submit',
            ]
        );

        $this->add_control(
            'bcssbs_cf7_btn_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'pxel_cf7_btn_box_shadow',
                'selector'  => '{{WRAPPER}} .wpcf7-submit',
                'separator' => 'after',
            ]
        );


        $this->start_controls_tabs( 'pxel_cf7_tabs_btn_style' );

        $this->start_controls_tab(
            'pxel_cf7_tab_btn_normal',
            [
                'label'     => esc_html__( 'Normal', 'pixi-contactform' ),
            ]
        );

        $this->add_control(
            'pxel_cf7_btn_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pxel_cf7_btn_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'bcssbs_cf7_tab_btn_hover',
            [
                'label'     => esc_html__( 'Hover', 'pixi-contactform' ),
            ]
        );

        $this->add_control(
            'pxel_cf7_btn_hover_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pxel_cf7_btn_hover_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pxel_cf7_btn_hover_border_color',
            [
                'label' => esc_html__( 'Border Color', 'pixi-contactform' ),
                'type' =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! $this->is_cf7_activated() ) {
            return;
        }

        $settings = $this->get_settings_for_display();

        if ( ! empty( $settings['pxel_cf7_form_id'] ) ) {
            echo $this->do_shortcode( 
                'contact-form-7', [ 'id' => $settings['pxel_cf7_form_id'] ,
                'html_class' => 'pixi-cf7-form ' . $this->sanitize_html_class_param( $settings['pxel_cf7_html_class'] ),
            ] );
            
        }
    }


        // Do shortcode
        public function do_shortcode( $tag, array $atts = array(), $content = null ) {
            global $shortcode_tags;
            if ( ! isset( $shortcode_tags[ $tag ] ) ) {
                return false;
            }
            return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
        }
    
        // Sanitize code
        public function sanitize_html_class_param( $class ) {
            $classes = ! empty( $class ) ? explode( ' ', $class ) : [];
            $sanitized = [];
            if ( ! empty( $classes ) ) {
                $sanitized = array_map( function( $cls ) {
                    return sanitize_html_class( $cls );
                }, $classes );
            }
            return implode( ' ', $sanitized );
        }

}
