<?php
/**
 * Product Table Widget.
 *
 * @package Product Table Elementor
 */
namespace PTEW_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Widget_Base;
use Elementor\Repeater;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

class PRODUCT_ELEMENTOR_LIST_TABLE_MODULE extends Widget_Base {

	   public function __construct($data = [], $args = null) {

	    parent::__construct($data, $args);
    		wp_enqueue_style( 'datatables-css', plugins_url( '../assets/css/jquery.dataTables.min.css', __FILE__ ) );
    		wp_enqueue_script( 'datatables-js', plugins_url( '../assets/js/jquery.dataTables.min.js', __FILE__ ), array( 'jquery' ), '', true );
	   }

		/**
		 * Get the name of the widget.
		 *
		 * @return string The name of the widget.
		 */
		public function get_name() {
		    return 'ptew-woocommerce-product-table';
		}

		/**
		 * Get the title of the widget.
		 *
		 * @return string The title of the widget.
		 */
		public function get_title() {
		    return esc_html__( 'Product Table', 'product-table-for-elementor' );
		}

		/**
		 * Get the icon of the widget.
		 *
		 * @return string The icon of the widget.
		 */
		public function get_icon() {
		    return 'eicon-column';
		}

		/**
		 * Get the categories of the widget.
		 *
		 * @return array The categories of the widget.
		 */
		public function get_categories() {
		    return [ 'ptew-addons-elementor' ];
		}

    protected function get_product_orderby_options()
    {
        return [
                'ID'            => esc_html__('ID', 'product-table-for-elementor'),
                'title'         => esc_html__('Title', 'product-table-for-elementor'),
                'name'          => esc_html__('Name', 'product-table-for-elementor'),
                'date'          => esc_html__('Date', 'product-table-for-elementor'),
                'comment_count' => esc_html__('Popular', 'product-table-for-elementor'),
                'modified'      => esc_html__('Modified', 'product-table-for-elementor'),
                'price'         => esc_html__('Price', 'product-table-for-elementor'),
                'sales'         => esc_html__('Sales', 'product-table-for-elementor'),
                'rated'         => esc_html__('Top Rated', 'product-table-for-elementor'),
                'rand'          => esc_html__('Random', 'product-table-for-elementor'),
                'menu_order'    => esc_html__('Menu Order', 'product-table-for-elementor'),
                'sku'           => esc_html__('SKU', 'product-table-for-elementor'),
                'stock_status'  => esc_html__('Stock Status', 'product-table-for-elementor')
        ];
    }

