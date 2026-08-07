<?php
/**
 * First-activation content seeding: creates the site's pages (Home, About,
 * Our Services, Our Articles, Contact) with the right templates, imports
 * the client-supplied photo and logo, and populates the 8 practice-area
 * pages with the approved copy so the theme is a working, real site the
 * moment it's activated — not a lorem-ipsum shell.
 *
 * Everything here is idempotent: it only ever CREATES missing content. It
 * never overwrites a page/post that already exists, so re-activating the
 * theme (or switching away and back) can't clobber a client's edits.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_activate() {
	// register_post_type() has already run earlier in this request (init
	// fires before after_switch_theme), but calling again is harmless and
	// removes any doubt about ordering.
	sbvi_register_service_cpt();
	sbvi_register_article_cpt();
	sbvi_register_testimonial_cpt();

	$image_id = sbvi_seed_default_images();

	sbvi_seed_pages( $image_id );
	sbvi_seed_services( $image_id );
	sbvi_seed_testimonials();
	sbvi_seed_articles( $image_id );

	flush_rewrite_rules();

	set_transient( 'sbvi_activation_notice', 1, 60 );
}
add_action( 'after_switch_theme', 'sbvi_activate' );

function sbvi_activation_notice() {
	if ( ! get_transient( 'sbvi_activation_notice' ) ) {
		return;
	}
	delete_transient( 'sbvi_activation_notice' );
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Sinclairs (BVI) theme activated.', 'sinclairs-bvi' ); ?></strong>
			<?php esc_html_e( 'Home, About, Our Services, Our Articles and Contact pages, the 8 practice areas, the 3 client testimonials and the 6 Legal Insights articles have been created and populated from the approved copy and the live site.', 'sinclairs-bvi' ); ?>
			<a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>"><?php esc_html_e( 'Visit Settings → Permalinks and click Save once', 'sinclairs-bvi' ); ?></a>
			<?php esc_html_e( 'if any practice-area or article link 404s.', 'sinclairs-bvi' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'sbvi_activation_notice' );

/* ---------------------------------------------------------------------- */
/* Block-content helpers                                                  */
/* ---------------------------------------------------------------------- */

function sbvi_block_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->";
}

function sbvi_block_heading( $text, $level = 3 ) {
	return "<!-- wp:heading {\"level\":{$level}} -->\n<h{$level}>" . $text . "</h{$level}>\n<!-- /wp:heading -->";
}

function sbvi_block_list( array $items ) {
	$lis = '';
	foreach ( $items as $item ) {
		$lis .= '<li>' . $item . '</li>';
	}
	return "<!-- wp:list {\"className\":\"sbvi-coverage-grid\"} -->\n<ul class=\"wp-block-list sbvi-coverage-grid\">{$lis}</ul>\n<!-- /wp:list -->";
}

function sbvi_block_quote( $text ) {
	return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>" . $text . '</p></blockquote>' . "\n<!-- /wp:quote -->";
}

/* ---------------------------------------------------------------------- */
/* Media                                                                  */
/* ---------------------------------------------------------------------- */

function sbvi_seed_image_from_theme_file( $relative_path, $title, $alt ) {
	$file_path = SBVI_DIR . '/' . ltrim( $relative_path, '/' );
	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$contents = file_get_contents( $file_path );
	if ( false === $contents ) {
		return 0;
	}

	$upload = wp_upload_bits( basename( $file_path ), null, $contents );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$filetype   = wp_check_filetype( basename( $upload['file'] ), null );
	$attach_id  = wp_insert_attachment( array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $upload['file'] );

	if ( ! $attach_id || is_wp_error( $attach_id ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );

	return $attach_id;
}

/**
 * Imports the client logo (set as the site custom logo) and the one
 * client-supplied harbour photo (set as the site-wide default/fallback
 * photo — see README for the "reused placeholder" note). Returns the
 * harbour photo's attachment ID for reuse while seeding services/pages.
 */
function sbvi_seed_default_images() {
	if ( ! get_theme_mod( 'custom_logo' ) ) {
		$logo_id = sbvi_seed_image_from_theme_file(
			'assets/images/sinclairs-logo.png',
			'Sinclairs (BVI) logo',
			'Sinclairs (BVI) — Lawyers and Notaries Public'
		);
		if ( $logo_id ) {
			set_theme_mod( 'custom_logo', $logo_id );
		}
	}

	$existing = (int) get_theme_mod( 'sbvi_default_banner_image' );
	if ( $existing && wp_attachment_is_image( $existing ) ) {
		return $existing;
	}

	$photo_id = sbvi_seed_image_from_theme_file(
		'assets/images/bvi-harbour.jpg',
		'BVI harbour',
		'A Tortola shoreline and harbour view'
	);
	if ( $photo_id ) {
		set_theme_mod( 'sbvi_default_banner_image', $photo_id );
	}

	return $photo_id;
}

/* ---------------------------------------------------------------------- */
/* Pages                                                                  */
/* ---------------------------------------------------------------------- */

/**
 * @return array{id:int,created:bool}
 */
function sbvi_get_or_create_page( $title, $slug, $template, $content, $excerpt = '' ) {
	$existing = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
		'no_found_rows'  => true,
	) );

	if ( $existing ) {
		return array( 'id' => $existing[0]->ID, 'created' => false );
	}

	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
	) );

	if ( ! $id || is_wp_error( $id ) ) {
		return array( 'id' => 0, 'created' => false );
	}

	update_post_meta( $id, '_wp_page_template', $template );

	return array( 'id' => $id, 'created' => true );
}

