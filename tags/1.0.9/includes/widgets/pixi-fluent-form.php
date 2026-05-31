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

class pixi_fluent_form extends Widget_Base {


	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 */
	public function get_name() {
		return 'pixi-fluent-form';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve  widget title.
	 */
	public function get_title() {
		return esc_html__( 'Pixi Fluent Form', 'pixi-contactform' );
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

    // public function get_style_depends() {
	// 	return [
	// 		'pixi-contact-fluent',
	// 	];
	// }

    public function is_form_activated() {
        return defined( 'FLUENTFORM' );
    }

     /*
    * Get a list of all Fluent Forms
    *
    * @return array
    */
    public function get_fluent_forms() {
        $forms = [ '' => esc_html__( 'None', 'pixi-contactform' ) ];
        
        if ( $this->is_form_activated() ) {

            global $wpdb;

            $table = $wpdb->prefix . 'fluentform_forms';
            $query = "SELECT * FROM {$table}";
            $fluent_forms = $wpdb->get_results( $query );

            if ( $fluent_forms ) {
                foreach( $fluent_forms as $form ) {
                    $forms[ $form->id ] = $form->title;
                }
            }
        }
        return $forms;
    }
    

    protected function register_controls()
    {
        /**
         * Fluent Form General Settings
        */ 
        $this->start_controls_section(
            'pxel_fluent_general_settings',
            [
                'label'     => $this->is_form_activated() ? esc_html__( 'General Settings', 'pixi-contactform' ) : esc_html__( 'Missing Notice', 'pixi-contactform' ),
                'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        if ( ! $this->is_form_activated() ) {
            $this->add_control(
                'pxel_fluent_missing_notice',
                [
                    'type'  => \Elementor\Controls_Manager::RAW_HTML,
                    'raw'   => sprintf(
                        __( 'Hello, looks like %1$s is missing in your site. Please click on the link below and install/activate %1$s. Make sure to refresh this page after installation or activation.', 'pixi-contactform' ),
                        '<a href="'.esc_url( admin_url( 'plugin-install.php?s=fluentform&tab=search&type=term' ) )
                        .'" target="_blank" rel="noopener">Contact Form 7</a>'
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
                ]
            );

            $this->add_control(
                'pxel_fluent_install',
                [
                    'type'  => \Elementor\Controls_Manager::RAW_HTML,
                    'raw'   => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=fluentform&tab=search&type=term' ) ).'" target="_blank" rel="noopener">Click to install or activate Contact Form 7</a>',
                ]
            );
            $this->end_controls_section();
            return;
        }

        $this->add_control(
            'pxel_fluent_form_id',
            [
                'label'     => esc_html__( 'Select Your Form', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::SELECT,
                'label_block' => true,
                'options'   => $this->get_fluent_forms(),

            ]
        );

        $this->add_control(
            'pxel_fluent_html_class',
            [
                'label'         => esc_html__( 'HTML Class', 'mega-elements-addons-for-elementor' ),
                'type'          => \Elementor\Controls_Manager::TEXT,
                'label_block'   => true,
                'description'   => esc_html__( 'Add "column-2" custom class to break the input fields.', 'mega-elements-addons-for-elementor' ),
            ]
        );

        $this->end_controls_section();

        /**
         * Fluent Form Fields Style
        */ 
        $this->start_controls_section(
            'pxel_fluent_label_style',
            [
                'label'     => esc_html__( 'Form Label', 'pixi-contactform' ),
                'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'pxel_fluent_label_margin',
            [
                'label' => __( 'Spacing Bottom', 'pixi-contactform' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ff-el-input--label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
                'label'     => esc_html__( 'Typography', 'pixi-contactform' ),
                'name' => 'pxel_fluent_input_label_font',
				'selector' => '{{WRAPPER}} .fluentform label',
			]
		);

        $this->add_control(
            'pxel_fluent_input_label_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fluentform label' => 'color: {{VALUE}}',
                ],
            ]
        );
        

        $this->end_controls_section();

         // Form INput 

         $this->start_controls_section(
            'pxel_fluent_input_style',
            [
                'label'     => esc_html__( 'Form Fields', 'pixi-contactform' ),
                'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'field_margin',
            [
                'label' => __( 'Spacing Bottom', 'pixi-contactform' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ff-el-group:not(.ff_submit_btn_wrapper)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
                'label'     => esc_html__( 'Typography', 'pixi-contactform' ),
                'name' => 'pxel_form_field_font',
				'selector' => '{{WRAPPER}} .ff-el-form-control',
			]
		);
        $this->add_control(
            'pxel_fluent_input_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-el-form-control' => 'color: {{VALUE}}',
                ],
            ]
        );


        $this->add_control(
            'field_placeholder_color',
            [
                'label' => __( 'Placeholder Text Color', 'pixi-contactform' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ::-webkit-input-placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} ::-moz-placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} ::-ms-input-placeholder' => 'color: {{VALUE}};',
                ],
            ]
		);
         
        $this->add_control(
            'pxel_fluent_input_bg_color',
            [
                'label'     => esc_html__( 'Background', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-el-form-control' => 'background: {{VALUE}}',
                ],
            ]
        );


        $this->add_control(
            'hr',
            [
                'type' => Controls_Manager::DIVIDER,
                'style' => 'thick',
            ]
        );


        $this->add_responsive_control(
			'pxel_fluent_input_padding',
			[
				'label' => esc_html__('Padding', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
                    '{{WRAPPER}} .ff-el-form-control:not(select)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
			]
		);

        $this->add_responsive_control(
            'pxel_textarea_height',
            [
                'label'     => esc_html__( 'Textarea Height', 'pixi-contactform' ),
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
                    '{{WRAPPER}} .fluentform textarea' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
			'pxel_fluent_input_border_radius',
			[
				'label' => esc_html__('Border Radius', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .ff-el-form-control' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
        
        $this->start_controls_tabs( 'pxel_fluent_tabs_field_state' );
        $this->start_controls_tab(
            'pxel_fluent_tab_field_normal',
            [
                'label' => __( 'Normal', 'pixi-contactform' ),
            ]
		);

		$this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'pxel_fluent_field_border',
                'selector' => '{{WRAPPER}} .ff-el-form-control',
            ]
        );

		$this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pxel_fluent_field_box_shadow',
                'selector' => '{{WRAPPER}} .ff-el-form-control:not(select)',
            ]
        );

        $this->add_control(
            'pxel_fluent_field_bg_color',
            [
                'label' => __( 'Background Color', 'pixi-contactform' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-el-form-control' => 'background-color: {{VALUE}}',
                ],
            ]
		);
       

		$this->end_controls_tab();

		$this->start_controls_tab(
            'pxel_fluent_tab_field_focus',
            [
                'label' => __( 'Focus', 'pixi-contactform' ),
            ]
		);

		$this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'field_focus_border',
                'selector' => '{{WRAPPER}} .ff-el-form-control:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pxel_fluent_field_focus_box_shadow',
                'exclude' => [
                    'box_shadow_position',
                ],
                'selector' => '{{WRAPPER}} .ff-el-form-control:not(select):focus',
            ]
		);

		$this->add_control(
            'pxel_fluent_field_focus_bg_color',
            [
                'label' => __( 'Background Color', 'pixi-contactform' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-el-form-control:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

		$this->end_controls_tab();
		$this->end_controls_tabs();

        $this->end_controls_section();


    // Form button  
        $this->start_controls_section(
            'pxel_submit_btn_style',
            [
                'label'     => esc_html__( 'Submit Button', 'pixi-contactform' ),
                'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
			'submit_btn_size',
			[
				'label' => esc_html__( 'Width', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Default', 'pixi-contactform' ),
					'100%' => esc_html__( 'Full Width', 'pixi-contactform' ),
					'custom'  => esc_html__( 'Custom', 'pixi-contactform' ),
				],
				'selectors' => [
					'{{WRAPPER}} .ff-btn-submit' => 'width: {{VALUE}};',
				],
			]
		);
        $this->add_responsive_control(
            'pxel_submit_btn_width',
            [
                'label'     => esc_html__( 'Custom Width', 'pixi-contactform' ),
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
                'size_units' => ['px' ,'%'],
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
                    '{{WRAPPER}} .ff-btn-submit' => 'width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'submit_btn_size' => 'custom',
                ],
            ]
        );

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'pxel_fluent_btn_font',
				'selector' => '{{WRAPPER}} .ff-btn-submit',
			]
		);

        
        $this->add_responsive_control(
			'pxel_fluent_btn_padding',
			[
				'label' => esc_html__('Padding', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .ff-btn-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'pxel_fluent_btn_border_radius',
			[
				'label' => esc_html__('Border Radius', 'pixi-contactform' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .ff-btn-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => __( 'Normal', 'pixi-contactform' ),
            ]
        );

        $this->add_control(
            'pxel_submit_btn_color',
            [
                'label'     => esc_html__( 'Text Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-btn-submit' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'pxel_submit_btn_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'pixi-contactform' ),
                'type'      =>  \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-btn-submit' => 'background: {{VALUE}}',
                ],
            ]
        );


		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'pxel_submit_btn_border',
				'selector' => '{{WRAPPER}} .ff-btn-submit',
			]
		);

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pxel_submit_box_shadow',
                'selector' => '{{WRAPPER}} .ff-btn-submit',
            ]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => __( 'Hover', 'pixi-contactform' ),
            ]
        );

        $this->add_control(
            'submit_btn_hover_color',
            [
                'label' => __( 'Text Color', 'pixi-contactform' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submit_btn_hover_bg_color',
            [
                'label' => __( 'Background Color', 'pixi-contactform' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .ff-btn-submit:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .ff-btn-submit:focus' => 'opacity:1;',
                ],
            ]
        );

        $this->add_control(
            'submit_btn_hover_border_color',
            [
                'label' => __( 'Border Color', 'pixi-contactform' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .ff-btn-submit:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_btn_hover_box_shadow',
                'selector' => '{{WRAPPER}} .ff-btn-submit:hover',
            ]
		);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! $this->is_form_activated() ) {
            return;
        }

        $settings = $this->get_settings_for_display();

        ?>
        <?php


        if ( ! empty( $settings['pxel_fluent_form_id'] ) ) {
            echo $this->do_shortcode( 'fluentform', [
                'id' => $settings['pxel_fluent_form_id'],
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
