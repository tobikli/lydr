<div class="hostinger hsr-banner-container">
	<div class="hsr-promotional-banner">
		<div class="hsr-promotional-banner-content">
			<div>
				<div class="hsr-onboarding__title"><?php echo esc_html__( 'Invite a Friend, Earn Up to $100', 'hostinger-easy-onboarding' ); ?></div>
				<p class="hsr-promotional-banner-description">
					<?php
					echo wp_kses(
						__(
							'Share your referral link with friends and family and <b>receive 20% commission</b> for every successful referral.',
							'hostinger-easy-onboarding'
						),
						array(
							'b' => array(), // Allowing only <b> tags
						)
					);
					?>
				</p>
			</div>
			<div class="hsr-buttons">
				<a class="hsr-btn hsr-purple-btn"
					href="<?php echo esc_url( $helper->get_promotional_link_url( get_locale() ) ); ?>"
					target="_blank"
					rel="noopener noreferrer"><?php echo esc_html__( 'Start earning', 'hostinger-easy-onboarding' ); ?></a>
				<svg class="close-btn"
					width="25"
					height="24"
					viewBox="0 0 25 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd"
							clip-rule="evenodd"
							d="M19.5 6.41L18.09 5L12.5 10.59L6.91 5L5.5 6.41L11.09 12L5.5 17.59L6.91 19L12.5 13.41L18.09 19L19.5 17.59L13.91 12L19.5 6.41Z"
							fill="#2F1C6A"/>
				</svg>
			</div>
		</div>
	</div>
</div>