function sbvi_seed_pages( $image_id ) {
	// Home.
	$home_content = implode( "\n\n", array(
		sbvi_block_p( "Our approach is personal, thorough and solutions-oriented, with an emphasis on clear communication throughout each engagement." ),
		sbvi_block_p( 'Whether you are establishing or restructuring a business, launching or operating an investment fund, protecting intellectual property, planning for succession, winding up a company or navigating regulatory requirements, Sinclairs (BVI) offers dependable guidance at every stage.' ),
	) );
	$home = sbvi_get_or_create_page( 'Home', 'home', 'front-page.php', $home_content );
	if ( $home['created'] && $home['id'] ) {
		foreach ( sbvi_home_fields() as $group ) {
			foreach ( $group['fields'] as $key => $field ) {
				update_post_meta( $home['id'], $key, $field['default'] );
			}
		}
		if ( $image_id ) {
			set_post_thumbnail( $home['id'], $image_id );
			update_post_meta( $home['id'], '_sbvi_secondary_image', $image_id );
		}
		if ( '' === (string) get_option( 'show_on_front' ) || 'page' !== get_option( 'show_on_front' ) || ! (int) get_option( 'page_on_front' ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home['id'] );
		}
	}

	// About.
	$about_content = implode( "\n\n", array(
		sbvi_block_p( 'Sinclairs (BVI) is a British Virgin Islands law firm providing clear, practical and commercially focused legal services to BVI legal entities (regulated and unregulated), financial institutions, trust and corporate services providers and individuals in the BVI and internationally.' ),
		sbvi_block_p( 'We recognise that every client and every matter is different. We, therefore, take the time to understand our clients’ objectives and provide advice that is responsive to their particular circumstances. Our approach is personal, thorough and solutions-oriented, with an emphasis on clear communication throughout each engagement.' ),
		sbvi_block_p( 'Whether you are establishing or restructuring a business, launching or operating an investment fund, protecting intellectual property, planning for succession, winding up a company or navigating regulatory requirements, Sinclairs (BVI) offers dependable guidance at every stage.' ),
		sbvi_block_quote( 'Our aim is straightforward: to provide sound legal advice, attentive service and practical solutions that help our clients move forward with confidence.' ),
		sbvi_block_p( 'Our practice includes corporate and commercial law, investment funds, regulatory and compliance matters, banking and corporate finance, commercial and residential property matters, and estate planning.' ),
		sbvi_block_p( 'We draw on our knowledge of the BVI’s legal and regulatory landscape to help clients address complex issues, manage risk and make well-informed decisions.' ),
		sbvi_block_heading( 'Community Service', 2 ),
		sbvi_block_p( 'Our commitment to the BVI extends beyond our legal practice. We believe in giving our time and experience to initiatives that support young people and strengthen local institutions.' ),
		sbvi_block_p( 'One of our lawyers holds recognised Concacaf football coaching licences and dedicates Saturday mornings to coaching young players, over two consecutive seasons, coached a school’s U7 team and its U9 team in the 2024–25 and the 2025–26 BVI BDO Schools Football League. A member of our team has also served on the Board of Governors of H. Lavity Stoutt Community College, contributing time and professional experience to the College’s governance.' ),
	) );
	$about = sbvi_get_or_create_page( 'About Sinclairs (BVI)', 'about', 'page-about.php', $about_content );
	if ( $about['created'] && $about['id'] && $image_id ) {
		set_post_thumbnail( $about['id'], $image_id );
		update_post_meta( $about['id'], '_sbvi_secondary_image', $image_id );
	}

	// Our Services (hub).
	$services = sbvi_get_or_create_page(
		'Our Services',
		'services',
		'page-services.php',
		'',
		'Providing trusted guidance and personalised support for legal matters in the British Virgin Islands, as follows.'
	);
	if ( $services['created'] && $services['id'] && $image_id ) {
		set_post_thumbnail( $services['id'], $image_id );
	}

	// Our Articles (hub).
	$articles = sbvi_get_or_create_page(
		'Our Articles',
		'articles',
		'page-articles.php',
		'',
		'Our own posts on legislative change, regulatory practice and what it means for entities doing business through the British Virgin Islands.'
	);
	if ( $articles['created'] && $articles['id'] && $image_id ) {
		set_post_thumbnail( $articles['id'], $image_id );
	}

	// Contact.
	$contact_content = sbvi_block_p( 'Tell us what you are working on and we will come back to you promptly.' );
	$contact = sbvi_get_or_create_page( 'Contact', 'contact', 'page-contact.php', $contact_content );
	if ( $contact['created'] && $contact['id'] && $image_id ) {
		set_post_thumbnail( $contact['id'], $image_id );
	}
}

/* ---------------------------------------------------------------------- */
/* Services (the 8 practice areas)                                        */
/* ---------------------------------------------------------------------- */