    	// Product Settings
		protected function register_controls() {
			$this->start_controls_section(
				'section_button',
				[
					'label' => __( 'Product Settings', 'product-table-for-elementor' ),
				]
			);

			// Select Products by Categories
	        $this->add_control('product_grid_categories', [
	            'label' => esc_html__('Select Products by Categories', 'product-table-for-elementor'),
	            'description' => esc_html__('Leave empty if you want to show from all categories.', 'product-table-for-elementor'),
	            'type' => Controls_Manager::SELECT2,
	            'label_block' => true,
	            'multiple' => true,
	            'options' => ptew_get_product_terms_list('product_cat', 'slug'),
	        ]);

	        // Number of Products to display
	        $this->add_control('products_count', [
	            'label' => __('Products Count', 'product-table-for-elementor'),
	            'description' => esc_html__('Number of Products to display.', 'product-table-for-elementor'),
	            'type' => Controls_Manager::NUMBER,
	            'default' => 4,
	            'min' => 1,
	            'max' => 1000,
	            'step' => 1,
	        ]);

	        // Order By
	        $this->add_control('orderby', [
	            'label' => __('Order By', 'product-table-for-elementor'),
	            'type' => Controls_Manager::SELECT,
	            'options' => $this->get_product_orderby_options(),
	            'default' => 'date',

	        ]);

	        // Order
	        $this->add_control('order', [
	            'label' => __('Order', 'product-table-for-elementor'),
	            'type' => Controls_Manager::SELECT,
	            'options' => [
	                'asc' => 'Ascending',
	                'desc' => 'Descending',
	            ],
	            'default' => 'desc',

	        ]);


        /**
        * Control: Featured Image Size
        */
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name'              => 'feature_img_size',
                'fields_options'    => [
                    'size'  => [
                        'label' => esc_html__( 'Featured Image Size', 'product-table-for-elementor' ),
                    ],
                ],
                'exclude'           => [ 'custom' ],
                'default'           => 'thumbnail',
            ]
        );


			// Make Table Column Sortable
			$this->add_control(
				'make_table_sortable',
				[
					'label' 		=> __( 'Enable Column Sorting', 'product-table-for-elementor' ),
					'type' 			=> Controls_Manager::SWITCHER,
					'default' 		=> false,
					'label_on' 		=> __( 'Yes', 'product-table-for-elementor' ),
					'label_off' 	=> __( 'No', 'product-table-for-elementor' ),
					'return_value' 	=> 'yes',
				]
			);

			$this->end_controls_section();

			// WooCommerce Product Meta Fields
			$this->start_controls_section(
				'ptew_product_fields_label_section',
				[
					'label' => esc_html__( 'Add Product Fields', 'product-table-for-elementor' ),
				]
			);

			$repeater = new Repeater();

			// Meta Keys
			$meta_keys = ptew_get_product_meta_list();
			$meta_key_options = array_combine($meta_keys, $meta_keys);

			$repeater->add_control(
			    'um_user_meta_field_value',
			    [
			        'label'         => esc_html__('Meta Key', 'product-table-for-elementor'),
			        'type'          => Controls_Manager::SELECT,
			        'default'       => 'description',
			        'label_block'   => true,
			        'options'       => $meta_key_options,
			    ]
			);

	        	// Higlight Row
		        $repeater->add_control(
		            'um_user_meta_field_highlight',
		            [
		                'label' => esc_html__('Higlight Row', 'product-table-for-elementor'),
		                'type' => Controls_Manager::SWITCHER,
						'label_on' => esc_html__( 'Show', 'product-table-for-elementor' ),
						'label_off' => esc_html__( 'Hide', 'product-table-for-elementor' ),		                
		                'return_value' => 'highlighted',
		                'default' => 'plain',
		            ]
		        );

				// User Meta
		  		$this->add_control(
					'ptew_product_meta_control',
					[
		                'label' 		=> esc_html__( 'Product Fields', 'product-table-for-elementor' ),
						'type' 			=> Controls_Manager::REPEATER,
						'seperator' 	=> 'before',
						'fields' 		=> $repeater->get_controls(),
						'title_field' 	=> '{{{um_user_meta_field_value}}}',
						'default' => [
							[ 'um_user_meta_field_value' => 'Product Title' ],
							[ 'um_user_meta_field_value' => 'Image' ],
							[ 'um_user_meta_field_value' => 'Price' ],

						],						
					]
				);

			$this->end_controls_section();

			// Start Style Controls
	        $this->start_controls_section(
	            'ptew_product_section_member_style',
	            [
	                'label' => __( 'Table Header', 'product-table-for-elementor' ),
	                'tab' 	=> Controls_Manager::TAB_STYLE
	            ]
	        );

		        // Table Header Background Color
		        $this->add_control(
					'um_table_header_bg',
					[
						'label' 	=> __( 'Background Color', 'product-table-for-elementor' ),
						'type' 		=> Controls_Manager::COLOR,
						'default' 	=> '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .um-table-header' => 'background-color: {{VALUE}}',
						]
					]
				);

		        // Member Name Color
		        $this->add_control(
					'um_member_block_title_color',
					[
						'label' 	=> __( 'Title Color', 'product-table-for-elementor' ),
						'type' 		=> Controls_Manager::COLOR,
						'default'	=> '#475467',
						'selectors' => [
							'{{WRAPPER}} .table-header-column, {{WRAPPER}} .dataTables_paginate .paginate_button.current' => 'color: {{VALUE}};',
						]
					]
				);				

		        // Text Alignment
				$this->add_control(
		            'title_alignment',
		            [
		                'label' 	=> __( 'Text Alignment', 'product-table-for-elementor' ),
		                'type' 		=> Controls_Manager::CHOOSE,
		                'options' 	=> [
								'text-left' => [
									'title' => __( 'Left', 'product-table-for-elementor' ),
									'icon' 	=> 'eicon-text-align-left',
								],
								'text-center' => [
									'title' => __( 'Center', 'product-table-for-elementor' ),
									'icon' 	=> 'eicon-text-align-center',
								],
								'text-right' => [
									'title' => __( 'Right', 'product-table-for-elementor' ),
									'icon' 	=> 'eicon-text-align-right',
								]
						],
						'default' 	=> 'text-left',
						'separator'	=> 'before'
		            ]
		        );

		        // User Name Typography
		        $this->add_group_control(
		            Group_Control_Typography::get_type(),
		            [
		                'name' 		=> 'product_table_header_typography',
		                'label' 	=> esc_html__( 'Typography', 'product-table-for-elementor' ),
		                'selector' 	=> '{{WRAPPER}} .table-header-column, {{WRAPPER}} .dataTables_paginate .paginate_button.current',
		                'global'    => ['default' => Global_Typography::TYPOGRAPHY_PRIMARY,],
				        'fields_options' => [
				            'typography' => ['default' => 'yes'],
				            'font_weight' => ['default' => 500],
				        ],		                
		                
		            ]
		        );

				$this->add_group_control(
					Group_Control_Border::get_type(),
						[
							'name' => 'um_member_table_cell_header_border',
							'label' => esc_html__( 'Border', 'product-table-for-elementor'),
							'selector' => '{{WRAPPER}} .um-table-header tr td',
							'separator'	=> 'before',
							'fields_options' => [
								'border' => [
									'default' => 'solid',
								],
								'width' => [
									'default' => [
										'top' => '0',
										'right' => '0',
										'bottom' => '1',
										'left' => '0',
										'isLinked' => false,
									],
								],
								'color' => [
									'default' => '#EAECF0',
								],
							],							
						]
				);

				// Padding Between Users
				$this->add_responsive_control(
					'um_member_block_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'product-table-for-elementor' ),
						'type' 			=> Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em' ],
						'default'    => [
							'top'    => '16',
							'bottom' => '16',
							'left'   => '16',
							'right'  => '16',
							'unit'   => 'px',
						],						
						'selectors' 	=> [
							'{{WRAPPER}} .table-header-column' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);				


			$this->end_controls_section();

			// Member Block Typography
	        $this->start_controls_section(
	            'ptew_product_section_member_typography',
	            [
	                'label' => __( 'Table Content', 'product-table-for-elementor' ),
	                'tab' 	=> Controls_Manager::TAB_STYLE
	            ]
	        );


				$this->add_control(
					'ptew_product_odd_style_heading',
					[
						'label' => esc_html__( 'ODD Row - Zebra Stripes', 'product-table-for-elementor'),
						'type' => Controls_Manager::HEADING,
					]
				);

				$this->add_control(
					'ptew_product_content_color_odd',
					[
						'label' => esc_html__( 'Color ( Odd Row )', 'product-table-for-elementor'),
						'type' => Controls_Manager::COLOR,
						'default' => '#475467',
						'selectors' => [
							'{{WRAPPER}} .ptew-product-table-list tbody > tr:nth-child(2n) td' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'ptew_product_content_bg_odd',
					[
						'label' => esc_html__( 'Background ( Odd Row )', 'product-table-for-elementor'),
						'type' => Controls_Manager::COLOR,
						'default' => '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .ptew-product-table-list tbody > tr:nth-child(2n) td' => 'background: {{VALUE}};',
						],
					]
				);


				$this->add_control(
					'ptew_product_even_style_heading',
					[
						'label' => esc_html__( 'EVEN Row - Zebra Stripes', 'product-table-for-elementor'),
						'type' => Controls_Manager::HEADING,
						'separator'	=> 'before'
					]
				);

				$this->add_control(
					'ptew_product_content_color_even',
					[
						'label' => esc_html__( 'Color ( EVEN Row )', 'product-table-for-elementor'),
						'type' => Controls_Manager::COLOR,
						'default' => '#475467',
						'selectors' => [
							'{{WRAPPER}} .ptew-product-table-list tbody > tr:nth-child(2n+1) td' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'ptew_product_content_bg_even',
					[
						'label' => esc_html__( 'Background ( EVEN Row )', 'product-table-for-elementor'),
						'type' => Controls_Manager::COLOR,
						'default' => '#F9FAFB',
						'selectors' => [
							'{{WRAPPER}} .ptew-product-table-list tbody > tr:nth-child(2n+1) td' => 'background: {{VALUE}};',
						],
					]
				);


				// Meta Value Typography
		        $this->add_group_control(
		            Group_Control_Typography::get_type(),
		            [
		                'name' => 'um_member_meta_field_field_typography',
		                'label' => esc_html__( 'Typography', 'product-table-for-elementor' ),
		                'selector' 	=> '{{WRAPPER}} .um-table-column',
		                'global'    => ['default' => Global_Typography::TYPOGRAPHY_TEXT,],
		                'separator'	=> 'before'
		            ]
		        );

				$this->add_group_control(
					Group_Control_Border::get_type(),
						[
							'name' => 'um_member_table_cell_border',
							'label' => esc_html__( 'Border', 'product-table-for-elementor'),
							'selector' => '{{WRAPPER}} .ptew-product-table-list tbody tr td',
							'separator'	=> 'before',							
						]
				);

				$this->add_responsive_control(
					'um_member_table_each_cell_padding',
					[
						'label' => esc_html__( 'Padding', 'product-table-for-elementor'),
						'type' => Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em' ],
						'default'    => [
							'top'    => '10',
							'bottom' => '10',
							'left'   => '10',
							'right'  => '10',
							'unit'   => 'px',
						],
						'selectors' => [
								 '{{WRAPPER}} .ptew-product-table-list tbody tr td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						 ],
					]
				);

			$this->end_controls_section();


			// Highlighted Column
	        $this->start_controls_section(
	            'ptew_product_section_table_highlight_style',
	            [
	                'label' => __( 'Highlighted Column', 'product-table-for-elementor' ),
	                'tab' 	=> Controls_Manager::TAB_STYLE
	            ]
	        );

				$this->add_control(
					'ptew_product_table_color_highlited',
					[
						'label' => esc_html__( 'Color', 'product-table-for-elementor'),
						'type' => Controls_Manager::COLOR,
						'default' => '#126e65',
						'selectors' => [
							'{{WRAPPER}} .ptew-product-table-list td.highlighted.um-table-column, {{WRAPPER}} .ptew-product-table-list td.highlighted.um-table-column a' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'ptew_product_table_bg_highlited',
					[
						'label' => esc_html__( 'Background', 'product-table-for-elementor'),
						'type' => Controls_Manager::COLOR,
						'default' => '#ebfbeb',
						'selectors' => [
							'{{WRAPPER}} .ptew-product-table-list td.highlighted.um-table-column, {{WRAPPER}} .ptew-product-table-list td.highlighted.um-table-column a' => 'background: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
						[
							'name' => 'ptew_product_table_border_highlited',
							'label' => esc_html__( 'Border', 'product-table-for-elementor'),
							'selector' => '{{WRAPPER}} .ptew-product-table-list td.highlighted.um-table-column',
							'separator'	=> 'before',
							'fields_options' => [
								'border' => [
									'default' => 'solid',
								],
								'width' => [
									'default' => [
										'top' => '1',
										'right' => '1',
										'bottom' => '1',
										'left' => '1',
										'isLinked' => false,
									],
								],
								'color' => [
									'default' => '#f6f6f6',
								],
							],							
						]
				);



			$this->end_controls_section();
		}

	/**
	 * Render the product meta fields based on the settings.
	 *
	 * @param array $settings The widget settings.
	 */
	protected function render_woo_product_meta($settings, $product_id)
	{
	    if (empty($settings['ptew_product_meta_control'])) {
	        return;
	    }

	    $user_meta_controls = $this->get_settings('ptew_product_meta_control');
	    $settings 			= $this->get_settings();
	    $image_size = $settings['feature_img_size_size'];



	    $product = wc_get_product($product_id);

	    if (!empty($user_meta_controls)) {
	        foreach ($user_meta_controls as $control) {
	            $meta_key = $control['um_user_meta_field_value'];
	            ?>
	            <td class="<?php echo esc_attr($control['um_user_meta_field_highlight']); ?> um-table-column <?php echo esc_html(sanitize_html_class($meta_key)); ?>">
	                <?php
	                switch ($meta_key) {
	                    case 'Product Title':
						    ?>
						    <a href="<?php the_permalink(); ?>">
						        <?php the_title('<h5 class="product_title">', '</h5>'); ?>
						    </a>
	                        <?php
	                        break;
	                    case 'ID':
	                        $meta_value = get_the_ID();
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Description':
	                        woocommerce_template_single_excerpt();
	                        break;
						case 'Image':
						    ?>
						    <a href="<?php the_permalink(); ?>">
						        <?php echo woocommerce_get_product_thumbnail($image_size); ?>
						    </a>
						    <?php
						    break;
	                    case 'Price':
	                        woocommerce_template_single_price();
	                        break;
	                    case 'Rating':
	                        woocommerce_template_single_rating();
	                        break;
	                    case 'Add to Cart':
	                        woocommerce_template_single_add_to_cart();
	                        break;
	                    case 'SKU':
	                        $meta_value = get_post_meta($product_id, '_sku', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Regular Price':
	                        $meta_value = get_post_meta($product_id, '_regular_price', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Sale Price':
	                        $meta_value = get_post_meta($product_id, '_sale_price', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Stock Quantity':
	                        $meta_value = get_post_meta($product_id, '_stock', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Stock Status':
	                        $meta_value = get_post_meta($product_id, '_stock_status', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Backorder Allowed':
	                        $meta_value = get_post_meta($product_id, '_backorders', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Total Sold':
	                        $meta_value = get_post_meta($product_id, 'total_sales', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Number of reviews':
	                        $meta_value = get_post_meta($product_id, '_wc_review_count', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Manage Stock':
	                        $meta_value = get_post_meta($product_id, '_manage_stock', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Sold Individually':
	                        $meta_value = get_post_meta($product_id, '_sold_individually', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Downloadable':
	                        $meta_value = get_post_meta($product_id, '_downloadable', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Download Limit':
	                        $meta_value = get_post_meta($product_id, '_download_limit', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Download Expiry':
	                        $meta_value = get_post_meta($product_id, '_download_expiry', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Average Rating':
	                        $meta_value = get_post_meta($product_id, '_wc_average_rating', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Virtual':
	                        $meta_value = get_post_meta($product_id, '_virtual', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Attributes':
							$meta_value = get_post_meta($product_id, '_product_attributes', true);

							foreach ($meta_value as $meta) {
							    $attribute_name = esc_html(wc_attribute_label($meta['name']));
							    $attribute_values = wc_get_product_terms($product_id, $meta['name'], array('fields' => 'all'));
							    $attribute_names = array();

							    foreach ($attribute_values as $attribute_value) {
							        $attribute_names[] = esc_html($attribute_value->name);
							    }

							    if (!empty($attribute_names)) {
							        echo "<div class='attributes'>";
							        echo "<label>{esc_html($attribute_name)}:</label>";
							        echo "<span>" . esc_html(implode(", ", $attribute_names)) . "</span>";
							        echo "</div>";
							    }
							}

	                        break;                        
	                    case 'Tax Status':
	                        $meta_value = get_post_meta($product_id, '_tax_class', true);
	                        echo esc_attr($meta_value);
	                        break;
	                    case 'Image Gallery':
							$meta_value = get_post_meta($product_id, '_product_image_gallery', true);?>

						    <a href="<?php the_permalink(); ?>">
						        <?php echo woocommerce_get_product_thumbnail('shop_catalog'); ?>
						    </a>

						    <?php						

							if (!empty($meta_value)) {
							    $gallery_images = explode(',', $meta_value);

							    foreach ($gallery_images as $image_id) {
							        $image_url = wp_get_attachment_image_url($image_id, 'full');

							        if (!empty($image_url)) {
							            echo '<img src="' . esc_url($image_url) . '" alt="Product Image">';
							        }
							    }
							}
	                        break;
	                    case 'Rating count':
							$meta_value = get_post_meta($product_id, '_wc_rating_count', true);

							if (is_array($meta_value)) {
							    echo implode(' ', array_map(function($key, $value) {
							        return $key . "(" . esc_attr($value) . ")";
							    }, array_keys($meta_value), $meta_value));
							}
	                        break;
	                    case 'Downloadable files':
							if (is_array($meta_value)) {
							    foreach ($meta_value as $file) {
							        if (isset($file['file']) && isset($file['name'])) {
							            echo '<a href="' . esc_url($file['file']) . '">' . esc_attr($file['name']) . '</a><br>';
							        }
							    }
							}
	                        break;
	                    case 'Category':
							echo wc_get_product_category_list(
							    $product_id,
							    ', ',
							    '<span class="posted_in">',
							    '</span>'
							);
	                        break;
	                    case 'Tags':
							echo wc_get_product_tag_list(
							    $product_id,
							    ', ',
							    '<span class="posted_in">',
							    '</span>'
							);

	                        break;
	                    default:
	                        $meta_value = get_post_meta($product_id, $meta_key, true);
	                        echo esc_attr($meta_value);
	                        break;
	                }
	                ?>
	            </td>
	            <?php
	        }
	    }
	}

	/**
	 * Render the Product Table.
	 *
	 * This function retrieves settings, generates query arguments for products, renders the product table,
	 * and optionally initializes DataTables if specified in settings.
	 */
	protected function render() {
	    // Retrieve settings.
	    $settings = $this->get_settings();

	    // Determine the number of products to display.
	    $no_product = !empty($settings['products_count']) ? $settings['products_count'] : 4;

	    // Check if table should be sortable.
	    $make_table_sortable = !empty($settings['make_table_sortable']) && $settings['make_table_sortable'] === 'yes';

	    // Define table ID.
	    $table_id = 'ptew-product-table';

	    // Generate query arguments for products.
	    $args = $this->getProductQueryArgs($settings, $no_product);

	    // Retrieve products query.
	    $products_query = new \WP_Query($args);

	    // Render the product table.
	    $product_table_html = $this->renderProductTable($settings, $products_query, $table_id);

	    // Output the product table HTML.
	    echo wp_kses_post($product_table_html);

	    // Initialize DataTables if specified.
	    if ($make_table_sortable) {
	        $this->initializeDataTables($table_id);
	    }
	}

	/**
	 * Generate query arguments for retrieving products based on settings.
	 *
	 * This function generates query arguments for retrieving products based on the provided settings.
	 * It sets the post type, status, number of products per page, order, and orderby parameters.
	 * Additionally, it handles sorting by various options and filtering by product categories if specified.
	 *
	 * @param array $settings      The settings for generating the query arguments.
	 * @param int   $no_product    The number of products to retrieve.
	 *
	 * @return array $args The generated query arguments.
	 */
	protected function getProductQueryArgs($settings, $no_product) {
	    $args = [
	        'post_type'      => 'product',
	        'post_status'    => 'publish',
	        'posts_per_page' => $no_product,
	        'order'          => isset($settings['order']) ? $settings['order'] : 'desc',
	    ];

	    $orderby_options = [
	        'price'        => '_price',
	        'sales'        => 'total_sales',
	        'rated'        => '_wc_average_rating',
	        'sku'          => '_sku',
	        'stock_status' => '_stock_status',
	    ];

	    if (isset($settings['orderby']) && array_key_exists($settings['orderby'], $orderby_options)) {
	        $args['meta_key'] = $orderby_options[$settings['orderby']];
	        $args['orderby'] = 'meta_value_num';
	    } else {
	        $args['orderby'] = 'date';
	    }

	    if (!empty($settings['product_grid_categories'])) {
	        $args['tax_query'] = [
	            [
	                'taxonomy' => 'product_cat',
	                'field'    => 'slug',
	                'terms'    => $settings['product_grid_categories'],
	                'operator' => 'IN',
	            ],
	        ];
	    }

	    return $args;
	}


	protected function renderProductTable($settings, $products_query, $table_id) {
	    ob_start();
	    ?>
	    <!-- Start of the user table -->
	    <table id="<?php echo esc_attr($table_id); ?>" class="ptew-product-table-list">
	        <thead class="um-table-header">
	            <tr class="um-table-header-row">
	                <?php
	                // Check if user meta control is empty
	                if (!empty($settings['ptew_product_meta_control'])) {
	                    // Get only the labels from the user meta control settings
	                    $get_only_labels = array_column($settings['ptew_product_meta_control'], 'um_user_meta_field_value');

	                    // Iterate through the labels and display table headers
	                    foreach ($get_only_labels as $get_only_label) {
	                        echo "<th class='table-header-column'>";
	                        echo esc_html($get_only_label);
	                        echo "</th>";
	                    }
	                }
	                ?>
	            </tr>
	        </thead>

	        <tbody>
	            <?php if ($products_query->have_posts()) :
	                while ($products_query->have_posts()) :
	                    $products_query->the_post();
	                    $product_id = get_the_ID();
	                    ?>
	                    <tr class="um-table-row">
	                        <?php $this->render_woo_product_meta($settings, $product_id); ?>
	                    </tr>
	                <?php endwhile;
	                wp_reset_postdata();
	            endif; ?>
	        </tbody>
	    </table>
	    <!-- End of the user table -->
	    <?php
	    return ob_get_clean();
	}

	/**
	 * Initialize DataTables for a specific table.
	 *
	 * This function initializes DataTables on the specified table ID using jQuery.
	 * It sets the required options for DataTables to operate properly.
	 *
	 * @param string $table_id The ID of the table to initialize DataTables.
	 */
	protected function initializeDataTables($table_id) {
	    ?>
	    <!-- Start of JavaScript block to initialize DataTables -->
	    <script>
	        jQuery(document).ready(function($) {
	            // Initialize DataTables on the table with the given ID
	            $('#<?php echo esc_attr($table_id); ?>').dataTable({
	                "dom": 'rt',
	            });
	        });
	    </script>
	    <!-- End of JavaScript block -->
	    <?php
	}

	protected function content_template() {}
}

/**
 * Register the PRODUCT_ELEMENTOR_LIST_TABLE_MODULE widget with the Elementor widgets manager.
 *
 * @param object $widgets_manager The Elementor widgets manager instance.
 */
add_action('elementor/widgets/register', function ($widgets_manager) {
    $widgets_manager->register(new PRODUCT_ELEMENTOR_LIST_TABLE_MODULE());
});





	