

// Allow the AyeNav block to be added to the navigation and navigation submenu blocks.
(function() {
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;

	const ayecodeAllowedBlocksFilter = ( settings, name ) => {
		if ( name === 'core/navigation') {
			settings.allowedBlocks = [
				...( settings.allowedBlocks || [] ),
				'ayecode/ayecode-wp-nav',
			];
		}
		return settings;
	};

	addFilter(
		'blocks.registerBlockType',
		'ayecode/ayecode-wp-nav',
		ayecodeAllowedBlocksFilter
	);
})();