function sbvi_services_data() {
	return array(
		array(
			'slug'    => 'corporate-commercial',
			'title'   => 'Corporate & Commercial Law',
			'order'   => 1,
			'dek'     => 'Practical advice across the full life cycle of a BVI entity — from formation and financing through to restoration and continuation.',
			'nutshell'=> 'Structuring and restructuring entities, contracts, cross-border transactions, acquisitions, continuations and company restorations.',
			'body'    => array(
				sbvi_block_p( 'Sinclairs (BVI) provides practical advice across the full life cycle of a BVI entity. We help clients structure and restructure companies and other corporate vehicles, advise on financing and commercial contracts, advise on acquisitions, cross-border transactions, and assist with restoration of struck-off or dissolved companies to the Register.' ),
				sbvi_block_p( 'We also advise on the continuation of companies into and out of the BVI and prepare bespoke memoranda and articles of association for BVI companies whose ownership, governance or business requirements are not adequately addressed by standard constitutional documents.' ),
				sbvi_block_p( 'Our banking and corporate finance work includes advising on financing transactions, security over shares and other assets, and the registration of charges. We also issue BVI legal opinions on transactions, including opinions on a BVI entity’s capacity, power and authority, and on the validity and enforceability of the contracts and deeds into which it enters, and opinions on BVI law for proceedings before foreign courts.' ),
				sbvi_block_p( 'We provide directorship services to licensed entities and advise BVI companies and limited partnerships on their economic substance obligations. Where necessary, we assist clients with submissions to and enquiries from the BVI International Tax Authority.' ),
				sbvi_block_p( 'Our commercial property practice covers leases, acquisitions and development projects, as well as the legal aspects of property ownership and management in the BVI.' ),
			),
			'faqs'    => array(
				array( 'question' => 'Do you advise on cross-border transactions involving BVI entities?', 'answer' => 'Yes. We provide BVI corporate and commercial support for cross-border acquisitions, investments, joint ventures, financings and restructurings. We work with clients and overseas counsel to review transaction documents, prepare corporate approvals, register security and address the BVI aspects of the transaction through to completion.' ),
				array( 'question' => 'Can a struck-off or dissolved BVI company be restored?', 'answer' => 'Yes. Depending on the circumstances, restoration may be obtained through the Registrar of Corporate Affairs or by application to the Court. We advise on the appropriate route and handle the process.' ),
				array( 'question' => 'Do you issue BVI legal opinions?', 'answer' => 'Yes. We issue opinions on specific BVI legal matters and on the capacity and authority of BVI entities, as well as the validity and enforceability of transaction documents.' ),
			),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'Company & Commercial', 'description' => 'Structuring or restructuring corporate entities, advising on contracts, cross-jurisdictional transactions, advising on acquisitions, company restorations. Continuations into and out of the BVI. Drafting special memorandum and articles of association for BVI companies.' ),
				array( 'group' => 'corporate', 'label' => 'Directorship', 'description' => 'Providing directorship services to licensed entities.' ),
			),
		),
		array(
			'slug'    => 'investment-funds',
			'title'   => 'Investment Funds, Approved Managers & Investment Business',
			'order'   => 2,
			'dek'     => 'Structuring, establishing and obtaining regulatory approval for BVI funds and managers under the Securities and Investment Business Act, 2010.',
			'nutshell'=> 'Establishing BVI funds, Approved Managers, SIBA licences and authorised representative services.',
			'body'    => array(
				sbvi_block_heading( 'Investment Funds' ),
				sbvi_block_p( 'Sinclairs (BVI) advises investment managers, fund promoters and onshore counsel on structuring and establishing investment funds in the British Virgin Islands under the Securities and Investment Business Act, 2010 (“SIBA”).' ),
				sbvi_block_p( 'We advise on hedge funds, private equity funds and other investment structures formed as BVI business companies, segregated portfolio companies, limited partnerships or unit trusts. Our work includes establishing and obtaining regulatory recognition or approval of private funds, professional funds, incubator funds, approved funds and private investment funds, including qualifying closed-ended structures. We also establish segregated portfolio companies and assist existing funds wishing to convert to SPC status.' ),
				sbvi_block_heading( 'Approved Managers' ),
				sbvi_block_p( 'The BVI Approved Managers regime offers a practical regulatory framework for qualifying investment managers and investment advisers. We advise clients on establishing Approved Managers in the BVI and guide them through the application process, including the preparation and submission of applications and supporting documents to the BVI Financial Services Commission.' ),
				sbvi_block_heading( 'Investment Business Licensing' ),
				sbvi_block_p( 'We assist investment managers, fund administrators and other service providers with applications for licences under SIBA. This includes applications relating to businesses dealing in contracts for differences and other regulated investment products and activities.' ),
				sbvi_block_heading( 'Authorised Representative Services' ),
				sbvi_block_p( 'Sinclairs (BVI) also provides Authorised Representative services to regulated funds and licensees. We act as the principal liaison between our clients and the BVI Financial Services Commission and assist with regulatory submissions and continuing obligations.' ),
			),
			'faqs'    => array(
				array( 'question' => 'What type of funds do you assist with establishing in the BVI?', 'answer' => 'We assist with establishing private, professional, incubator, approved and private investment funds, including hedge funds and private equity funds. These may be structured as BVI business companies, segregated portfolio companies, limited partnerships or unit trusts.' ),
				array( 'question' => 'Do you assist with regulatory and licence applications to the BVI Financial Services Commission?', 'answer' => "Yes. We advise on and prepare applications to the BVI Financial Services Commission under SIBA. These include applications for the recognition or approval of private, professional, incubator, approved and private investment funds, as well as applications relating to Approved Managers and segregated portfolio companies.\n\nWe also assist investment managers, fund administrators and other businesses seeking licences to conduct regulated investment business, including activities involving contracts for differences." ),
				array( 'question' => 'What is an Approved Manager?', 'answer' => 'An Approved Manager is a BVI investment manager or adviser operating under a lighter-touch regulatory regime. It offers eligible managers a simpler route to approval than applying for a full investment-business licence under SIBA. Approved Managers remain regulated by the BVI Financial Services Commission and must comply with ongoing requirements and prescribed business limits.' ),
			),
			'nutshell_items' => array(
				array( 'group' => 'funds', 'label' => 'Investment Funds', 'description' => 'Structuring and establishing hedge funds, private equity funds and other investment structures under SIBA, including private, professional, incubator, approved and private investment funds, and segregated portfolio companies.' ),
				array( 'group' => 'funds', 'label' => 'Approved Managers', 'description' => 'Establishing Approved Managers in the BVI and guiding qualifying investment managers and advisers through the application process with the BVI Financial Services Commission.' ),
				array( 'group' => 'funds', 'label' => 'Investment Business Licensing', 'description' => 'Assisting investment managers, fund administrators and other service providers with applications for licences under SIBA, including contracts for differences and other regulated activities.' ),
				array( 'group' => 'funds', 'label' => 'Authorised Representative Services', 'description' => 'Acting as the principal liaison between regulated funds and licensees and the BVI Financial Services Commission, and assisting with regulatory submissions and continuing obligations.' ),
			),
		),
		array(
			'slug'    => 'banking-finance',
			'title'   => 'Banking & Corporate Finance',
			'order'   => 3,
			'dek'     => 'Advising on corporate financing, registration of charges and security, and issuing BVI legal opinions.',
			'nutshell'=> 'Corporate financing, registration of charges and security, and BVI legal opinions.',
			'body'    => array(
				sbvi_block_p( 'Our banking and corporate finance work includes advising on financing transactions, security over shares and other assets, and the registration of charges.' ),
				sbvi_block_p( 'We issue BVI legal opinions on transactions, including opinions on a BVI entity’s capacity, power and authority, and on the validity and enforceability of the contracts and deeds into which it enters, and opinions on BVI law for proceedings before foreign courts.' ),
				sbvi_block_p( 'We act for lenders and borrowers alike, and work alongside overseas counsel to address the BVI aspects of a financing through to completion.' ),
			),
			'faqs'    => array(),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'Banking & Corporate Finance', 'description' => 'Advising on corporate financing, registration of charges and security, and issuing legal opinions (whether on specific BVI legal issues for foreign courts or on the validity, capacity, power and enforceability of contracts and deeds entered into by BVI entities).' ),
			),
		),
		array(
			'slug'    => 'aml-cft-compliance',
			'title'   => 'AML/CFT and Regulatory Compliance',
			'order'   => 4,
			'dek'     => 'Effective compliance is more than having a manual on file. It requires policies, procedures and controls that reflect how a business actually operates.',
			'nutshell'=> 'Manuals, risk assessments and controls that reflect how a business actually operates.',
			'body'    => array(
				sbvi_block_p( 'Sinclairs (BVI) advises regulated entities and other relevant persons on the BVI framework for anti-money laundering, countering the financing of terrorism and countering proliferation financing (“AML/CFT/CPF”). We help clients understand regulatory changes and translate their obligations into clear, workable procedures.' ),
				sbvi_block_p( 'We prepare and update AML/CFT/CPF compliance manuals tailored to each client’s regulatory status, business model, customers, services, operating jurisdictions and risk profile.' ),
				sbvi_block_p( 'Our advice covers:' ),
				sbvi_block_list( array( 'Governance', 'Institutional and customer risk assessments', 'Customer due diligence', 'Ongoing monitoring', 'Internal reporting', 'Sanctions controls', 'Record-keeping', 'Staff training' ) ),
			),
			'faqs'    => array(
				array( 'question' => 'Can you prepare or update our compliance manual?', 'answer' => 'Yes. We prepare practical, risk-based manuals tailored to each client’s business, customers, services and operating jurisdictions.' ),
			),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'AML/CFT & Regulatory Compliance', 'description' => 'Advises regulated entities and other relevant persons on the BVI framework for anti-money laundering, countering the financing of terrorism and countering proliferation financing (“AML/CFT/CPF”).' ),
			),
		),
		array(
			'slug'    => 'virtual-assets-fintech',
			'title'   => 'Virtual Assets and FinTech',
			'order'   => 5,
			'dek'     => 'The British Virgin Islands has emerged as a leading jurisdiction for digital and virtual asset businesses.',
			'nutshell'=> 'VASP registration, digital asset funds and blockchain-based products under VASPA and SIBA.',
			'body'    => array(
				sbvi_block_p( 'We provide comprehensive legal advice to entities seeking to operate as Virtual Asset Service Providers (VASPs) in the BVI, navigating the requirements under the Virtual Assets Service Providers Act, 2022 (VASPA).' ),
				sbvi_block_p( 'Whether you are launching a cryptocurrency exchange, a digital asset fund, a blockchain-based financial product, or a Web3 platform, we provide the legal foundation you need to operate with confidence in the BVI. We also advise on digital asset funds and blockchain-based financial products, including whether SIBA or other BVI financial services legislation may apply. From initial structuring through registration and launch, we provide clear, practical guidance at each stage.' ),
				sbvi_block_p( 'If needed, we prepare applications for registration as a Virtual Asset Service Provider with the BVI Financial Services Commission. We also advise on corporate structuring, governance, ongoing regulatory obligations and the anti-money laundering, counter-terrorist financing and counter-proliferation financing requirements applicable to virtual asset businesses.' ),
			),
			'faqs'    => array(
				array( 'question' => 'What activities may require VASP registration in the BVI?', 'answer' => 'VASP registration may be required for businesses providing virtual asset exchange, transfer, custody or administration services, or certain financial services connected with the offer or sale of virtual assets.' ),
				array( 'question' => 'Does every digital asset business require VASP registration?', 'answer' => 'No. Registration depends on the services the business provides. However, SIBA or other BVI legislation may apply even where the business is not required to register as a VASP.' ),
				array( 'question' => 'Can Sinclairs (BVI) assist with a VASP registration application?', 'answer' => 'Yes. We assess the proposed business model, advise on structure and compliance, prepare the registration application and supporting documents, and liaise with the BVI Financial Services Commission throughout the process.' ),
			),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'Digital or Virtual Assets and FinTech', 'description' => 'Advising on VASP and other digital and cryptocurrency issues.' ),
			),
		),
		array(
			'slug'    => 'economic-substance',
			'title'   => 'Economic Substance',
			'order'   => 6,
			'dek'     => 'Advising BVI entities on the economic substance law and dealing with the International Tax Authority, if required.',
			'nutshell'=> 'Advising BVI entities on the economic substance law and dealing with the ITA, if required.',
			'body'    => array(
				sbvi_block_p( 'We advise BVI companies and limited partnerships on their economic substance obligations — whether an entity carries on a relevant activity, what the requirements mean for its governance and operations, and how they interact with its wider structure.' ),
				sbvi_block_p( 'Where necessary, we assist clients with submissions to and enquiries from the BVI International Tax Authority.' ),
			),
			'faqs'    => array(),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'Economic Substance', 'description' => 'Advising BVI entities on the economic substance law and dealing with the ITA, if required.' ),
			),
		),
		array(
			'slug'    => 'property-private-client',
			'title'   => 'Property & Private Client',
			'order'   => 7,
			'dek'     => 'Commercial and residential property, and the planning that protects what a family has built.',
			'nutshell'=> 'Commercial and residential conveyancing, wills, trust deeds, probate and lasting powers of attorney.',
			'body'    => array(
				sbvi_block_heading( 'Commercial Property' ),
				sbvi_block_p( 'Handling commercial leases, land acquisitions, development projects, and property management, as well as the legal aspects of property ownership in the BVI.' ),
				sbvi_block_heading( 'Residential Conveyancing' ),
				sbvi_block_p( 'Advising on the purchase and transfer of residential and commercial property.' ),
				sbvi_block_heading( 'Wills, Probate & Estate Planning' ),
				sbvi_block_p( 'Crafting wills, trust deeds and lasting powers of attorney, and guiding families through probate.' ),
			),
			'faqs'    => array(),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'Commercial Property', 'description' => 'Handling commercial leases, land acquisitions, development projects, and property management.' ),
				array( 'group' => 'private', 'label' => 'Residential Conveyancing', 'description' => 'Advising on purchasing and transferring of residential and commercial property.' ),
				array( 'group' => 'private', 'label' => 'Wills, Probate & Estate Planning', 'description' => 'Crafting wills, trust deeds and lasting powers of attorney.' ),
			),
		),
		array(
			'slug'    => 'liquidations-trademarks-notarial',
			'title'   => 'Liquidations, Trade Marks & Notarial Services',
			'order'   => 8,
			'dek'     => 'Winding a company up cleanly, protecting a mark, and getting a document recognised abroad.',
			'nutshell'=> 'Voluntary liquidations, trade mark agent services, notarisation, apostille and legalisation in the UK and UAE.',
			'body'    => array(
				sbvi_block_heading( 'Voluntary Liquidations' ),
				sbvi_block_p( 'Handling the voluntary liquidation of both regulated and unregulated entities, from the initial solvency assessment through to dissolution.' ),
				sbvi_block_heading( 'Trade Marks' ),
				sbvi_block_p( 'Providing trade mark agent services, registrations, renewals and assignments.' ),
				sbvi_block_heading( 'Notarial Services' ),
				sbvi_block_p( 'Notarising corporate documents, affidavits, transfer deeds, certificates and other personal documents, and attending to apostille and legalisation in the UK and UAE.' ),
			),
			'faqs'    => array(),
			'nutshell_items' => array(
				array( 'group' => 'corporate', 'label' => 'Voluntary Liquidations', 'description' => 'Handling regulated and unregulated entities.' ),
				array( 'group' => 'corporate', 'label' => 'Trade Marks', 'description' => 'Providing trade mark agent services, registrations, renewals and assignments.' ),
				array( 'group' => 'corporate', 'label' => 'Notarial Services', 'description' => 'Notarising corporate documents, affidavits, transfer deeds, certificates and other personal documents, attending to apostille and legalisation in the UK and UAE.' ),
			),
		),
	);
}

