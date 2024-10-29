

<?php
/**
 * MoMo ACGWC - Page Getting Started
 *
 * @author MoMo Themes
 * @package momoacg
 * @since v1.2.2
 */

global $momoacgwc;
$assets = $momoacgwc->momoacgwc_assets . 'getting-started/';
?>
<style>
	body.ai-tools_page_momoacg-getting-started #wpcontent{
		padding: 0;
		background: #fff;
	}
	section.momo-be-getting-started{
		padding: 50px;
	}
	.momo-be-gs-header{
		text-align: center;
		padding: 50px 0;
	}
	.momo-be-gs-center{
		text-align: center;
	}
	.momo-be-gs-relative{
		position: relative;
	}
	h2.momo-be-gs-header-title{
		margin: 5px 0;
		font-size: 30px;
		font-weight: 700;
	}
	h3.momo-be-gs-header-subtitle{
		font-weight: 400;
	}
	.momo-be-gs-block{
		background: #eaf7ed;
		border-radius: 20px;
		display: table;
	}
	.momo-be-gs-block-column {
		display: flex;
		padding: 30px;
		background: #eaf7ed;
		border-radius: 20px;
	}
	.momo-be-gs-block-column img{
		max-width: 100%;
		height: auto;
	}
	.momo-be-gs-block-column-70 {
		flex: 70%;
	}

	.momo-be-gs-block-column-30 {
		flex: 30%;
		padding: 40px;
	}
	h2.momo-be-gs-block-header{
		font-size: 1.5rem;
		font-weight: 600;
		margin: 1rem 0;
		display: inline-flex;
		align-items: center;
	}
	.momo-be-gs-block-content p{
		font-size: 1rem;
		color: #000;
	}
	.momo-be-gs-container {
		display: flex;
		justify-content: space-between;
		margin-top: 50px;
	}

	.momo-be-gs-column-50 {
		flex-basis: calc(50% - 25px); /* 50% width minus gap */
		padding: 30px;
		background: #eaf7ed;
		border-radius: 20px;
		box-sizing: border-box;
		position: relative;
	}

	.momo-be-gs-column-gap {
		width: 50px; /* Width of the gap */
	}
	.momo-be-gs-mw-60{
		max-width: 60%!important;
	}
	.momo-va-center{
		display: flex;
		justify-content: center; /* Horizontal centering */
		align-items: center; /* Vertical centering */
	}
	.momo-fd-column{
		flex-direction: column;
	}
	.momo-mh-130{
		max-height: 130px;
		max-width: 100%;
	}
	.momo-gs-pro-tip{
		background-color: #b3edbd;
		padding: 1px 12px;
		color: #000;
		font-size: 14px;
		border-radius: 12px;
		position: absolute;
		right: 30px;
		top: 40px;
		font-weight: 600;
	}
	h2 > .momo-gs-pro-tip{
		right: -70px;
		top: -3px;
	}
	.momo-be-howto{
		text-align: center;
		margin-bottom: 50px;
	}
	.momo-be-howto span a{
		color: #dd5151;
		text-decoration: none;
		font-size: 16px;
	}
	.momo-be-howto span.hts a{
		font-weight: 800;
		font-size: 18px;
	}
	.momo-be-howto span{
		display: block;
	}
	/* Responsive video container */
	.momo-be-video-container {
		position: relative;
		width: 100%;
		padding-bottom: 56.25%; /* 16:9 aspect ratio (height: 9/16 * width) */
		overflow: hidden;
		margin: 50px 30px;
		margin-bottom: 0;
	}

	/* Responsive video iframe */
	.momo-be-video-container iframe {
		position: absolute;
		top: 0;
		left: 0;
		width: 95%;
		height: 95%;
	}
	.momo-be-gs-mb-50{
		margin-bottom: 50px;
	}

