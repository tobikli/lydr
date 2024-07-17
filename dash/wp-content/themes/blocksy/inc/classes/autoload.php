<?php

namespace Blocksy;

class ThemeAutoloader {

	/**
	 * Classes map.
	 *
	 * Maps Blocksy classes to file names.
     *
	 * @static
	 *
	 * @var array Classes used by blocksy.
	 */
	private static function get_classes_map() {
		return apply_filters('blocksy_theme_autoloader_classes_map', [
			'WordPressActionsManager' => 'inc/classes/trait-wordpress-actions-manager.php',

			'SearchModifications' => 'inc/components/search.php',

			'StringHelpers' => 'inc/classes/StringHelpers.php',

			'Database' => 'inc/classes/database.php',
			'DbVersioning' => 'inc/classes/theme-db-versioning.php',

			'DbVersioning\\CacheManager' => 'inc/classes/db-versioning/utils/cache-manager.php',

			'DbVersioning\\V200' => 'inc/classes/db-versioning/v2-0-0.php',
			'DbVersioning\\V202' => 'inc/classes/db-versioning/v2-0-2.php',
			'DbVersioning\\V203' => 'inc/classes/db-versioning/v2-0-3.php',
			'DbVersioning\\V209' => 'inc/classes/db-versioning/v2-0-9.php',
			'DbVersioning\\V2015' => 'inc/classes/db-versioning/v2-0-15.php',
			'DbVersioning\\V2019' => 'inc/classes/db-versioning/v2-0-19.php',
			'DbVersioning\\V2026' => 'inc/classes/db-versioning/v2-0-26.php',
			'DbVersioning\\V2027' => 'inc/classes/db-versioning/v2-0-27.php',
			'DbVersioning\\V2031' => 'inc/classes/db-versioning/v2-0-31.php',
			'DbVersioning\\V2034' => 'inc/classes/db-versioning/v2-0-34.php',
			'DbVersioning\\V2036' => 'inc/classes/db-versioning/v2-0-36.php',
			'DbVersioning\\V2038' => 'inc/classes/db-versioning/v2-0-38.php',
			'DbVersioning\\V2053' => 'inc/classes/db-versioning/v2-0-53.php',

			'DbVersioning\\DefaultValuesCleaner' => 'inc/classes/db-versioning/utils/db-default-values-cleaner.php',

			'Database\\SearchReplace' => 'inc/classes/db-versioning/utils/db-search-replace.php',
			'Database\\Utils' => 'inc/classes/db-versioning/utils/db-utils.php',
			'Database\\SearchReplacer' => 'inc/classes/db-versioning/utils/db-search-replacer.php',

			'FontsManager' => 'inc/css/fonts-manager.php',

			'WpHooksManager' => 'inc/classes/hooks-manager.php',

			'ArchiveLogic' => 'inc/components/archive/archive.php',

			'CustomPostTypes' => 'inc/integrations/custom-post-types.php',
			'ThemeDynamicCss' => 'inc/dynamic-css.php',

			'BreadcrumbsBuilder' => 'inc/components/breadcrumbs.php',

			'Sidebar' => 'inc/components/sidebar.php',
			'WooCommerce' => 'inc/components/woocommerce-integration.php',
			'WooDefaultPages' => 'inc/components/woocommerce/common/default-pages.php',

			'Blocks' => 'inc/components/blocks/blocks.php',
			'GutenbergBlock' => 'inc/components/blocks/gutenberg-block.php',

			'BlocksFallback' => 'inc/components/blocks/blocks-fallback.php',

			'LegacyWidgetsTransformer' => 'inc/components/blocks/legacy-widgets-transformer.php',
			'LegacyWidgetsPostsTransformer' => 'inc/components/blocks/legacy/legacy-posts-transformer.php',
			'LegacyWidgetsAboutMeTransformer' => 'inc/components/blocks/legacy/legacy-about-me-transformer.php',
			'LegacyWidgetsContactInfoTransformer' => 'inc/components/blocks/legacy/legacy-contact-info-transformer.php',
			'LegacyWidgetsSocialsTransformer' => 'inc/components/blocks/legacy/legacy-socials-transformer.php',
			'LegacyWidgetsAdvertisementTransformer' => 'inc/components/blocks/legacy/legacy-advertisement-transformer.php',
			'LegacyWidgetsNewsletterSubscribeTransformer' => 'inc/components/blocks/legacy/legacy-newsletter-subscribe.php',
			'LegacyWidgetsQuoteTransformer' => 'inc/components/blocks/legacy/legacy-quote-transformer.php',

			'Blocks\\BlockWrapper' => 'inc/components/blocks/block-wrapper/block.php',
			'Blocks\\BreadCrumbs' => 'inc/components/blocks/breadcrumbs/block.php',
			'Blocks\\Query' => 'inc/components/blocks/query/block.php',
			'Blocks\\DynamicData' => 'inc/components/blocks/dynamic-data/block.php',

			/**
			 * No namespace
			 */
			'_Blocksy_Css_Injector' => 'inc/classes/class-ct-css-injector.php',
		]);
	}

	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @static
	 */
	public static function run() {
		spl_autoload_register([__CLASS__, 'autoload']);
	}

	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @static
	 *
	 * @param string $relative_class_name Class name.
	 */
	private static function load_class($relative_class_name) {
		if (isset(self::get_classes_map()[$relative_class_name])) {
			$filename = get_template_directory() . '/' . self::get_classes_map()[$relative_class_name];
		} else {
			$filename = strtolower(
				preg_replace(
					['/([a-z])([A-Z])/', '/_/', '/\\\/'],
					['$1-$2', '-', DIRECTORY_SEPARATOR],
					$relative_class_name
				)
			);

			$filename = get_template_directory() . $filename . '.php';
		}

		if (is_readable($filename)) {
			require $filename;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exist and load it.
	 *
	 * @static
	 *
	 * @param string $class Class name.
	 */
	private static function autoload($class) {
		if (
			0 !== strpos($class, __NAMESPACE__ . '\\')
			&&
			! isset(self::get_classes_map()['_' . $class])
		) {
			return;
		}

		$relative_class_name = preg_replace('/^' . __NAMESPACE__ . '\\\/', '', $class);

		$final_class_name = __NAMESPACE__ . '\\' . $relative_class_name;

		if (isset(self::get_classes_map()['_' . $relative_class_name])) {
			$final_class_name = $relative_class_name;
			$relative_class_name = '_' . $relative_class_name;
		}

		if (! class_exists($final_class_name)) {
			self::load_class($relative_class_name);
		}
	}
}