function sbvi_seed_services( $image_id ) {
	foreach ( sbvi_services_data() as $data ) {
		$existing = get_page_by_path( $data['slug'], OBJECT, 'service' );
		if ( $existing ) {
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'    => 'service',
			'post_status'  => 'publish',
			'post_title'   => $data['title'],
			'post_name'    => $data['slug'],
			'post_content' => implode( "\n\n", $data['body'] ),
			'post_excerpt' => $data['dek'],
			'menu_order'   => $data['order'],
		) );

		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		update_post_meta( $id, '_sbvi_nutshell', $data['nutshell'] );
		update_post_meta( $id, '_sbvi_faqs', $data['faqs'] );
		update_post_meta( $id, '_sbvi_nutshell_items', $data['nutshell_items'] );

		if ( $image_id ) {
			set_post_thumbnail( $id, $image_id );
		}
	}
}

/* ---------------------------------------------------------------------- */
/* Testimonials & Articles (sample/placeholder content)                   */
/* ---------------------------------------------------------------------- */

/**
 * Real client testimonials, pulled from the live sinclairsbvi.com
 * testimonial slider (2026-08). Testimonial 1's source fields were
 * entered inconsistently on the live site (a first name in the "name"
 * field, the full name in the "job" field, no role given) — reproduced
 * faithfully rather than guessed at.
 */
function sbvi_real_testimonials_data() {
	return array(
		array(
			'name'   => 'Benoit Quertemont',
			'matter' => '',
			'quote'  => "We've been working with Adenike and her team for years, and they have been nothing short of amazing. Their team of lawyers and notaries always goes the extra mile to provide top-notch legal services with a high level of professionalism. Their deep understanding of BVI law and their keen attention to detail ensure that our needs are always met smoothly and efficiently. What really makes Sinclairs (BVI) stand out is their genuine commitment to client satisfaction. They take the time to understand our unique needs and offer solutions that truly work for us. Plus, their communication is always prompt and clear, keeping us in the loop every step of the way. We highly recommend this firm to anyone seeking reliable, expert legal and notary services in the BVI. They have been an invaluable partner in our journey, and we look forward to many more years of successful collaboration. Thank you Ade, for your outstanding service and dedication!",
		),
		array(
			'name'   => 'Michael Fitzhugh',
			'matter' => 'Attorney-At-Law',
			'quote'  => 'I was fortunate enough to have engaged Attorney Sicard to represent me on the purchase of a property in the BVI. I had a rather vague sense of the challenges going in, but was not at all prepared for the bureaucratic morass attendant to a real estate transaction there. She expertly guided me through the process as a purchaser and demonstrated a firm command of the law as my attorney, as well as a practical skill set that was necessary to get the transaction accomplished. There were a number of points that I wanted included in the Sale & Purchase agreement and her negotiation skills were key to getting them reflected in the final contract. As a practicing lawyer myself, I was quite demanding and far more engaged than most clients would have been, and thus my appraisal of her should be viewed through that prism of heightened scrutiny and expectations. My well-deserved endorsement of her was thus earned by her impressive diligence, talent, and professionalism.',
		),
		array(
			'name'   => 'Matt Freeman',
			'matter' => 'In-House Counsel',
			'quote'  => "Adenike's help has been essential in successfully restructuring elements of our investment fund business. It's rare to find an expert in a niche area of law who is also a generalist but Adenike is definitely both. Adenike is personable and communicative. Internal clients that have worked with Adenike have been particularly impressed by this. Beyond legal advice, Adenike has connected us with other skilled professional service providers from her network and that is something we value greatly. We can recommend Adenike and Sinclairs to anyone looking for a more personalised and tailored legal support service that bigger firms sometimes struggle to offer.",
		),
	);
}

