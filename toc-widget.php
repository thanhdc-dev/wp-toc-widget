<?php
/*
Plugin Name: TOC Widget
Plugin URI: 
Description: Plugin hiển thị widget mục lục.
Author: Thanhdc
Version: 1.0
Author URI: http://thanhdc.dev
*/

if (!class_exists('Thanhdc_TOC_Widget')) {
    class Thanhdc_TOC_Widget extends WP_Widget
    {
        protected $textdomain = 'toc-widget';
        protected $version = '2305';
        protected $options = [
            'title' => 'Mục lục',
            'heading_levels' => [1, 2, 3, 4, 5, 6],
            'container_id' => 'widget-toc',
            'container_class' => 'widget-toc',
            'item_class' => 'widget-toc__item',
        ];

        /**
         * Sets up the widgets name etc
         */
        public function __construct()
        {
            $widget_options = array(
                'description' => 'Display the table of contents in the sidebar with this widget',
            );

            parent::__construct('thanhdc_toc_widget', 'TOC', $widget_options);
            $this->options = get_option($this->textdomain, $this->options);

            $this->widgets_init();
            $this->load_styles();
            $this->load_scripts();
            $this->register_filter_auto_add_heading();
        }

        /**
         * Register Widget
         *
         * @return void
         */
        private function widgets_init()
        {
            add_action('widgets_init', function () {
                register_widget('Thanhdc_TOC_Widget');
            });
        }

        private function load_styles()
        {
            add_action('wp_enqueue_scripts', function () {
                if (is_singular() || is_page()) {
                    wp_enqueue_style($this->textdomain . "-style", plugins_url('/css/style.css', __FILE__));
                }
            });
        }

        private function load_scripts()
        {
            add_action('wp_enqueue_scripts', function () {
                if (is_singular() || is_page()) {
                    wp_enqueue_script($this->textdomain . '-script', plugins_url('/js/script.js', __FILE__), array(), $this->version, true);
                }
            });
        }

        /**
         * Register filter auto add heading after edit post content
         *
         * @return void
         */
        private function register_filter_auto_add_heading()
        {
            add_filter('the_content', function ($content) {
                // $pattern = '/<h([1-6])(.*?)>(.*?)<\/h[1-6]>/i';
                // $replacement = '<h$1 id="$3"$2>$3</h$1>';
                // $content = preg_replace($pattern, $replacement, $content);
                // return $content;

                // Tìm tất cả các heading trong nội dung bài viết
                preg_match_all('/<h([1-6])(.*?)>(.*?)<\/h[1-6]>/i', $content, $headings);

                // Duyệt qua từng heading để thêm id
                foreach ($headings[0] as $key => $value) {
                    $old_id = '';
                    preg_match('/id=(["\'])(.*?)\1/i', $value, $old_id);
                    // Nếu heading đã có id thì không cần phải thêm id mới
                    if (!empty($old_id[2])) {
                        continue;
                    }
                    // Tạo slug từ nội dung của heading
                    $slug = sanitize_title(strip_tags($headings[3][$key]));
                    // Thêm id mới vào heading
                    $new_heading = str_replace('<h' . $headings[1][$key], '<h' . $headings[1][$key] . ' id="' . $slug . '"', $value);
                    // Thay thế heading cũ bằng heading mới có id
                    $content = str_replace($value, $new_heading, $content);
                }

                return $content;
            });
        }

        /**
         * Outputs the content of the widget
         *
         * @param array $args
         * @param array $instance
         */
        public function widget($args, $instance)
        {
            global $post;
            $title = $instance['title'] ?? esc_html__($this->options['title'], $this->textdomain);
            $container_class = $instance['container_class'] ?? $this->options['container_class'];
            $item_class = $instance['item_class'] ?? $this->options['item_class'];
            $heading_levels = $instance['heading_levels'] ?? [];

            echo $args['before_widget'];
            if (!empty($instance['title'])) {
                echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
            }
            printf('<ul id="%s" class="widget-content %s">', $this->options['container_id'], $container_class . ' widget-toc');

            if (is_singular()) { // chỉ hiển thị trên các post và page
                $content = get_the_content();
                if (preg_match_all('/<h([1-6]).*?>(.*?)<\/h\1>/', $content, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        // remove undesired headings (if any) as defined by heading_levels
                        if (!in_array($match[1], $heading_levels)) {
                            continue;
                        }
                        $url = '#' . sanitize_title($match[2]);
                        $title = $match[2];
                        printf('<li class="%s"><a class="link" href="%s">%s</a></li>', $item_class . " widget-toc__item widget-toc__item--level-{$match[1]}", $url, $title);
                    }
                }
            }

            echo '</div>';
            echo $args['after_widget'];
        }

        /**
         * Outputs the options form on admin
         *
         * @param array $instance The widget options
         */
        public function form($instance)
        {
            $title = $instance['title'] ?? esc_html__($this->options['title'], $this->textdomain);
            $container_class = $instance['container_class'] ?? $this->options['container_class'];
            $item_class = $instance['item_class'] ?? $this->options['item_class'];
            $show_heading_levels = $instance['heading_levels'] ?? [];
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html__('Title:', $this->textdomain); ?></label>
                <input class="widget-input" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                    value="<?php echo esc_attr($title); ?>" style="width: 100%;">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('container_class')); ?>"><?php echo esc_html__('Container Class:', $this->textdomain); ?></label>
                <input class="widget-input" id="<?php echo esc_attr($this->get_field_id('container_class')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('container_class')); ?>" type="text"
                    value="<?php echo esc_attr($container_class); ?>" style="width: 100%;">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('item_class')); ?>"><?php echo esc_html__('Item Class:', $this->textdomain); ?></label>
                <input class="widget-input" id="<?php echo esc_attr($this->get_field_id('item_class')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('item_class')); ?>" type="text"
                    value="<?php echo esc_attr($item_class); ?>" style="width: 100%;">
            </p>
            <p>Heading levels: </p>
            <ul style="padding-left: 1.5rem;">
            <?php
            foreach ($this->options['heading_levels'] as $heading_level): ?>
                <li>
                    <input type="checkbox" class="widget-heading-level-checkbox"
                        id="<?php echo esc_attr($this->get_field_id('heading_level_' . $heading_level)); ?>"
                        name="<?php echo esc_attr($this->get_field_name('heading_levels')); ?>[]"
                        value="<?php echo esc_attr($heading_level); ?>" <?php echo in_array($heading_level, $show_heading_levels) ? 'checked' : '' ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('heading_level_' . $heading_level)); ?>"><?php echo esc_html__("Heading level {$heading_level}", $this->textdomain); ?></label>
                </li>
            <?php endforeach; ?>
            </ul>
            <?php
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance The new options
         * @param array $old_instance The previous options
         *
         * @return array
         */
        public function update($new_instance, $old_instance): array
        {
            $instance = $new_instance;

            // strip tags for title to remove HTML (important for text inputs)
            $instance['title'] = wp_strip_all_tags(trim($new_instance['title']));

            return $instance;
        }
    }
}

$toc = new Thanhdc_TOC_Widget();