<?php
namespace Pixi_contactform;
/**
 * Pixi Pro Form Widget
 *
 * Features added in this version:
 *  - Form layout: Stacked (default) OR Inline (field + button side by side)
 *  - Field width slider: 0–100% for each field group
 *  - Button width slider: 0–100% (exact percentage, not just auto/full)
 *  - Multiple submissions allowed — but same email cannot submit twice
 *  - All previous: Select/Multi-select, Choose, Checkbox controls
 */

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit;

class Pixi_Pro_Form_Widget extends Widget_Base {

    public function get_name()       { return 'pixi_pro_form'; }
    public function get_title()      { return __( 'Pixi Pro Form', 'pixi' ); }
    public function get_icon()       { return 'eicon-form-horizontal'; }
    public function get_categories() { return [ 'pixi-form' ]; }
    public function get_style_depends() {
		return [
			'pixi-contact',
		];
	}
    public function get_keywords()   { return [ 'form', 'contact', 'pixi', 'subscribe', 'newsletter', 'inline' ]; }

    // =========================================================
    //  CONTROLS
    // =========================================================
    protected function register_controls() {

        // ─────────────────────────────────────────────────────
        //  SECTION: Form Layout & Settings
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_layout', [
            'label' => __( 'Form Layout', 'pixi' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        /**
         * CHOOSE — Stacked (vertical) vs Inline (horizontal)
         * Inline mode puts all fields + button in one row —
         * perfect for a subscribe/newsletter bar.
         */
        $this->add_control( 'form_layout', [
            'label'   => __( 'Layout', 'pixi' ),
            'type'    => Controls_Manager::CHOOSE,
            'options' => [
                'stacked' => [
                    'title' => __( 'Stacked', 'pixi' ),
                    'icon'  => 'eicon-editor-list-ul',
                ],
                'inline'  => [
                    'title' => __( 'Inline', 'pixi' ),
                    'icon'  => 'eicon-ellipsis-h',
                ],
            ],
            'default' => 'stacked',
            'toggle'  => false,
        ] );

        $this->add_control( 'inline_gap', [
            'label'      => __( 'Column Gap (px)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 10 ],
            'condition'  => [ 'form_layout' => 'inline' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-form.pixi-inline' => 'gap: {{SIZE}}{{UNIT}}' ],
        ] );

        $this->add_control( 'inline_wrap', [
            'label'        => __( 'Wrap on Small Screens', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => [ 'form_layout' => 'inline' ],
        ] );

        $this->add_control( 'allow_duplicate_email', [
            'label'        => __( 'Allow Same Email Again?', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'pixi' ),
            'label_off'    => __( 'No', 'pixi' ),
            'return_value' => 'yes',
            'default'      => '',   // default = NO duplicate
            'description'  => __( 'Off = one submission per email address.', 'pixi' ),
        ] );

        $this->add_control( 'duplicate_message', [
            'label'     => __( 'Duplicate Email Message', 'pixi' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => __( 'This email has already been submitted.', 'pixi' ),
            'condition' => [ 'allow_duplicate_email' => '' ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  SECTION: Basic Fields
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_basic_fields', [
            'label' => __( 'Basic Fields', 'pixi' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_name', [
            'label'        => __( 'Show Name Field', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'name_placeholder', [
            'label'     => __( 'Name Placeholder', 'pixi' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => __( 'Your Name', 'pixi' ),
            'condition' => [ 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'email_placeholder', [
            'label'   => __( 'Email Placeholder', 'pixi' ),
            'type'    => Controls_Manager::TEXT,
            'default' => __( 'Your Email Address', 'pixi' ),
        ] );

        $this->add_control( 'show_phone', [
            'label'        => __( 'Show Phone Field', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'show_message', [
            'label'        => __( 'Show Message Field', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'button_text', [
            'label'   => __( 'Button Text', 'pixi' ),
            'type'    => Controls_Manager::TEXT,
            'default' => __( 'Send Message', 'pixi' ),
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  SECTION: Select Field
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_select_control', [
            'label' => __( 'Select Field', 'pixi' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_select', [
            'label'        => __( 'Show Select Field', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'select_mode', [
            'label'     => __( 'Select Mode', 'pixi' ),
            'type'      => Controls_Manager::CHOOSE,
            'options'   => [
                'single' => [ 'title' => __( 'Single', 'pixi' ), 'icon' => 'eicon-radio-on'  ],
                'multi'  => [ 'title' => __( 'Multi',  'pixi' ), 'icon' => 'eicon-checkbox'  ],
            ],
            'default'   => 'single',
            'toggle'    => false,
            'condition' => [ 'show_select' => 'yes' ],
        ] );

        $this->add_control( 'select_label', [
            'label'     => __( 'Field Label', 'pixi' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => __( 'Subject', 'pixi' ),
            'condition' => [ 'show_select' => 'yes' ],
        ] );

        $this->add_control( 'select_placeholder', [
            'label'     => __( 'Placeholder', 'pixi' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => __( '— Choose an option —', 'pixi' ),
            'condition' => [ 'show_select' => 'yes', 'select_mode' => 'single' ],
        ] );

        $repeater = new Repeater();
        $repeater->add_control( 'option_label', [ 'label' => __( 'Label', 'pixi' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Option', 'pixi' ) ] );
        $repeater->add_control( 'option_value', [ 'label' => __( 'Value', 'pixi' ), 'type' => Controls_Manager::TEXT, 'default' => '' ] );

        $this->add_control( 'select_options', [
            'label'       => __( 'Options', 'pixi' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => [
                [ 'option_label' => 'General Inquiry', 'option_value' => 'general' ],
                [ 'option_label' => 'Support',         'option_value' => 'support' ],
                [ 'option_label' => 'Partnership',     'option_value' => 'partnership' ],
                [ 'option_label' => 'Other',           'option_value' => 'other' ],
            ],
            'title_field' => '{{{ option_label }}}',
            'condition'   => [ 'show_select' => 'yes' ],
        ] );

        $this->add_control( 'select_default_single', [
            'label'     => __( 'Default Value', 'pixi' ),
            'type'      => Controls_Manager::SELECT,
            'options'   => [ '' => '— None —', 'general' => 'General Inquiry', 'support' => 'Support', 'partnership' => 'Partnership', 'other' => 'Other' ],
            'default'   => '',
            'condition' => [ 'show_select' => 'yes', 'select_mode' => 'single' ],
        ] );

        $this->add_control( 'select_default_multi', [
            'label'     => __( 'Pre-selected Values', 'pixi' ),
            'type'      => Controls_Manager::SELECT2,
            'multiple'  => true,
            'options'   => [ 'general' => 'General Inquiry', 'support' => 'Support', 'partnership' => 'Partnership', 'other' => 'Other' ],
            'default'   => [],
            'condition' => [ 'show_select' => 'yes', 'select_mode' => 'multi' ],
        ] );

        $this->add_control( 'select_required', [
            'label'        => __( 'Required', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => [ 'show_select' => 'yes' ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  SECTION: Checkbox Group
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_checkbox_control', [
            'label' => __( 'Checkbox Fields', 'pixi' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_checkbox', [
            'label'        => __( 'Show Checkbox Group', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'checkbox_label', [
            'label'     => __( 'Group Label', 'pixi' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => __( 'I am interested in…', 'pixi' ),
            'condition' => [ 'show_checkbox' => 'yes' ],
        ] );

        $this->add_control( 'checkbox_layout', [
            'label'     => __( 'Layout', 'pixi' ),
            'type'      => Controls_Manager::CHOOSE,
            'options'   => [
                'vertical'   => [ 'title' => __( 'Vertical',    'pixi' ), 'icon' => 'eicon-editor-list-ul' ],
                'horizontal' => [ 'title' => __( 'Horizontal',  'pixi' ), 'icon' => 'eicon-ellipsis-h'    ],
                'grid'       => [ 'title' => __( '2-Col Grid',  'pixi' ), 'icon' => 'eicon-posts-grid'    ],
            ],
            'default'   => 'vertical',
            'toggle'    => false,
            'condition' => [ 'show_checkbox' => 'yes' ],
        ] );

        $cb_repeater = new Repeater();
        $cb_repeater->add_control( 'cb_label',   [ 'label' => __( 'Label',            'pixi' ), 'type' => Controls_Manager::TEXT,     'default' => __( 'Option', 'pixi' ) ] );
        $cb_repeater->add_control( 'cb_value',   [ 'label' => __( 'Value',            'pixi' ), 'type' => Controls_Manager::TEXT,     'default' => '' ] );
        $cb_repeater->add_control( 'cb_checked', [ 'label' => __( 'Checked by default','pixi'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ] );

        $this->add_control( 'checkbox_items', [
            'label'       => __( 'Items', 'pixi' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $cb_repeater->get_controls(),
            'default'     => [
                [ 'cb_label' => 'Web Design',  'cb_value' => 'web-design',  'cb_checked' => '' ],
                [ 'cb_label' => 'Development', 'cb_value' => 'development', 'cb_checked' => '' ],
                [ 'cb_label' => 'SEO',         'cb_value' => 'seo',         'cb_checked' => '' ],
                [ 'cb_label' => 'Branding',    'cb_value' => 'branding',    'cb_checked' => '' ],
            ],
            'title_field' => '{{{ cb_label }}}',
            'condition'   => [ 'show_checkbox' => 'yes' ],
        ] );

        $this->add_control( 'checkbox_required', [
            'label'        => __( 'Require at least one', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
            'condition'    => [ 'show_checkbox' => 'yes' ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  SECTION: GDPR
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_gdpr', [
            'label' => __( 'Privacy & GDPR', 'pixi' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_gdpr', [
            'label'        => __( 'Show GDPR Checkbox', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'gdpr_text', [
            'label'     => __( 'GDPR Label Text', 'pixi' ),
            'type'      => Controls_Manager::TEXTAREA,
            'default'   => __( 'I agree to the Privacy Policy and consent to being contacted.', 'pixi' ),
            'rows'      => 3,
            'condition' => [ 'show_gdpr' => 'yes' ],
        ] );

        $this->end_controls_section();


        // =========================================================
        //  STYLE TABS
        // =========================================================

        // ─────────────────────────────────────────────────────
        //  STYLE: Form Wrapper
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_form_wrap', [
            'label' => __( 'Form Wrapper', 'pixi' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'form_bg_color', [
            'label'     => __( 'Background', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .pixi-form-wrap' => 'background-color: {{VALUE}}' ],
        ] );

        $this->add_control( 'form_padding', [
            'label'      => __( 'Padding', 'pixi' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-form-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
        ] );

        $this->add_control( 'form_border_radius', [
            'label'      => __( 'Border Radius', 'pixi' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-form-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
        ] );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
            'name'     => 'form_box_shadow',
            'selector' => '{{WRAPPER}} .pixi-form-wrap',
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  STYLE: Field Width
        //  Slider 0–100% — works for both stacked and inline
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_field_width', [
            'label' => __( 'Field Widths', 'pixi' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'field_width_notice', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => __( '<small>Set <strong>0</strong> to use automatic/default sizing. In <em>Inline</em> layout, use flex-grow instead (set to 100% to fill remaining space).</small>', 'pixi' ),
            'content_classes' => 'elementor-descriptor',
        ] );

        /**
         * SLIDER — field width 0–100%
         * 0 = no override (auto), otherwise applies the exact %
         */
        $this->add_control( 'name_field_width', [
            'label'      => __( 'Name Field Width (%)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'condition'  => [ 'show_name' => 'yes' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-field-name' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;' ],
        ] );

        $this->add_control( 'email_field_width', [
            'label'      => __( 'Email Field Width (%)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'selectors'  => [ '{{WRAPPER}} .pixi-field-email' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;' ],
        ] );

        $this->add_control( 'phone_field_width', [
            'label'      => __( 'Phone Field Width (%)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'condition'  => [ 'show_phone' => 'yes' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-field-phone' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;' ],
        ] );

        $this->add_control( 'select_field_width', [
            'label'      => __( 'Select Field Width (%)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'condition'  => [ 'show_select' => 'yes' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-field-select' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;' ],
        ] );

        $this->add_control( 'message_field_width', [
            'label'      => __( 'Message Field Width (%)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'condition'  => [ 'show_message' => 'yes' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-field-message' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;' ],
        ] );

        $this->add_control( 'field_row_gap', [
            'label'      => __( 'Row Gap (px)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 16 ],
            'selectors'  => [
                '{{WRAPPER}} .pixi-form.pixi-stacked .pixi-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                '{{WRAPPER}} .pixi-form.pixi-inline'                    => 'row-gap: {{SIZE}}{{UNIT}}',
            ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  STYLE: Text Inputs & Textarea
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_fields', [
            'label' => __( 'Input Fields', 'pixi' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'field_bg_color', [
            'label'     => __( 'Background', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .pixi-form input:not([type="checkbox"]), {{WRAPPER}} .pixi-form select, {{WRAPPER}} .pixi-form textarea' => 'background-color: {{VALUE}}' ],
        ] );

        $this->add_control( 'field_text_color', [
            'label'     => __( 'Text Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [ '{{WRAPPER}} .pixi-form input:not([type="checkbox"]), {{WRAPPER}} .pixi-form select, {{WRAPPER}} .pixi-form textarea' => 'color: {{VALUE}}' ],
        ] );

        $this->add_control( 'field_placeholder_color', [
            'label'     => __( 'Placeholder Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#aaaaaa',
            'selectors' => [
                '{{WRAPPER}} .pixi-form input::placeholder'    => 'color: {{VALUE}}',
                '{{WRAPPER}} .pixi-form textarea::placeholder' => 'color: {{VALUE}}',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'field_typography',
            'selector' => '{{WRAPPER}} .pixi-form input:not([type="checkbox"]), {{WRAPPER}} .pixi-form select, {{WRAPPER}} .pixi-form textarea',
        ] );

        $this->add_group_control( Group_Control_Border::get_type(), [
            'name'     => 'field_border',
            'selector' => '{{WRAPPER}} .pixi-form input:not([type="checkbox"]), {{WRAPPER}} .pixi-form select, {{WRAPPER}} .pixi-form textarea',
        ] );

        $this->add_control( 'field_border_radius', [
            'label'      => __( 'Border Radius', 'pixi' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-form input:not([type="checkbox"]), {{WRAPPER}} .pixi-form select, {{WRAPPER}} .pixi-form textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
        ] );

        $this->add_control( 'field_padding', [
            'label'      => __( 'Padding', 'pixi' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => '12', 'right' => '16', 'bottom' => '12', 'left' => '16', 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [ '{{WRAPPER}} .pixi-form input:not([type="checkbox"]), {{WRAPPER}} .pixi-form select, {{WRAPPER}} .pixi-form textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
        ] );

        $this->add_control( 'field_focus_border_color', [
            'label'     => __( 'Focus Border Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#0073aa',
            'selectors' => [
                '{{WRAPPER}} .pixi-form input:focus'    => 'border-color: {{VALUE}}; outline: none;',
                '{{WRAPPER}} .pixi-form select:focus'   => 'border-color: {{VALUE}}; outline: none;',
                '{{WRAPPER}} .pixi-form textarea:focus' => 'border-color: {{VALUE}}; outline: none;',
            ],
        ] );

        $this->add_control( 'multi_select_height', [
            'label'      => __( 'Multi-select Height (px)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 80, 'max' => 300 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 140 ],
            'selectors'  => [ '{{WRAPPER}} .pixi-form select[multiple]' => 'height: {{SIZE}}{{UNIT}}' ],
            'condition'  => [ 'select_mode' => 'multi' ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  STYLE: Labels
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_labels', [
            'label' => __( 'Field Labels', 'pixi' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'show_labels', [
            'label'        => __( 'Show Labels', 'pixi' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'description'  => __( 'Hide labels for a cleaner inline/subscribe look.', 'pixi' ),
        ] );

        $this->add_control( 'label_color', [
            'label'     => __( 'Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'condition' => [ 'show_labels' => 'yes' ],
            'selectors' => [ '{{WRAPPER}} .pixi-field-group label.pixi-label' => 'color: {{VALUE}}' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'label_typography',
            'selector'  => '{{WRAPPER}} .pixi-field-group label.pixi-label',
            'condition' => [ 'show_labels' => 'yes' ],
        ] );

        $this->add_control( 'label_spacing', [
            'label'      => __( 'Bottom Spacing (px)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 24 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 6 ],
            'condition'  => [ 'show_labels' => 'yes' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-field-group label.pixi-label' => 'margin-bottom: {{SIZE}}px; display: block;' ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  STYLE: Checkboxes
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_checkbox', [
            'label'     => __( 'Checkboxes', 'pixi' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_checkbox' => 'yes' ],
        ] );

        $this->add_control( 'checkbox_size', [
            'label'      => __( 'Size (px)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 12, 'max' => 32 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 18 ],
            'selectors'  => [ '{{WRAPPER}} .pixi-checkbox-item input[type="checkbox"]' => 'width: {{SIZE}}px; height: {{SIZE}}px;' ],
        ] );

        $this->add_control( 'checkbox_accent_color', [
            'label'     => __( 'Checked Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#0073aa',
            'selectors' => [ '{{WRAPPER}} .pixi-checkbox-item input[type="checkbox"]' => 'accent-color: {{VALUE}}' ],
        ] );

        $this->add_control( 'checkbox_label_color', [
            'label'     => __( 'Label Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#444444',
            'selectors' => [ '{{WRAPPER}} .pixi-checkbox-item label, {{WRAPPER}} .pixi-checkbox-item' => 'color: {{VALUE}}' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'checkbox_label_typography',
            'selector' => '{{WRAPPER}} .pixi-checkbox-item label, {{WRAPPER}} .pixi-checkbox-item',
        ] );

        $this->add_control( 'checkbox_item_gap', [
            'label'      => __( 'Gap Between Items (px)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 4, 'max' => 40 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 12 ],
            'selectors'  => [ '{{WRAPPER}} .pixi-checkbox-wrap' => 'gap: {{SIZE}}px' ],
        ] );

        $this->end_controls_section();


        // ─────────────────────────────────────────────────────
        //  STYLE: Submit Button
        // ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_button', [
            'label' => __( 'Submit Button', 'pixi' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        /**
         * SLIDER — button width 0–100%
         * 0 = auto (shrink to content)
         * Any value > 0 sets an exact percentage width
         */
        $this->add_control( 'button_width_pct', [
            'label'       => __( 'Button Width (%)', 'pixi' ),
            'type'        => Controls_Manager::SLIDER,
            'size_units'  => [ '%' ],
            'range'       => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
            'default'     => [ 'unit' => '%', 'size' => 0 ],
            'description' => __( '0 = auto (fit content). Set to 100% for full-width button.', 'pixi' ),
        ] );

        /**
         * CHOOSE — button alignment (only meaningful when width < 100%)
         */
        $this->add_control( 'button_align', [
            'label'     => __( 'Alignment', 'pixi' ),
            'type'      => Controls_Manager::CHOOSE,
            'options'   => [
                'flex-start' => [ 'title' => __( 'Left',   'pixi' ), 'icon' => 'eicon-text-align-left'   ],
                'center'     => [ 'title' => __( 'Center', 'pixi' ), 'icon' => 'eicon-text-align-center' ],
                'flex-end'   => [ 'title' => __( 'Right',  'pixi' ), 'icon' => 'eicon-text-align-right'  ],
            ],
            'default'   => 'flex-start',
            'toggle'    => false,
            'selectors' => [ '{{WRAPPER}} .pixi-btn-wrap' => 'display:flex; justify-content: {{VALUE}};' ],
        ] );

        $this->add_control( 'button_bg_color', [
            'label'     => __( 'Background', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#0073aa',
            'selectors' => [ '{{WRAPPER}} .pixi-submit' => 'background-color: {{VALUE}}' ],
        ] );

        $this->add_control( 'button_text_color', [
            'label'     => __( 'Text Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .pixi-submit' => 'color: {{VALUE}}' ],
        ] );

        $this->add_control( 'button_hover_bg', [
            'label'     => __( 'Hover Background', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .pixi-submit:hover' => 'background-color: {{VALUE}}' ],
        ] );

        $this->add_control( 'button_hover_text_color', [
            'label'     => __( 'Hover Text Color', 'pixi' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .pixi-submit:hover' => 'color: {{VALUE}}' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'button_typography',
            'selector' => '{{WRAPPER}} .pixi-submit',
        ] );

        $this->add_group_control( Group_Control_Border::get_type(), [
            'name'     => 'button_border',
            'selector' => '{{WRAPPER}} .pixi-submit',
        ] );

        $this->add_control( 'button_border_radius', [
            'label'      => __( 'Border Radius', 'pixi' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [ '{{WRAPPER}} .pixi-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
        ] );

        $this->add_control( 'button_padding', [
            'label'      => __( 'Padding', 'pixi' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => '14', 'right' => '32', 'bottom' => '14', 'left' => '32', 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [ '{{WRAPPER}} .pixi-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
        ] );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
            'name'     => 'button_box_shadow',
            'selector' => '{{WRAPPER}} .pixi-submit',
        ] );

        $this->add_control( 'button_transition', [
            'label'      => __( 'Transition (ms)', 'pixi' ),
            'type'       => Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 800 ] ],
            'default'    => [ 'size' => 250 ],
            'selectors'  => [ '{{WRAPPER}} .pixi-submit' => 'transition: all {{SIZE}}ms ease' ],
        ] );

        $this->end_controls_section();
    }


    // =========================================================
    //  RENDER
    // =========================================================
    protected function render() {
        $s     = $this->get_settings_for_display();
        $nonce = wp_create_nonce( 'pixi_form_nonce' );
        $uid   = 'pixi_' . $this->get_id(); // unique ID per widget instance

        // Layout CSS classes
        $layout_class = ( $s['form_layout'] === 'inline' ) ? 'pixi-inline' : 'pixi-stacked';
        $wrap_class   = ( $s['form_layout'] === 'inline' && $s['inline_wrap'] === 'yes' ) ? 'pixi-wrap' : '';

        // Checkbox layout CSS class
        $cb_layout_class = 'pixi-cb-vertical';
        if ( $s['checkbox_layout'] === 'horizontal' ) $cb_layout_class = 'pixi-cb-horizontal';
        if ( $s['checkbox_layout'] === 'grid'       ) $cb_layout_class = 'pixi-cb-grid';

        // Button width: 0 = auto, else exact %
        $btn_width_size = isset( $s['button_width_pct']['size'] ) ? (int) $s['button_width_pct']['size'] : 0;
        $btn_width_css  = ( $btn_width_size > 0 ) ? $btn_width_size . '%' : 'auto';

        // Show labels?
        $show_labels = ( $s['show_labels'] !== 'no' );

        // Duplicate email setting passed to JS as data attribute
        $allow_dup   = ( $s['allow_duplicate_email'] === 'yes' ) ? '1' : '0';
        $dup_message = esc_js( $s['duplicate_message'] ?? 'This email has already been submitted.' );
        ?>

        <style>
            /* ── Base ────────────────────────────────────── */
            #<?php echo esc_attr($uid); ?> .pixi-form input:not([type="checkbox"]),
            #<?php echo esc_attr($uid); ?> .pixi-form select,
            #<?php echo esc_attr($uid); ?> .pixi-form textarea {
                width: 100%; box-sizing: border-box; display: block;
            }
            #<?php echo esc_attr($uid); ?> .pixi-form textarea { resize: vertical; min-height: 120px; }
            #<?php echo esc_attr($uid); ?> .pixi-submit { cursor: pointer; box-sizing: border-box; width: <?php echo $btn_width_css; ?>; }

            /* ── Stacked layout ──────────────────────────── */
            #<?php echo esc_attr($uid); ?> .pixi-form.pixi-stacked { display: block; }

            /* ── Inline layout ───────────────────────────── */
            #<?php echo esc_attr($uid); ?> .pixi-form.pixi-inline {
                display: flex; align-items: flex-end;
                <?php echo ($wrap_class === 'pixi-wrap') ? 'flex-wrap: wrap;' : 'flex-wrap: nowrap;'; ?>
            }
            #<?php echo esc_attr($uid); ?> .pixi-form.pixi-inline .pixi-field-group { margin-bottom: 0; }
            #<?php echo esc_attr($uid); ?> .pixi-form.pixi-inline .pixi-btn-wrap    { flex-shrink: 0; }

            /* ── Checkboxes ──────────────────────────────── */
            #<?php echo esc_attr($uid); ?> .pixi-checkbox-wrap  { display: flex; }
            #<?php echo esc_attr($uid); ?> .pixi-cb-vertical    { flex-direction: column; }
            #<?php echo esc_attr($uid); ?> .pixi-cb-horizontal  { flex-direction: row; flex-wrap: wrap; }
            #<?php echo esc_attr($uid); ?> .pixi-cb-grid        { display: grid !important; grid-template-columns: 1fr 1fr; }
            #<?php echo esc_attr($uid); ?> .pixi-checkbox-item  { display: flex; align-items: center; gap: 8px; cursor: pointer; }
            #<?php echo esc_attr($uid); ?> .pixi-checkbox-item input[type="checkbox"] { cursor: pointer; flex-shrink: 0; }

            /* ── Misc ────────────────────────────────────── */
            #<?php echo esc_attr($uid); ?> .pixi-form select    { appearance: auto; }
            #<?php echo esc_attr($uid); ?> .pixi-form-response  { margin-top: 10px; font-size: 14px; }
            #<?php echo esc_attr($uid); ?> .pixi-label          { display: block; }
            <?php if ( ! $show_labels ) : ?>
            #<?php echo esc_attr($uid); ?> .pixi-label          { display: none !important; }
            <?php endif; ?>
        </style>

        <div class="pixi-form-wrap" id="<?php echo esc_attr($uid); ?>">
          <form class="pixi-form <?php echo esc_attr( $layout_class ); ?>"
                method="post" novalidate
                data-allow-dup="<?php echo $allow_dup; ?>"
                data-dup-msg="<?php echo $dup_message; ?>"
                data-ajaxurl="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>">

            <?php /* ── NAME ── */ ?>
            <?php if ( $s['show_name'] === 'yes' ) : ?>
            <div class="pixi-field-group pixi-field-name">
                <label class="pixi-label" for="<?php echo esc_attr($uid); ?>_name">
                    <?php esc_html_e( 'Name', 'pixi' ); ?>
                </label>
                <input type="text" id="<?php echo esc_attr($uid); ?>_name" name="pixi_name"
                       placeholder="<?php echo esc_attr( $s['name_placeholder'] ); ?>" required />
            </div>
            <?php endif; ?>

            <?php /* ── EMAIL ── */ ?>
            <div class="pixi-field-group pixi-field-email">
                <label class="pixi-label" for="<?php echo esc_attr($uid); ?>_email">
                    <?php esc_html_e( 'Email', 'pixi' ); ?>
                </label>
                <input type="email" id="<?php echo esc_attr($uid); ?>_email" name="pixi_email"
                       placeholder="<?php echo esc_attr( $s['email_placeholder'] ); ?>" required />
            </div>

            <?php /* ── PHONE ── */ ?>
            <?php if ( $s['show_phone'] === 'yes' ) : ?>
            <div class="pixi-field-group pixi-field-phone">
                <label class="pixi-label" for="<?php echo esc_attr($uid); ?>_phone">
                    <?php esc_html_e( 'Phone', 'pixi' ); ?>
                </label>
                <input type="tel" id="<?php echo esc_attr($uid); ?>_phone" name="pixi_phone"
                       placeholder="<?php esc_attr_e( '+1 000 000 0000', 'pixi' ); ?>" />
            </div>
            <?php endif; ?>

            <?php /* ── SELECT / MULTI-SELECT ── */ ?>
            <?php if ( $s['show_select'] === 'yes' && ! empty( $s['select_options'] ) ) :
                $is_multi   = ( $s['select_mode'] === 'multi' );
                $fname      = $is_multi ? 'pixi_select[]' : 'pixi_select';
                $multi_attr = $is_multi ? 'multiple' : '';
                $req_attr   = ( $s['select_required'] === 'yes' ) ? 'required' : '';
                $pre_single = $s['select_default_single'] ?? '';
                $pre_multi  = is_array( $s['select_default_multi'] ) ? $s['select_default_multi'] : [];
            ?>
            <div class="pixi-field-group pixi-field-select">
                <label class="pixi-label" for="<?php echo esc_attr($uid); ?>_select">
                    <?php echo esc_html( $s['select_label'] ); ?>
                    <?php if ( $s['select_required'] === 'yes' ) : ?><span style="color:red"> *</span><?php endif; ?>
                </label>
                <select id="<?php echo esc_attr($uid); ?>_select"
                        name="<?php echo esc_attr($fname); ?>"
                        <?php echo $multi_attr; ?> <?php echo $req_attr; ?>>
                    <?php if ( ! $is_multi && $s['select_placeholder'] ) : ?>
                        <option value=""><?php echo esc_html( $s['select_placeholder'] ); ?></option>
                    <?php endif; ?>
                    <?php foreach ( $s['select_options'] as $opt ) :
                        $oval = esc_attr( $opt['option_value'] );
                        $olbl = esc_html( $opt['option_label'] );
                        $sel  = $is_multi
                            ? ( in_array($oval, $pre_multi, true) ? 'selected' : '' )
                            : ( $oval === $pre_single ? 'selected' : '' );
                    ?>
                        <option value="<?php echo $oval; ?>" <?php echo $sel; ?>><?php echo $olbl; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ( $is_multi ) : ?>
                <small style="color:#888;font-size:12px;margin-top:4px;display:block;">
                    <?php esc_html_e( 'Hold Ctrl / Cmd to select multiple.', 'pixi' ); ?>
                </small>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php /* ── CHECKBOX GROUP ── */ ?>
            <?php if ( $s['show_checkbox'] === 'yes' && ! empty( $s['checkbox_items'] ) ) : ?>
            <div class="pixi-field-group pixi-field-checkboxes pixi-checkbox-group"
                 <?php if ( $s['checkbox_required'] === 'yes' ) : ?>data-pixi-required="1"<?php endif; ?>>
                <label class="pixi-label">
                    <?php echo esc_html( $s['checkbox_label'] ); ?>
                    <?php if ( $s['checkbox_required'] === 'yes' ) : ?><span style="color:red"> *</span><?php endif; ?>
                </label>
                <div class="pixi-checkbox-wrap <?php echo esc_attr($cb_layout_class); ?>">
                    <?php foreach ( $s['checkbox_items'] as $idx => $item ) :
                        $cbid    = esc_attr( $uid . '_cb_' . $idx );
                        $checked = ( $item['cb_checked'] === 'yes' ) ? 'checked' : '';
                    ?>
                    <label class="pixi-checkbox-item" for="<?php echo $cbid; ?>">
                        <input type="checkbox" id="<?php echo $cbid; ?>"
                               name="pixi_checkboxes[]"
                               value="<?php echo esc_attr( $item['cb_value'] ); ?>"
                               <?php echo $checked; ?> />
                        <?php echo esc_html( $item['cb_label'] ); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php /* ── MESSAGE ── */ ?>
            <?php if ( $s['show_message'] === 'yes' ) : ?>
            <div class="pixi-field-group pixi-field-message">
                <label class="pixi-label" for="<?php echo esc_attr($uid); ?>_message">
                    <?php esc_html_e( 'Message', 'pixi' ); ?>
                </label>
                <textarea id="<?php echo esc_attr($uid); ?>_message" name="pixi_message"
                          placeholder="<?php esc_attr_e( 'Write your message here…', 'pixi' ); ?>"
                          required></textarea>
            </div>
            <?php endif; ?>

            <?php /* ── GDPR ── */ ?>
            <?php if ( $s['show_gdpr'] === 'yes' ) : ?>
            <div class="pixi-field-group pixi-field-gdpr">
                <label class="pixi-checkbox-item" for="<?php echo esc_attr($uid); ?>_gdpr">
                    <input type="checkbox" id="<?php echo esc_attr($uid); ?>_gdpr"
                           name="pixi_gdpr" value="1" required />
                    <?php echo esc_html( $s['gdpr_text'] ); ?>
                </label>
            </div>
            <?php endif; ?>

            <?php /* ── HIDDEN ── */ ?>
            <input type="hidden" name="pixi_nonce" value="<?php echo esc_attr($nonce); ?>" />
            <input type="hidden" name="action"     value="pixi_submit_form" />

            <?php /* ── SUBMIT ── */ ?>
            <div class="pixi-btn-wrap">
                <button type="submit" class="pixi-submit">
                    <?php echo esc_html( $s['button_text'] ); ?>
                </button>
            </div>

            <div class="pixi-form-response" role="alert" aria-live="polite"></div>

          </form><!-- .pixi-form -->
        </div><!-- .pixi-form-wrap -->

        <script>
        (function($){
            var $form     = $('#<?php echo esc_js($uid); ?> .pixi-form');
            var ajaxUrl   = $form.data('ajaxurl');
            var allowDup  = $form.data('allow-dup') === 1 || $form.data('allow-dup') === '1';
            var dupMsg    = $form.data('dup-msg');

            $form.on('submit', function(e){
                e.preventDefault();

                // Validate required checkbox groups
                var cbValid = true;
                $form.find('.pixi-checkbox-group[data-pixi-required]').each(function(){
                    if( $(this).find('input[type="checkbox"]:checked').length === 0 ){
                        cbValid = false;
                        $(this).find('.pixi-checkbox-wrap').css('outline','2px solid red');
                    } else {
                        $(this).find('.pixi-checkbox-wrap').css('outline','');
                    }
                });
                if( ! cbValid ){
                    $form.find('.pixi-form-response')
                         .html('<p style="color:red">⚠️ Please select at least one option.</p>');
                    return;
                }

                var $btn = $form.find('.pixi-submit');
                $btn.prop('disabled', true).css('opacity','0.65');
                $form.find('.pixi-form-response').html('');

                $.post( ajaxUrl, $form.serialize(), function(res){
                    $btn.prop('disabled', false).css('opacity','1');
                    if( res && res.success ){
                        $form.find('.pixi-form-response')
                             .html('<p style="color:green;font-weight:600">✅ ' + res.data.message + '</p>');
                        $form[0].reset();
                    } else {
                        var msg = (res && res.data && res.data.message) ? res.data.message : 'Something went wrong.';
                        $form.find('.pixi-form-response')
                             .html('<p style="color:red">⚠️ ' + msg + '</p>');
                    }
                }).fail(function(){
                    $btn.prop('disabled', false).css('opacity','1');
                    $form.find('.pixi-form-response')
                         .html('<p style="color:red">⚠️ Server error. Please try again.</p>');
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}