function sbvi_seed_testimonials() {
	$existing = get_posts( array( 'post_type' => 'testimonial', 'post_status' => 'any', 'posts_per_page' => 1, 'no_found_rows' => true ) );
	if ( $existing ) {
		return;
	}

	foreach ( sbvi_real_testimonials_data() as $i => $t ) {
		$id = wp_insert_post( array(
			'post_type'    => 'testimonial',
			'post_status'  => 'publish',
			'post_title'   => $t['name'],
			'post_content' => sbvi_block_p( $t['quote'] ),
			'menu_order'   => $i + 1,
		) );
		if ( $id && ! is_wp_error( $id ) && $t['matter'] ) {
			update_post_meta( $id, '_sbvi_matter', $t['matter'] );
		}
	}
}

function sbvi_real_articles_data() {
	return array(
		array(
			'slug'     => 'registering-trade-marks-in-the-bvi',
			'title'    => 'What You Need Know About Registering Trade Marks In The BVI',
			'category' => 'Trade Marks',
			'date'     => '2026-07-29 09:00:00',
			'body'     => array(
				sbvi_block_p( 'On 1 September 2015, a new Trade Marks Act, 2013 (the “New Act”) and the Trade Mark Rules, 2015 came into force in the British Virgin Islands (the “BVI”).' ),
				sbvi_block_p( 'The Act repeals and replaces the existing Trade Mark Act (Cap. 158) (the “repealed Act”) the corresponding Trade Mark Rules, the Merchandise Marks Act (Cap. 154) and the Registration of United Kingdom Trade Marks Act (Cap. 157).' ),
				sbvi_block_p( 'Under the New Act, registrations and renewals of trademarks submitted upon the New Act coming into force will no longer require the submission of the UK registration or renewal certificate. A welcomed new feature.' ),
				sbvi_block_p( 'Under the new Act, a “trade mark” means any sign that is capable of (a) being represented graphically, and (b) distinguishing the goods or services of one person from those of another person, and includes a certification trade mark and collective trade mark (see below), unless otherwise specifically excepted. A “sign” includes (a) a brand, colour, device, figurative element, heading, label, letter, name, numeral, shape, signature, smell, sound, taste, ticket or word and “numeral” and “word” in this regard includes a foreign numeral and foreign script or word; and(b) any combination of signs.' ),
				sbvi_block_heading( 'BENEFITS THAT THE NEW ACT INTRODUCES' ),
				sbvi_block_p( '• The New Act allows marks relating to services to be registered. Under the former Act, only marks in respect of goods were allowed to be registered.' ),
				sbvi_block_p( '• Under the New Act, registrations and renewals of trademarks submitted upon the New Act coming into force will no longer require the submission of the UK registration or renewal certificate.' ),
				sbvi_block_p( '• Smells, sounds and tastes may obtain protection under the New Act.' ),
				sbvi_block_p( '• A collective trade mark (meaning a sign that is capable of (a) being represented graphically; and (b) distinguishing the goods or services of members of the collective association that is the owner of the sign from those persons who are not members of the collective association) may be registered under the New Act.' ),
				sbvi_block_p( '• A certification trade mark (meaning a sign that is capable of: (a) being represented graphically; and (b) distinguishing, in the course of trade, (i) goods that are certified by any person in respect of origin, material, mode of manufacture, quality, accuracy or other characteristic from goods that are not so certified; or (ii) services that are certified by any person in respect of quality, accuracy, performance or other characteristic from services that are not so certified) may be registered under the New Act.' ),
				sbvi_block_heading( 'APPLICATION FOR REGISTRATION OF TRADE MARK' ),
				sbvi_block_p( 'An application to register a trade mark must be filed with the Registrar through an approved Trade Mark Agent. The application must include the following:' ),
				sbvi_block_p( '(a) a request for registration of the trade mark;(b) the name and address of the applicant;(c) a statement of the goods or services in relation to which it is sought to register the trade mark;(d) a representation of the trade mark and such other information, document or matter as may be prescribed.' ),
				sbvi_block_p( 'The application must state whether the trade mark is being used by the applicant or with his consent in relation to the goods or services for which it is to be registered or whether the applicant honestly intends to use the trade mark or to allow it to be used in relation to the goods or services concerned.' ),
				sbvi_block_p( 'An application for the registration of a trade mark may be made in more than one class of the Nice Classification and shall specify the class or classes of goods or services to which the application relates.' ),
				sbvi_block_p( 'The specification must include for each class of goods or services a clear description (appropriate to that class) of the goods or services in respect of which the trade mark is proposed to be registered.' ),
				sbvi_block_p( 'Where the application relates to more than one class of goods or services in the Nice Classification, the specification must set out the classes in consecutive numerical order and the specification of the goods or services shall be grouped accordingly.' ),
				sbvi_block_heading( 'EXAMINATION, SEARCH AND APPROVAL' ),
				sbvi_block_p( 'Upon receiving an application for the registration of a trade mark, the Registrar will examine the application and conduct a search of earlier trade marks.' ),
				sbvi_block_p( 'Provided that (a) no disabilities apply (for example, where another application that relates to the trade mark concerned has priority under the New Act), (b) no notice of opposition has been filed with the Registrar within 3 months from the date of publication of the application for registration, (c) all oppositions are withdrawn or decided in favour of the applicant and (d) the required fees have been paid, the Registrar will approve the application and register the trade mark in the Trade Mark Register and issue a Certificate of Registration.' ),
				sbvi_block_p( 'Registration lasts for a period of ten (10) years from the date of registration.' ),
				sbvi_block_heading( 'RENEWAL AND/OR RESTORATION' ),
				sbvi_block_p( 'The owner of a registered trade mark may file a request to renew the registration prior to or on the expiration of the registration period by filing Form TM 11 along with the renewal fee of US$250 for 1 class (and $150 for each additional class and $75 for each additional mark of the series).' ),
				sbvi_block_p( 'Where 6 months have elapsed from the expiration of the registration and the removal of the trade mark name from the register, the owner must request to have the name restored to the register, and pay the requisition fee ($250), the renewal fee ($250) and the applicable penalty for late renewal of registration ($150). The restoration will be treated as if it were a renewal of the trade mark registration and the effective will be from the date of expiry of the previous registration.' ),
				sbvi_block_heading( 'PRIORITY' ),
				sbvi_block_p( 'First in time will have priority in respect of identical or similar trade marks. Also, a person who has duly filed an application for the registration of a trade mark in a Paris Convention country (the “Convention application”) or WTO member (the “WTO application”), or his successor in title, has a right of priority, for the purposes of registering the same trade mark under the New Act for some or all of the same goods or services, for a period of 6 months from the date of filing of the first such application.' ),
				sbvi_block_p( 'The foregoing is for general information purposes only and is not intended to replace or substitute legal advice. This guide is provided to clients of Sinclairs (BVI) and is not to be circulated or published without prior written permission.' ),
			),
		),
		// 27 blocks

		array(
			'slug'     => 'what-are-trusts',
			'title'    => 'What are trusts?',
			'category' => 'Trusts & Estates',
			'date'     => '2026-07-29 09:00:00',
			'body'     => array(
				sbvi_block_p( 'Trusts are a long established system of, exactly as it is called, a system of trust. A system of trusting others to look after your assets for the benefit of others. It relies on a great deal of trust, since the legal interest or title in your assets will be transferred to the trustee.' ),
				sbvi_block_p( 'A person who transfers property into a trust is called a “Settlor”. The persons who enjoy the income or capital from a trust are called “Beneficiaries”. The person to whom the great deal of trust is given, is called the “Trustee”.' ),
				sbvi_block_p( 'In more modern times, the laws governing trusts allow for more flexibility, such as the BVI, where the Settlor is allowed to also benefit from the assets transferred to a trust, without the Settlor having the legal ownership of those assets, so that the Trustee will continue to have day to day control over the assets. Alternatively, a trust can be completely independent of the person who established it (i.e. – the Settlor).' ),
				sbvi_block_heading( 'Tax Perspectives' ),
				sbvi_block_p( 'Tax advice should always be obtained from a lawyer in the both your jurisdiction of residence and domicile (i.e. – onshore tax lawyers) prior to establishing a trust.' ),
				sbvi_block_p( 'Under the Trustee Act of the BVI, the income of any trust established under the laws of the BVI (“trust”), in the hands of a Trustee is exempt from income tax payable in the BVI and the beneficiaries of any trust who are not persons resident in the BVI shall likewise be exempt from payment of income tax in the BVI in respect of any moneys received by them from the Trustee of any trust. No estate tax, inheritance tax, succession tax, gift tax, rate, duty, levy or other charge is payable in the BVI by beneficiaries who are not resident in the BVI in respect of any distribution to them by the trustee of any trust.' ),
				sbvi_block_heading( 'Types of trusts' ),
				sbvi_block_p( 'There are several types of trusts that one may establish under the laws of the BVI (some of these types may overlap in any one trust structure):' ),
				sbvi_block_p( '(1) A Discretionary Trust – Subject to guidelines and the powers in the trust deed, and perhaps the supervision of a protector, and information in a letter of wishes, the Trustee of a discretionary trust will have the discretion to distribute the benefits or income (or capital) of the trust to a named class of beneficiaries. No Beneficiary is entitled to income as of right. The income may be retained in the trust by the Trustee. Capital may be gifted to nominated individuals or to a class of beneficiaries at the discretion of the' ),
				sbvi_block_p( '(2) A Life Interest Trust – (Sometimes referred to as interest in possession trusts and in Scotland known as life renter trusts). Here a nominated beneficiary (called a “life tenant” or “life renter” in Scotland) has an interest in the income from the assets in the trust or has the use of trust assets. This right may be for life or some shorter period (such as up and until the nominated beneficiary attains a certain age). The capital may pass onto another beneficiary or beneficiaries, for example, where income is left to a wife for her life, and upon her death, the income is to pass to the children.' ),
				sbvi_block_p( '(3) A Charitable Trust – A trust that is set up for exclusively charitable' ),
				sbvi_block_p( '(4) A Purpose Trust – (Also called a “Non-Charitable Purpose Trust”) a trust that may be created for any purpose or purposes provided that, the following apply:' ),
				sbvi_block_list( array( '(a) the purpose or purposes are specific, reasonable and possible;', '(b) the purpose or purposes are not immoral, contrary to public policy or unlawful;', '(c) at least one trustee of the trust is a designated person1;', '(d) the trust instrument appoints a person as enforcer of the trust, and provides for the appointment of another enforcer on any occasion on which there is no enforcer, or no enforcer able and willing to act;', '(e) the enforcer appointed by the trust instrument is a party to the trust instrument or gives his consent in writing, addressed to the trustee who is a designated person, to act as enforcer of the trust.' ) ),
				sbvi_block_p( 'A Purpose Trust, along with all other BVI trusts, may exist for a perpetual period, which under the laws of the BVI is up to 360 years.' ),
				sbvi_block_p( '(5) A VISTA Trust – Established pursuant to the Virgin Island Special Trusts Act, 2003, which forms the trust’s name and acronym “VISTA”. The main attractive features of a VISTA Trust are (a) the trust fund is made up of shares (such as shares in a family company), (b) the Trustee is relieved of the duty to intervene in or be involved in the company’s affairs or management, unless an “intervention call” is made, which grounds are set out in the trust deed, (c) Trustee is not liable for the consequences of retaining the shares in the trust, and (d) the Trustee may not sell the shares without the director’s approval.' ),
				sbvi_block_p( '1 Pursuant to section 84A(1) of the Trustee Act, a “designated person” means: (a) a legal practitioner practising in the BVI; (b) an accountant practising in the BVI who qualifies as an auditor for the purposes of the Regulatory Code or any financial services legislation of the BVI; (c) a licensee under the Banks and Trust Companies Act, 1990, (such as trust company situated in the BVI) or (d) a private trust company within the meaning of paragraph 1 of Part I of the Schedule to the Financial Services (Exemptions) Regulations, 2007, which a family may set up to act as the trustee itself; or (e) such other person as the Minister of Finance may, by Order, designate.' ),
				sbvi_block_heading( 'Private Trust Companies' ),
				sbvi_block_p( 'A company in the BVI which offers trustee services to trusts, must be licensed (and is regulated) by the BVI Financial Services Commission under the Bank and Trust Companies Act, 1990 (the “BTC Act”). Therefore, using a BVI licensed trust company should offer a level of security and comfort.' ),
				sbvi_block_p( 'On the other hand, if using a foreign corporate trustee who is unknown to you is not appealing, you may set up your own private trust company to offer trustee services to a trust.' ),
				sbvi_block_p( 'A BVI private trust company (“PTC”), is exempted from the requirement to be licensed by the BVI Financial Services Commission under the BTC Act, in order to carry on trustee services, including acting as the sole or joint trustee of a VISTA Trust (described above).' ),
				sbvi_block_p( 'However, to avail of the exemption, a PTC company may be formed on the basis that it must only conduct: (a) unremunerated trust business or (b) “related trust business” – meaning trust business that is provided to either a single qualifying trust or a group of related qualifying trusts. A “qualifying trust” means a trust where each beneficiary of the trust is (a) a connected person (i.e.- a family relationship, whether through consanguinity, marriage or adoption) (b) a charity, or (c) the settlor.' ),
				sbvi_block_p( 'Restrictions – The PTC may not carry on any business that is not trust business, solicit trust business from members of the public, or carry on any trust business other than either unremunerated trust business or related trust business, as the case may be.' ),
				sbvi_block_heading( 'How we can help?' ),
				sbvi_block_p( 'This Memorandum covers some aspects of trusts. If you are interested in providing for your family through the use of trusts, please feel free to contact us. We would be happy to assist you.' ),
				sbvi_block_p( 'For more information of advice on trusts please contact us at bvi@sinclairsoffshore.com . The foregoing is for general information purposes only and is not intended to replace or substitute legal advice. This guide is provided to clients of Sinclairs (BVI) and is not to be circulated or published without prior written permission.' ),
			),
		),
		// 25 blocks

		array(
			'slug'     => 'trustee-amendment-act-2015',
			'title'    => 'Trustee (Amendment) Act, 2015',
			'category' => 'Trusts & Estates',
			'date'     => '2026-07-29 09:00:00',
			'body'     => array(
				sbvi_block_p( 'On 30 March 2015, the Trustee Act, 1961 (the “Trustee Act”) of the British Virgin Islands (the “BVI”) was amended by virtue of the Trustee (Amendment) Act, 2015.' ),
				sbvi_block_p( 'The amendment is not entirely new to the industry. The amendment reflects the same wording and duty imposed on BVI business companies under section 98 of the BVI Business Companies Act, 2004 (as amended) to maintain financial records that are sufficient to show and explain the company’s transactions and which will, at any, time enable the financial position of the company to be determining with reasonable accuracy.' ),
				sbvi_block_heading( 'IN A NUTSHELL' ),
				sbvi_block_p( 'The amendment to the Trustee Act, similarly, places on every Trustee, the duty to maintain records and underlying documentation of the each trust for which it is a trustee whether within or outside of the BVI and to retain these records and underlying documentation for a period of at least 5 years. The records and underlying documentation of the trust must be sufficient to show and explain the trusts transactions and will, at any time, enable the financial position of the trust to be determined with reasonable accuracy.' ),
				sbvi_block_p( 'The records and underlying documentation includes accounts and records, such as invoices, contracts or other similar documentation in relation to the following:' ),
				sbvi_block_p( '(a) all sums of money received and expended by the trust and the matters in respect of which the receipt and expenditure takes places;' ),
				sbvi_block_p( '(b) all sales and purchases of goods by the trust; and' ),
				sbvi_block_p( '(c) the assets and liabilities of the trust.' ),
				sbvi_block_heading( 'BREACH AND LIABILITY' ),
				sbvi_block_p( 'Each Trustee should take this responsibility seriously. Where a Trustee, without lawful or reasonable excuse, fails to comply with this legal requirement, the Trustee commits an offence and is liable on summary conviction to a fine not exceeding US$100,000.00 or to imprisonment for a term not exceeding 5 years.' ),
				sbvi_block_p( 'Under the common law, a Trustee already had the duty to keep clear trust accounts and trust accounts separate from his or her own personal accounts. A Trustee who failed to keep and be ready with such accounts could be held to be in breach of his trustee duties and be personally liable to pay damages and any costs to remedy the breach.' ),
				sbvi_block_p( 'Therefore, Trustees must now be aware of both the statutory obligations and liabilities, as well as to those which apply under the common law.' ),
				sbvi_block_heading( 'IN PRACTICAL TERMS' ),
				sbvi_block_p( 'The duty to maintain records and underlying documentation of the each trust should not be taken lightly as failure to comply carries with it the possibility of a fine or imprisonment.' ),
				sbvi_block_p( 'If the Trustee acts for a trust which solely holds shares in a company, at the bare minimum, the Trustee should request and obtain the company’s annual financial statements.' ),
				sbvi_block_p( 'With respect to the time frame for keeping the records, we would interpret the time period to commence running from the date of termination of the trustee relationship, keeping in line with the general requirements of other legislation in the BVI which requires the maintenance of documents, such as due diligence documents, for a period of 5 years after the termination of the relationship. Perhaps in the future the Trustee Act may once more be amended to make this requirement clear.' ),
				sbvi_block_p( 'The amendment would apply to any trust which falls under the Trustee Act, such as one which has a BVI Trustee or is governed by the laws of the BVI.' ),
				sbvi_block_heading( 'STANDARD OF CARE' ),
				sbvi_block_p( 'The standard of care that is expected of a Trustee has not changed. However, the amendment makes is clear what is expected of a Trustee in carrying out his or her duties as a Trustee with respect to maintaining accountability of the trust’s transactions and the trust’s financial position.' ),
				sbvi_block_p( 'For more information of advice on trusts please contact us at bvi@sinclairsoffshore.com . The foregoing is for general information purposes only and is not intended to replace or substitute legal advice. This guide is provided to clients of Sinclairs (BVI) and is not to be circulated or published without prior written permission.' ),
			),
		),
		// 20 blocks

		array(
			'slug'     => 'incubator-funds-and-approved-funds',
			'title'    => 'Incubator Funds and Approved Funds',
			'category' => 'Investment Funds',
			'date'     => '2026-07-29 09:00:00',
			'body'     => array(
				sbvi_block_p( 'The types of BVI Funds are relatively new and lightly regulated fund vehicles being offered in the British Virgin Islands, aimed at start-up emerging managers or persons wishing to manage funds for a group of investor friends or family members or non-institutional investors.' ),
				sbvi_block_p( 'The light touch regulation allows for the funds to be set up in a timely manner with minimal set up costs and regulatory obligations, thereby allowing fund managers to increase their assets efficiently.' ),
				sbvi_block_p( 'With respect to the Approved Fund, it is a more likely choice for managers who wish to establish funds for the long term geared at family offices or a tight network of friends or group of people. The funds may begin operating within 2 business days of submitting their completed application to the BVI Financial Services Commission for approval.' ),
				sbvi_block_p( 'Since coming on stream in 2015, the number of Incubator Funds and Approved Funds has increased from 9 funds of each type at the end of the first quarter of that year, to 46 and 71, respectively, as at the end of the first quarter of last year, 2018.' ),
				sbvi_block_p( 'An Incubator Fund is suitable for sophisticated private investors only and the total number of investors in the fund is limited to a maximum of 20. An Incubator Fund may only have investments not exceeding US$20,000,000 in net assets or its equivalent in any other currency. It provides the flexibility to managers of a start-up fund to keep costs down for a period of two years while they build up their portfolio. After two years, the fund is expected to either convert to a private or professional fund, wind up, or if it requires more time, to apply to the Commission for an extension if its incubator status.' ),
				sbvi_block_p( 'In the case of an Approved Fund, the total number of investors in the fund is limited to a maximum of 20, and the fund may only have investments not exceeding US $100,000,000 in net assets or its equivalence in any other currency. There is no limited on the time period of its approved status.' ),
				sbvi_block_p( 'The foregoing is for general information purposes only and is not intended to replace or substitute legal advice. This guide is provided to clients of Sinclairs (BVI) and is not to be circulated or published without prior written permission.' ),
				sbvi_block_p( 'For more advice, please contact Adenike M. Sicard, Partner, Sinclairs (BVI) at adenike.sicard@sinclairsoffshore.com .' ),
			),
		),
		// 8 blocks

		array(
			'slug'     => 'economic-substance-act-2018-explained',
			'title'    => 'How does the Economic Substance (Companies and Limited Partnership) Act, 2018 (the “Act”) Affect you?',
			'category' => 'Economic Substance',
			'date'     => '2026-07-29 09:00:00',
			'body'     => array(
				sbvi_block_heading( 'A Brief Historical Background leading to the implementation of the Act' ),
				sbvi_block_p( 'European Union (the “EU”) Member States adopted a Code of Conduct for Business Taxation in 1997, which addressed the issue of harmful tax practices, and established the Code of Conduct Group (the “COCG”) to police it. This was followed in 1998 by the Organisation for Economic Cooperation and Development (the “OECD”) which published a report titled “Harmful Tax Competition: An Emerging Global Issue” pertaining jurisdictions which levy either low or no corporate income tax.' ),
				sbvi_block_p( 'Both the EU’s Code of Conduct and the OECD’s report highlighted an area of concern being the granting of tax advantages by certain jurisdictions which did not require any real economic activity and substantial economic presence by the companies or entities which were established in said jurisdictions offering the tax advantages.' ),
				sbvi_block_p( 'As a result, the BVI, as well as other offshore financial centres, were required to implement legislation which addressed those concerns and issues raised by the COCG and the OECD.' ),
				sbvi_block_heading( 'Purpose of the Act' ),
				sbvi_block_p( 'The Act, which came into force on 1 January 2019, seeks to address the EU concerns about the possible misuse of BVI companies for profit shifting and the OECD’s concerns regarding economic presence.' ),
				sbvi_block_heading( 'Salient Points in the Act' ),
				sbvi_block_heading( 'To whom does it apply?' ),
				sbvi_block_p( '– In a nutshell, the Act applies to all BVI companies and LPs (including foreign registered ones) (the “Entities”) unless they are and can prove that they are tax resident outside of the BVI in a jurisdiction that is not included on the EU’s list of non-cooperative jurisdictions.' ),
				sbvi_block_heading( 'What are the requirements of the Act?' ),
				sbvi_block_p( '– Entities which are tax resident in the BVI are required to demonstrate economic substance in the BVI if they carry on “relevant activities”, which means any of the following activities:' ),
				sbvi_block_list( array( 'banking business;', 'insurance business;', 'fund management business;', 'finance and leasing business;', 'headquarters business;', 'shipping business;', 'holding business;', 'intellectual property business;', 'distribution and service centre business.' ) ),
				sbvi_block_p( '– Each Entity which is tax resident in the BVI must, in relation to any relevant activity listed above, carry out core income-generating activities in the BVI and demonstrate economic substance by reference to adequacy of expenditure, staff and premises in the' ),
				sbvi_block_heading( 'The Act, BOSSS and the Reporting Requirements' ),
				sbvi_block_p( 'The Act introduces economic substance requirements for Entities which are tax resident in the BVI and conducting “relevant activities” (defined above) and it also amends the Beneficial Ownership Secure Search System Act, 2017 (as amended) (“BOSSS“) so as to impose reporting requirements and the obligation to pass information to EU tax authorities in appropriate cases.' ),
				sbvi_block_p( 'Each Entity which is tax resident in the BVI must provide information via BOSSS to the BVI International Tax Authority (the “ITA”) to enable the ITA to determine whether or not the entity is carrying on relevant activities during its financial period and, if so, whether it is complying with the economic substance requirements. Sinclairs (BVI) is able to assist you with a review and legal opinion on your Entity’s activities and the applicability of the Act.' ),
				sbvi_block_p( 'Pursuant to amendments to BOSSS, entities are required to submit basic information on their tax residency and on the activities that they conduct on an annual basis.' ),
				sbvi_block_p( 'The ITA has investigative and enforcement powers which it may employ to request further information from an Entity. The ITA will conduct periodic on-site visits which are to be conducted randomly and also on a risk based approach.' ),
				sbvi_block_p( 'The ITA may impose penalties for failure to provide required information and/or for operating an Entity in breach of the economic substance requirements.' ),
				sbvi_block_heading( 'EU Member States' ),
				sbvi_block_p( 'EU member state tax authorities are notified of the information held on BOSSS which relates to an Entity which has a beneficial owner in the member state, which is registered in a member state or claims to be tax resident in a member state.' ),
				sbvi_block_p( 'For further information, please contact Sinclairs (BVI) at bvi@sinclairsoffshore.com. The foregoing is for general information purposes only and is not intended to replace or substitute legal advice. This guide is provided to clients of Sinclairs (BVI) and is not to be circulated or published without prior written permission' ),
			),
		),
		// 22 blocks

		array(
			'slug'     => 'estate-planning-and-cryptocurrencies',
			'title'    => 'Estate Planning and Cryptocurrencies',
			'category' => 'Trusts & Estates',
			'date'     => '2020-05-12 09:00:00',
			'body'     => array(
				sbvi_block_p( 'There are many things that one is sure of in life and, as the sun rises each day, we know that death and taxes are occurrences that, while we can prepare for them if we are lucky, we cannot avoid.' ),
				sbvi_block_p( 'Different types of assets have emerged and evolved over the years. Who remembers the first computers, or even a time before computers? A time before the “I owe you” notes, or the first payment by cheques (or really, a scribbling on a piece of paper to take to a bank and they will give you cash, hopefully), to the first payment by plastic. Swipe a plastic card through a machine or punch in 4 digits and you may walk out of my store with my goods in your hands. To present-day (well, a few years ago really), where there exists digital currency which is mined on a computer by mathematical whizzes solving puzzles for coins.' ),
				sbvi_block_p( 'If one thing is for sure now, it is that cryptocurrencies are deemed property. See the case AA v. Persons Unknown and Others, Re Bitcoin of the English High Court, which dealt with bitcoins in particular. Not that we needed the case law to confirm that reality. However, as lawyers, we love having case law as the authority on an issue. It is our lifeline. Perhaps, I exaggerate a little, but you get the point.' ),
				sbvi_block_p( 'In any event, people have been using bitcoins (a type of cryptocurrency) to purchase goods and therefore use it as currency, albeit fiat currency (i.e.- not backed by any central bank).' ),
				sbvi_block_p( 'Many financial advisors or planners have advised on having a diversified portfolio which includes having some cryptocurrencies (for those who have the risk appetite). This naturally leads to estate planning, especially as we started off accepting that death and taxes are certainties in life, albeit not ones that we dream about. Like insurance, it is best practice to have a will so that your loved ones may have access to the assets that you leave behind.' ),
				sbvi_block_p( 'Having accepted that cryptocurrency is an asset, considering it is digital and not the tangible type of assets that we are used to, such as a house, car or bank account held at a physical bank that you can walk into. How would your loved ones know about its existence, if you do not tell them about it? How can they access it, especially if your loved one is not tech-savvy or does not have a clue about digital currencies, much less “mining” something other than … coal? Also, you must safeguard your digital wallets and access codes. Otherwise, these can be stolen and all the “money” or coins in them, while you sleep.' ),
				sbvi_block_p( 'Some companies are recently offering retrieval of currencies and funds services where the owner is issued a unique number or card and gives it to beneficiaries, who upon providing a death certificate and said special card, may retrieve the deceased’s cryptocurrencies.' ),
				sbvi_block_p( 'However, companies may fold, and while that is an excellent service, nothing beats the surety of a good, properly drafted will. The important legal work in drafting such a will is to ensure that the cryptocurrencies are properly identified, so as to not fall in the residuary estate of the deceased, and also to be able to assist beneficiaries in finding them at the right time, while ensuring that private key information will not fall into the wrong hands.' ),
				sbvi_block_p( 'We have advised on and created wills for clients with cryptocurrency assets and can do the same for you.' ),
				sbvi_block_p( 'Do not leave your hard-earned coins to roll around in cyberland and not be accessed or used by those whom you care about most.' ),
			),
		),
		// 10 blocks

	);
}

function sbvi_seed_articles( $image_id ) {
	$existing = get_posts( array( 'post_type' => 'article', 'post_status' => 'any', 'posts_per_page' => 1, 'no_found_rows' => true ) );
	if ( $existing ) {
		return;
	}

	foreach ( sbvi_real_articles_data() as $i => $art ) {
		$term = term_exists( $art['category'], 'article_category' );
		if ( ! $term ) {
			$term = wp_insert_term( $art['category'], 'article_category' );
		}

		$id = wp_insert_post( array(
			'post_type'    => 'article',
			'post_status'  => 'publish',
			'post_title'   => $art['title'],
			'post_name'    => $art['slug'],
			'post_content' => implode( "\n\n", $art['body'] ),
			'post_date'    => $art['date'],
		) );

		if ( $id && ! is_wp_error( $id ) && ! is_wp_error( $term ) ) {
			wp_set_object_terms( $id, (int) $term['term_id'], 'article_category' );
			if ( $image_id ) {
				set_post_thumbnail( $id, $image_id );
			}
		}
	}
}
