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
	sbvi_seed_articles();

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
			<?php esc_html_e( 'Home, About, Our Services, Our Articles and Contact pages, the 8 practice areas, sample testimonials and sample articles have been created with starting content.', 'sinclairs-bvi' ); ?>
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

function sbvi_seed_testimonials() {
	$existing = get_posts( array( 'post_type' => 'testimonial', 'post_status' => 'any', 'posts_per_page' => 1, 'no_found_rows' => true ) );
	if ( $existing ) {
		return;
	}

	$placeholders = array(
		'Paste the first testimonial from the current site here.',
		'Paste the second testimonial from the current site here.',
		'Paste the third testimonial from the current site here.',
	);

	foreach ( $placeholders as $i => $quote ) {
		$id = wp_insert_post( array(
			'post_type'    => 'testimonial',
			'post_status'  => 'publish',
			'post_title'   => sprintf( 'Client name (sample %d — replace me)', $i + 1 ),
			'post_content' => sbvi_block_p( $quote ),
			'menu_order'   => $i + 1,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_sbvi_matter', 'Matter' );
		}
	}
}

function sbvi_seed_articles() {
	$existing = get_posts( array( 'post_type' => 'article', 'post_status' => 'any', 'posts_per_page' => 1, 'no_found_rows' => true ) );
	if ( $existing ) {
		return;
	}

	$samples = array(
		array(
			'title'    => 'Headline of your first article goes here',
			'category' => 'Regulatory',
		),
		array(
			'title'    => 'Headline of your second article goes here',
			'category' => 'Investment Funds',
		),
		array(
			'title'    => 'Headline of your third article goes here',
			'category' => 'Virtual Assets',
		),
	);

	$standfirst = 'A two-line standfirst summarising the piece — what changed, who it affects and what they should do about it.';

	foreach ( $samples as $i => $sample ) {
		$term = term_exists( $sample['category'], 'article_category' );
		if ( ! $term ) {
			$term = wp_insert_term( $sample['category'], 'article_category' );
		}

		$id = wp_insert_post( array(
			'post_type'    => 'article',
			'post_status'  => 'publish',
			'post_title'   => $sample['title'] . ' (sample — replace me)',
			'post_content' => sbvi_block_p( 'Replace this placeholder with the full article body.' ),
			'post_excerpt' => $standfirst,
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i * DAY_IN_SECONDS ) ),
		) );

		if ( $id && ! is_wp_error( $id ) && ! is_wp_error( $term ) ) {
			wp_set_object_terms( $id, (int) $term['term_id'], 'article_category' );
		}
	}
}