</style>
<section class="momo-be-getting-started">
	<div class="momo-be-gs-header">
		<h2 class="momo-be-gs-header-title">
			<?php esc_html_e( 'Getting Started', 'momoacgwc' ); ?>
		</h2>
	</div>
	<div class="momo-be-gs-block">
		<div class="momo-be-gs-block-column">
			<div class="momo-be-gs-block-column-70">
				<h2 class="momo-be-gs-block-header">
					<?php esc_html_e( 'AI for WooCommerce', 'momoacgwc' ); ?>
				</h2>
				<div class="momo-be-gs-block-content">
					<p>
						<?php esc_html_e( 'An advanced WordPress plugin leveraging artificial intelligence offers multiple features to enhance your WooCommerce store. It automatically writes engaging and SEO-optimized product descriptions, saving time and ensuring consistency across your product listings. Additionally, the plugin includes a conversational AI chatbot capable of human-like interactions. This chatbot can be trained with custom data specific to your business, providing personalized and accurate responses to customer queries, improving customer support and engagement.', 'momoacgwc' ); ?>
					</p>
					<p>
						<?php esc_html_e( 'Furthermore, the plugin delivers tailored product recommendations by analyzing user behavior, including browsing history and purchase patterns. This personalization ensures customers are presented with products that match their interests, significantly increasing the likelihood of sales. The integration of AI-driven descriptions, a smart chatbot, and personalized recommendations creates a seamless, efficient, and user-friendly shopping experience, boosting customer satisfaction and driving higher conversion rates. This comprehensive AI solution not only enhances the functionality of your WooCommerce store but also optimizes the overall shopping journey, leading to increased sales and customer loyalty.', 'momoacgwc' ); ?>
					</p>
				</div>
			</div>
			<div class="momo-be-gs-block-column-30 momo-be-gs-center momo-va-center momo-fd-column">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'ai3.png' ); ?>" alt="AI" class="momo-mh-130" />
				</div>
			</div>
		</div>
		<div class="momo-be-howto">
			<span class="hts">
				<a href="http://momothemes.com/documentationWooContentFree/" target="_blank"><?php esc_html_e( 'How to start ?', 'momoacgwc' ); ?></a>
			</span>
			<span>
				<a href="http://momothemes.com/documentationWooContentFree/" target="_blank"><?php esc_html_e( 'Read Documentation', 'momoacgwc' ); ?></a>
			</span>
		</div>
	</div>
	<div class="momo-be-video-container">
		<?php
		$src        = 'https://www.youtube.com/watch?v=P6DTghU5J4U';
		$embed_html = wp_oembed_get( $src );
		echo $embed_html; // phpcs:ignore
		?>
	</div>
	<div class="momo-be-gs-header">
		<h2 class="momo-be-gs-header-title">
			<?php esc_html_e( 'Unique Features', 'momoacgwc' ); ?>
		</h2>
		<h3 class="momo-be-gs-header-subtitle">
			<?php esc_html_e( 'These are some useful features of the plugin', 'momoacgwc' ); ?>
		</h3>
	</div>
	<div class="momo-be-gs-container momo-be-gs-center momo-be-gs-mb-50">
		<div class="momo-be-gs-column-50">
			<h2 class="momo-be-gs-block-header">
				<?php esc_html_e( 'Auto Blogging', 'momoacgwc' ); ?>
			</h2>
			<div class="momo-be-gs-block-content">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'autoblog.png' ); ?>" alt="autoblog" class="momo-mh-130" />
				</div>
				<p>
					<?php esc_html_e( 'Generate high-quality blog posts tailored to your needs. Simply input your topic, keywords, and desired tone, and the AI will create unique, SEO-friendly content in minutes. It researches relevant information, structures the content logically, and ensures readability for your audience. With customizable templates and the ability to schedule posts, the program saves you time and effort while keeping your blog active with fresh content. Ideal for businesses and individuals looking to enhance their content marketing without the hassle of manual writing.', 'momoacgwc' ); ?>
				</p>
			</div>
		</div>
		<div class="momo-be-gs-column-gap"></div>
		<div class="momo-be-gs-column-50">
			<h2 class="momo-be-gs-block-header">
				<?php esc_html_e( 'Sales Mail By User Search Term', 'momoacgwc' ); ?>
			</h2>
			<div class="momo-be-gs-block-content">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'automail.png' ); ?>" alt="automail" class="momo-mh-130" />
				</div>
				<p>
					<?php esc_html_e( "This plugin will automatically generate personalized sales letters based on the keywords or terms users search for. By analyzing search intent and preferences, it crafts targeted, persuasive copy designed to resonate with potential buyers. It generates a tailored sales message that highlights relevant benefits and includes a strong call-to-action. Ideal for boosting conversions, this feature saves time while delivering optimized, high-impact sales letters that speak directly to the user's needs and interests.", 'momoacgwc' ); ?>
				</p>
			</div>
		</div>
	</div>
	<div class="momo-be-gs-block">
		<div class="momo-be-gs-block-column">
			<div class="momo-be-gs-block-column-70">
				<h2 class="momo-be-gs-block-header momo-be-gs-relative">
					<span class="momo-gs-pro-tip"><?php esc_html_e( 'Pro', 'momoacgwc' ); ?></span>
					<?php esc_html_e( 'ChatBot Powered By Artificial Intelligence', 'momoacgwc' ); ?>
				</h2>
				<div class="momo-be-gs-block-content">
					<p>
						<?php esc_html_e( 'A chatbot with artificial intelligence capable of engaging users in human-like conversations, enhancing user experience on your site. Utilizing advanced AI technologies, it understands and responds to natural language, making interactions seamless and intuitive. This chatbot can be customized and trained with specific data relevant to your business or content, allowing it to provide accurate and contextually appropriate responses. By integrating this AI-powered feature, your WordPress site can offer personalized assistance, answer queries, guide users through processes, and improve overall engagement, all while continuously learning and adapting to better meet your audience\'s needs.', 'momoacgwc' ); ?>
					</p>
				</div>
			</div>
			<div class="momo-be-gs-block-column-30 momo-be-gs-center momo-va-center">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'chatbotair.png' ); ?>" alt="chat-bot" />
				</div>
			</div>
		</div>
	</div>
	<div class="momo-be-gs-container momo-be-gs-center">
		<div class="momo-be-gs-column-50">
			<h2 class="momo-be-gs-block-header">
				<?php esc_html_e( 'Product Description Writer', 'momoacgwc' ); ?>
			</h2>
			<div class="momo-be-gs-block-content">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'writer.png' ); ?>" alt="writer" class="momo-mh-130" />
				</div>
				<p>
					<?php esc_html_e( "A feature of the plugin that automatically generates descriptions for WooCommerce products and creates related images leveraging AI technology to enhance the e-commerce experience. This plugin wil analyze the product's attributes, such as title, category, and specifications, to generate a compelling and SEO-friendly description. Additionally, using AI-driven image generation, it can create high-quality, relevant images based on the product's description and characteristics. This will save time for store owners and ensure consistency and attractiveness in product listings.", 'momoacgwc' ); ?>
				</p>
			</div>
		</div>
		<div class="momo-be-gs-column-gap"></div>
		<div class="momo-be-gs-column-50">
			<h2 class="momo-be-gs-block-header">
				<?php esc_html_e( 'Auto recommendation', 'momoacgwc' ); ?>
			</h2>
			<div class="momo-be-gs-block-content">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'recommend.png' ); ?>" alt="recommend" class="momo-mh-130" />
				</div>
				<p>
					<?php esc_html_e( 'Automatically display products based on customer interests and history. Enhance user experience and boost sales. By analyzing visitor behavior, such as browsing history, previous purchases, and interaction patterns, the plugin tailors product recommendations to each individual. This personalized approach ensures that users see items that are most relevant to their preferences, increasing the likelihood of conversions. By delivering a customized shopping experience, it not only increases customer satisfaction but also drives higher sales and fosters customer loyalty.', 'momoacgwc' ); ?>
				</p>
			</div>
		</div>
	</div>
	<div class="momo-be-gs-container momo-be-gs-center">
		<div class="momo-be-gs-column-50">
			<h2 class="momo-be-gs-block-header">
				<?php esc_html_e( 'Documentation', 'momoacgwc' ); ?>
			</h2>
			<div class="momo-be-gs-block-content">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'documentation.png' ); ?>" alt="documentation" class="momo-mh-130" />
				</div>
				<p>
					<?php esc_html_e( 'We have comprehensive documentation, serving as a valuable resource for users. This documentation provides detailed information on installation, configuration, and usage, offering clear guidelines and troubleshooting tips. ', 'momoacgwc' ); ?>
				</p>
			</div>
		</div>
		<div class="momo-be-gs-column-gap"></div>
		<div class="momo-be-gs-column-50">
			<h2 class="momo-be-gs-block-header">
				<?php esc_html_e( 'HelpDesk', 'momoacgwc' ); ?>
			</h2>
			<div class="momo-be-gs-block-content">
				<div class="momo-be-gs-row">
					<img src="<?php echo esc_url( $assets . 'helpdesk.png' ); ?>" alt="helpdesk" class="momo-mh-130" />
				</div>
				<p>
					<?php esc_html_e( "The plugin provides a dedicated helpdesk for customer support, offering users a direct avenue to seek assistance. This ensures prompt and reliable support for any queries or issues, enhancing user experience and providing a reliable resource for addressing concerns related to the plugin's functionality and features.", 'momoacgwc' ); ?>
				</p>
			</div>
		</div>
	</div>
</section>
