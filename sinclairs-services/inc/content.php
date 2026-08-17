<?php
/**
 * All "Our Services" copy, transcribed from the client's Word documents
 * (28 July 2026 set, plus the amendments in "Comments to Andrew",
 * 10 August 2026).
 *
 * Three things are defined here:
 *
 *   ssvc_page_intro()       the "OUR EXPERTISE" header wording
 *   ssvc_sections()         the 10 practice areas, in page order
 *   ssvc_nutshell_groups()  the In a Nutshell index, grouped
 *
 * Every one runs through a filter of the same name so wording can be
 * amended from a child theme without touching this file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page header. The client asked for the live site's current wording to be
 * replaced — see "Our Services for Investment Business Part and its FAQs",
 * which quotes the old text and gives the replacement verbatim.
 */
function ssvc_page_intro() {
	return apply_filters( 'sinclairs_services_intro', array(
		'kicker' => __( 'Our Expertise', 'sinclairs-services' ),
		'title'  => __( 'Our Services and Expertise Include:', 'sinclairs-services' ),
		'dek'    => __( 'Providing trusted guidance and personalized support for legal matters in the British Virgin Islands as follows:', 'sinclairs-services' ),
	) );
}

/**
 * The practice areas, in the order the client listed them.
 *
 * Each section is:
 *   id     anchor slug — also the nav dropdown and nutshell link target
 *   title  H2, and the label used in the nav dropdown
 *   brief  the one-line "in a nutshell" wording, shown in the sticky side
 *          rail beside the detail prose
 *   body   the detail prose; 'h3' rows are sub-headings within a section
 *   faqs   question/answer pairs for the "Information & FAQs" accordion
 */
function ssvc_sections() {
	$sections = array(

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'investment-business',
			'title' => __( 'Investment Funds, Approved Managers & Investment Business', 'sinclairs-services' ),
			// The Nutshell document does not cover this practice area — see
			// Adenike's note that the remaining services would follow
			// separately. Carried over from the approved theme copy pending
			// a client-supplied line.
			'brief' => __( 'Establishing BVI funds, Approved Managers, SIBA licences and authorised representative services.', 'sinclairs-services' ),
			'body'  => array(
				array( 'h3', __( 'Investment Funds', 'sinclairs-services' ) ),
				array( 'p', __( 'Sinclairs (BVI) advises investment managers, fund promoters and onshore counsel on structuring and establishing investment funds in the British Virgin Islands under the Securities and Investment Business Act, 2010 (“SIBA”).', 'sinclairs-services' ) ),
				array( 'p', __( 'We advise on hedge funds, private equity funds and other investment structures formed as BVI business companies, segregated portfolio companies, limited partnerships or unit trusts. Our work includes establishing and obtaining regulatory recognition or approval of private funds, professional funds, incubator funds, approved funds and private investment funds, including qualifying closed-ended structures.', 'sinclairs-services' ) ),
				array( 'p', __( 'We also establish segregated portfolio companies and assist existing funds wishing to convert to SPC status.', 'sinclairs-services' ) ),
				array( 'h3', __( 'Approved Managers', 'sinclairs-services' ) ),
				array( 'p', __( 'The BVI Approved Managers regime offers a practical regulatory framework for qualifying investment managers and investment advisers. We advise clients on establishing Approved Managers in the BVI and guide them through the application process, including the preparation and submission of applications and supporting documents to the BVI Financial Services Commission.', 'sinclairs-services' ) ),
				array( 'h3', __( 'Investment Business Licensing', 'sinclairs-services' ) ),
				array( 'p', __( 'We assist investment managers, fund administrators and other service providers with applications for licences under SIBA. This includes applications relating to businesses dealing in contracts for differences and other regulated investment products and activities.', 'sinclairs-services' ) ),
				array( 'h3', __( 'Authorised Representative Services', 'sinclairs-services' ) ),
				array( 'p', __( 'Sinclairs (BVI) also provides Authorised Representative services to regulated funds and licensees. We act as the principal liaison between our clients and the BVI Financial Services Commission and assist with regulatory submissions and continuing obligations.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'What type of funds do you assist with establishing in the BVI?', 'sinclairs-services' ),
					'a' => array( __( 'We assist with establishing private, professional, incubator, approved and private investment funds, including hedge funds and private equity funds. These may be structured as BVI business companies, segregated portfolio companies, limited partnerships or unit trusts.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Do you assist with regulatory and licence applications to the BVI Financial Services Commission?', 'sinclairs-services' ),
					'a' => array(
						__( 'Yes. We advise on and prepare applications to the BVI Financial Services Commission under SIBA. These include applications for the recognition or approval of private, professional, incubator, approved and private investment funds, as well as applications relating to Approved Managers and segregated portfolio companies.', 'sinclairs-services' ),
						__( 'We also assist investment managers, fund administrators and other businesses seeking licences to conduct regulated investment business, including activities involving contracts for differences.', 'sinclairs-services' ),
					),
				),
				array(
					'q' => __( 'What is an Approved Manager?', 'sinclairs-services' ),
					'a' => array( __( 'An Approved Manager is a BVI investment manager or adviser operating under a lighter-touch regulatory regime. It offers eligible managers a simpler route to approval than applying for a full investment-business licence under SIBA. Approved Managers remain regulated by the BVI Financial Services Commission and must comply with ongoing requirements and prescribed business limits.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'corporate-commercial',
			'title' => __( 'Corporate and Commercial Law', 'sinclairs-services' ),
			'brief' => __( 'Structuring or restructuring corporate entities, advising on contracts, cross-jurisdictional transactions, advising on acquisitions, company restorations. Continuations into and out of the BVI. Drafting special memorandum and articles of association for BVI Companies.', 'sinclairs-services' ),
			'body'  => array(
				array( 'p', __( 'Sinclairs (BVI) provides practical advice across the full life cycle of a BVI entity. We help clients structure and restructure companies and other corporate vehicles, advise on financing and commercial contracts, advise on acquisitions, cross-border transactions, and assist with restoration of struck-off/dissolved companies to the Register.', 'sinclairs-services' ) ),
				array( 'p', __( 'We also advise on the continuation of companies into and out of the BVI and prepare bespoke memoranda and articles of association for BVI companies whose ownership, governance or business requirements are not adequately addressed by standard constitutional documents.', 'sinclairs-services' ) ),
				array( 'p', __( 'Our banking and corporate finance work includes advising on financing transactions, security over shares and other assets, and the registration of charges. We also issue BVI legal opinions on transactions, including opinions on a BVI entity’s capacity, power and authority, and on the validity and enforceability of the contracts and deeds into which it enters, and opinions on BVI law for proceedings before foreign courts.', 'sinclairs-services' ) ),
				array( 'p', __( 'We provide directorship services to licensed entities and advise BVI companies and limited partnerships on their economic substance obligations. Where necessary, we assist clients with submissions to and enquiries from the BVI International Tax Authority.', 'sinclairs-services' ) ),
				array( 'p', __( 'Our commercial property practice covers leases, acquisitions and development projects, as well as the legal aspects of property ownership and management in the BVI.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'Do you advise on cross-border transactions involving BVI entities?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We provide BVI corporate and commercial support for cross-border acquisitions, investments, joint ventures, financings and restructurings. We work with clients and overseas counsel to review transaction documents, prepare corporate approvals, register security and address the BVI aspects of the transaction through to completion.', 'sinclairs-services' ) ),
				),
				array(
					// Amended 10 Aug 2026: "by an application to the Court".
					'q' => __( 'Can a struck-off or dissolved BVI company be restored?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. Depending on the circumstances, restoration may be obtained through the Registrar of Corporate Affairs or by an application to the Court. We advise on the appropriate route and handle the process.', 'sinclairs-services' ) ),
				),
				array(
					// Amended 10 Aug 2026: question retitled and answer rewritten.
					'q' => __( 'Do you prepare and issue BVI legal opinions?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We prepare and issue legal opinions on BVI law, whether related to specific issues, or general, standard legal opinions on the power, capacity and authority for BVI Companies to enter into various transaction documents, and on the enforceability of their obligations thereunder.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			// Retitled from "Compliance & Regulatory Law" at the client's
			// request — see the AML/CFT document's opening instruction.
			'id'    => 'aml-cft-compliance',
			'title' => __( 'AML/CFT and Regulatory Compliance', 'sinclairs-services' ),
			'brief' => __( 'Advises regulated entities and other relevant persons on the BVI framework for anti-money laundering, countering the financing of terrorism and countering proliferation financing (“AML/CFT/CPF”).', 'sinclairs-services' ),
			'body'  => array(
				array( 'lede', __( 'Effective compliance is more than having a manual on file. It requires policies, procedures and controls that reflect how a business actually operates.', 'sinclairs-services' ) ),
				array( 'p', __( 'Sinclairs (BVI) advises regulated entities and other relevant persons on the BVI framework for anti-money laundering, countering the financing of terrorism and countering proliferation financing (“AML/CFT/CPF”). We help clients understand regulatory changes and translate their obligations into clear, workable procedures.', 'sinclairs-services' ) ),
				array( 'p', __( 'We prepare and update AML/CFT/CPF compliance manuals tailored to each client’s regulatory status, business model, customers, services, operating jurisdictions and risk profile. Our advice covers areas such as governance, institutional and customer risk assessments, customer due diligence, ongoing monitoring, internal reporting, sanctions controls, record-keeping and staff training.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'Can you prepare or update our compliance manual?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We prepare practical, risk-based manuals tailored to each client’s business, customers, services and operating jurisdictions.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'virtual-assets-fintech',
			'title' => __( 'Virtual Assets and FinTech', 'sinclairs-services' ),
			'brief' => __( 'Advising on VASP and other digital and cryptocurrency issues.', 'sinclairs-services' ),
			'body'  => array(
				array( 'lede', __( 'The British Virgin Islands has emerged as a leading jurisdiction for digital and virtual asset businesses.', 'sinclairs-services' ) ),
				array( 'p', __( 'We provide comprehensive legal advice to entities seeking to operate as Virtual Asset Service Providers (VASPs) in the BVI, navigating the requirements under the Virtual Assets Service Providers Act, 2022 (VASPA).', 'sinclairs-services' ) ),
				array( 'p', __( 'Whether you are launching a cryptocurrency exchange, a digital asset fund, a blockchain-based financial product, or a Web3 platform, we provide the legal foundation you need to operate with confidence in the BVI. We also advise on digital asset funds and blockchain-based financial products, including whether SIBA or other BVI financial services legislation may apply. From initial structuring through registration and launch, we provide clear, practical guidance at each stage.', 'sinclairs-services' ) ),
				array( 'p', __( 'If needed, we prepare applications for registration as a Virtual Asset Service Provider (“VASP”) with the BVI Financial Services Commission. We also advise on corporate structuring, governance, ongoing regulatory obligations and the anti-money laundering, counter-terrorist financing and counter-proliferation financing requirements applicable to virtual asset businesses.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'What activities may require VASP registration in the BVI?', 'sinclairs-services' ),
					'a' => array( __( 'VASP registration may be required for businesses providing virtual asset exchange, transfer, custody or administration services, or certain financial services connected with the offer or sale of virtual assets.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Does every digital asset business require VASP registration?', 'sinclairs-services' ),
					'a' => array( __( 'No. Registration depends on the services the business provides. However, SIBA or other BVI legislation may apply even where the business is not required to register as a VASP.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Can Sinclairs (BVI) assist with a VASP registration application?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We assess the proposed business model, advise on structure and compliance, prepare the registration application and supporting documents, and liaise with the BVI Financial Services Commission throughout the process.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'economic-substance',
			'title' => __( 'Economic Substance', 'sinclairs-services' ),
			'brief' => __( 'Advising BVI entities on the economic substance law and dealing with the ITA, if required.', 'sinclairs-services' ),
			'body'  => array(
				array( 'p', __( 'We advise companies and limited partnerships on their obligations under the Economic Substance (Companies and Limited Partnerships) Act, 2018 (as amended), which came into force on 1 January 2019 in the British Virgin Islands.', 'sinclairs-services' ) ),
				array( 'p', __( 'We help clients determine whether an entity is within the scope of the legislation and whether it carries on a relevant activity. Where an entity claims to be tax resident outside the BVI, we advise on the evidence required to support that position. Where the BVI economic substance requirements apply, we provide practical guidance on the level of substance the entity must maintain and the steps needed to demonstrate compliance.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'Does every BVI company or limited partnership have to maintain economic substance in the BVI?', 'sinclairs-services' ),
					'a' => array( __( 'No. The substance requirements generally apply where an entity is within the scope of the legislation, carries on a relevant activity and cannot establish that it is tax resident outside the BVI. Reporting obligations may still apply even where no relevant activity is conducted.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'What are the relevant activities under the ES Act?', 'sinclairs-services' ),
					'a' => array( __( 'The relevant activities include banking, insurance, fund management, finance and leasing, headquarters, shipping, holding, intellectual property, and distribution and service centre business.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'What if the entity is tax resident outside the BVI?', 'sinclairs-services' ),
					'a' => array( __( 'The entity may be treated as non-resident for economic substance purposes if it satisfies the applicable conditions and provides adequate evidence of its foreign tax residence. We can advise on the evidence required and whether the entity’s circumstances support the claim.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'banking-finance',
			'title' => __( 'Banking and Finance Law', 'sinclairs-services' ),
			// The Nutshell document's line for this service is missing its
			// closing bracket ("…entered into by BVI entities." with an
			// unclosed "(whether"). Closed here; flagged to the client.
			'brief' => __( 'Advising on corporate financing, registration of charges and security, and issuing legal opinions (whether on specific BVI legal issues for foreign courts or on the validity, capacity, power and enforceability of contracts/deeds entered into by BVI entities).', 'sinclairs-services' ),
			'body'  => array(
				array( 'p', __( 'We advise financial institutions and onshore law firms on the BVI aspects of finance transactions involving BVI entities. Our experience includes securitisations, swaps and derivatives, debt restructurings, and property and acquisition finance.', 'sinclairs-services' ) ),
				array( 'p', __( 'We prepare and review BVI transaction and security documents, advise on security over shares and other assets, and assist with corporate approvals and the registration of charges. We also issue BVI legal opinions on matters including corporate status, capacity, authority, due execution and the enforceability of transaction documents. Throughout the transaction, we work closely with clients and their onshore counsel to ensure that the BVI requirements are handled clearly and efficiently.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'Do you advise on cross-border financing transactions?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We advise lenders, borrowers, security providers and onshore counsel on the BVI aspects of cross-border loans, refinancings, acquisitions and other financing arrangements involving BVI entities.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Can security be taken over the shares or assets of a BVI company?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. Security may be taken over shares in a BVI company and over various company assets. We advise on the appropriate security documents, corporate approvals and any applicable registration requirements.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Do you provide BVI legal opinions for financing transactions?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We issue BVI legal opinions addressing matters such as the entity’s legal status, capacity and authority, the proper execution of transaction documents, and their validity and enforceability under BVI law.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'trusts-estates',
			'title' => __( 'Trusts and Estates', 'sinclairs-services' ),
			'brief' => __( 'Crafting wills, trust deeds, lasting powers of attorney, and handling applications for grants of probate and letters of administration.', 'sinclairs-services' ),
			'body'  => array(
				array( 'p', __( 'We advise individuals, families and professional advisers on BVI trusts and succession planning. Our work includes establishing discretionary trusts and VISTA trusts, as well as advising on private trust companies (PTCs) to act as trustees of qualifying private trusts.', 'sinclairs-services' ) ),
				array( 'p', __( 'We also prepare BVI wills and advise local and international clients on succession planning for assets situated in the British Virgin Islands.', 'sinclairs-services' ) ),
				array( 'p', __( 'Where a person dies leaving BVI assets, we handle applications to the BVI High Court for grants of probate and letters of administration. We can also apply to reseal a foreign grant issued in a recognised jurisdiction. These applications enable personal representatives to administer BVI estate assets, including shares in BVI companies.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'What is a VISTA trust?', 'sinclairs-services' ),
					'a' => array( __( 'A VISTA trust is primarily designed to hold shares in a BVI company. It allows the company’s directors to retain responsibility for managing the business, while limiting the trustee’s usual duty to intervene, subject to the terms of the trust and BVI law.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'What is a private trust company?', 'sinclairs-services' ),
					'a' => array( __( 'A private trust company, or PTC, is a BVI company established to act as trustee of qualifying private trusts. It can give a family greater involvement and continuity in the administration of its trust arrangements.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'What happens if a deceased person owned shares in a BVI company?', 'sinclairs-services' ),
					'a' => array( __( 'A BVI grant is generally required before the personal representative can transfer or otherwise deal with the shares. Depending on the circumstances, this may involve an application for probate or letters of administration, or the resealing of a qualifying foreign grant.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'voluntary-liquidations',
			'title' => __( 'Voluntary Liquidations', 'sinclairs-services' ),
			'brief' => __( 'Handling regulated and unregulated entities.', 'sinclairs-services' ),
			'body'  => array(
				array( 'lede', __( 'Voluntary liquidation provides an orderly and efficient way to wind up and formally dissolve a solvent BVI company.', 'sinclairs-services' ) ),
				array( 'p', __( 'It is available where a company has no liabilities, or where it can pay its debts as they fall due and the value of its assets is at least equal to its liabilities.', 'sinclairs-services' ) ),
				array( 'p', __( 'We can act as voluntary liquidators and/or simply guide clients through each stage of the process, from preparing the directors’ declaration of solvency, statement of assets and liabilities, and liquidation plan to appointing an eligible voluntary liquidator, completing the required filings and publishing the statutory notices. Once the liquidation is complete, the final documents are filed with the BVI Registrar of Corporate Affairs, which issues the company’s certificate of dissolution.', 'sinclairs-services' ) ),
				array( 'p', __( 'A straightforward voluntary liquidation can often be completed within six to ten weeks after the liquidator’s appointment is filed. More complex matters may take longer, depending on the company’s assets, liabilities, records and regulatory status.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'When can a BVI company enter voluntary liquidation?', 'sinclairs-services' ),
					'a' => array( __( 'A company may decide to enter into voluntary liquidation if is no longer conducting any business activities, has ceased trading or is simply no longer required for its purposes. However, for the process to be voluntary, the company must be solvent, meaning it must be able to pay its debts as they fall due and the value of its assets equals or exceeds its liabilities or have no liabilities.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Who may act as a voluntary liquidator?', 'sinclairs-services' ),
					'a' => array( __( 'The liquidator must be an eligible individual with the required experience and professional competence. The liquidator must also be resident in the BVI or, where joint liquidators are appointed, at least one must satisfy the BVI residency requirement.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Do regulated companies require BVI FSC approval?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. Before the directors or members pass the liquidation resolution, the BVI Financial Services Commission must consent to the regulated company entering voluntary liquidation and approve the proposed liquidator.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'trade-marks',
			'title' => __( 'Trade Marks', 'sinclairs-services' ),
			'brief' => __( 'Providing trade mark agent services, registrations, renewals and assignments.', 'sinclairs-services' ),
			'body'  => array(
				array( 'p', __( 'We advise international brand owners, businesses and overseas law firms on protecting and managing trade mark rights in the British Virgin Islands.', 'sinclairs-services' ) ),
				array( 'p', __( 'Our services include pre-filing searches, preparing and filing applications, renewing registrations, recording changes in ownership or registered details, and monitoring the BVI register for potentially conflicting marks. We also advise on oppositions, suspected infringements and the enforcement options available under BVI law.', 'sinclairs-services' ) ),
				array( 'p', __( 'Whether you are extending an established brand into the BVI or registering a trade mark for the first time, we provide clear, commercially focused advice and manage the process from application through registration and renewals.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'Must a BVI trade mark application be filed through a registered trade mark agent?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. Every application to register a trade mark in the BVI must be filed through a registered trade mark agent.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Does a trade mark registered elsewhere automatically receive protection in the BVI?', 'sinclairs-services' ),
					'a' => array( __( 'Not automatically. A separate BVI application is generally required to obtain registered rights. However, a qualifying application filed in a Paris Convention country or WTO member may support a priority claim if the BVI application is filed within six months.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'How long does a BVI trade mark registration last?', 'sinclairs-services' ),
					'a' => array( __( 'Registration lasts for ten years from the filing date, which is treated as the date of registration. It may be renewed for further periods of ten years.', 'sinclairs-services' ) ),
				),
			),
		),

		/* ---------------------------------------------------------- */
		array(
			'id'    => 'notarial-services',
			'title' => __( 'Notarial Services', 'sinclairs-services' ),
			'brief' => __( 'Notarising corporate documents, affidavits, transfer deeds, certificates and other personal documents, attending to apostille and legalization in the UK and UAE.', 'sinclairs-services' ),
			'body'  => array(
				array( 'p', __( 'We are Notaries Public and Commissioners of Oaths and provide all services required of notaries and commissioners of oaths in the British Virgin Islands.', 'sinclairs-services' ) ),
				array( 'p', __( 'We witness signatures, administer oaths and attend to the notarisation and legalization of corporate documents of BVI Companies. Where further authentication is required, we can also assist with the apostille or legalisation process in or outside of the BVI (such as the United Kingdom and the United Arab Emirates).', 'sinclairs-services' ) ),
				array( 'p', __( 'We also prepare Affidavits, Powers of Attorney and Special Notarial Certificates drafted to provide for the particular needs of our clients.', 'sinclairs-services' ) ),
				array( 'p', __( 'Our notarial services are used by individuals, businesses and law firms requiring BVI-authenticated documents for use in foreign jurisdictions, court proceedings, property transactions and other corporate matters.', 'sinclairs-services' ) ),
			),
			'faqs'  => array(
				array(
					'q' => __( 'Does a notarised document also require an apostille or legalisation?', 'sinclairs-services' ),
					'a' => array( __( 'Not always. Notarisation and apostille or legalisation are separate steps. Whether further authentication is required depends on the receiving country or authority, and we can advise on and assist with the appropriate process.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Can you prepare the document as well as notarise it?', 'sinclairs-services' ),
					'a' => array( __( 'Yes. We can prepare affidavits, statutory declarations, powers of attorney and notarial certificates suited to their intended use. If the receiving authority has prescribed wording or a particular form, this should be provided to us in advance.', 'sinclairs-services' ) ),
				),
				array(
					'q' => __( 'Do I need to provide the original document?', 'sinclairs-services' ),
					'a' => array( __( 'Generally, yes. If a true copy is to be certified, the Notary Public must inspect the original document. Where a signature must be witnessed, the original should usually remain unsigned until it is signed before the Notary. A copy may be sent in advance for review.', 'sinclairs-services' ) ),
				),
			),
		),
	);

	return apply_filters( 'sinclairs_services_content', $sections );
}

/**
 * The "In a Nutshell" index, exactly as grouped in the client's Nutshell
 * document, with two additions:
 *
 *  - "Investment Funds, Approved Managers & Investment Business" leads the
 *    index. The Nutshell document predates that copy (Adenike's note said
 *    the remaining services would follow separately), so its rows are
 *    drawn from the Investment Business document's own sub-headings.
 *  - "Probates" is a new row after Wills/Trusts, at the client's request
 *    (10 Aug). Its wording is summarised from the Trusts and Estates copy.
 *
 * 'target' is the section anchor a row jumps to. A row with an empty
 * target renders as plain text rather than a dead link — currently only
 * Residential Conveyancing, which has no detail copy yet.
 */
function ssvc_nutshell_groups() {
	$groups = array(
		array(
			'label' => __( 'Investment Funds, Approved Managers & Investment Business', 'sinclairs-services' ),
			'rows'  => array(
				array( 'label' => __( 'Investment Funds', 'sinclairs-services' ), 'target' => 'investment-business', 'text' => __( 'Structuring and establishing hedge funds, private equity funds and other investment structures under SIBA, including private, professional, incubator, approved and private investment funds, and segregated portfolio companies.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Approved Managers', 'sinclairs-services' ), 'target' => 'investment-business', 'text' => __( 'Establishing Approved Managers in the BVI and guiding qualifying investment managers and advisers through the application process with the BVI Financial Services Commission.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Investment Business Licensing', 'sinclairs-services' ), 'target' => 'investment-business', 'text' => __( 'Assisting investment managers, fund administrators and other service providers with applications for licences under SIBA, including contracts for differences and other regulated activities.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Authorised Representative Services', 'sinclairs-services' ), 'target' => 'investment-business', 'text' => __( 'Acting as the principal liaison between regulated funds and licensees and the BVI Financial Services Commission, and assisting with regulatory submissions and continuing obligations.', 'sinclairs-services' ) ),
			),
		),
		array(
			'label' => __( 'Corporate & Commercial Law', 'sinclairs-services' ),
			'rows'  => array(
				array( 'label' => __( 'Company & Commercial', 'sinclairs-services' ), 'target' => 'corporate-commercial', 'text' => __( 'Structuring or restructuring corporate entities, advising on contracts, cross-jurisdictional transactions, advising on acquisitions, company restorations. Continuations into and out of the BVI. Drafting special memorandum and articles of association for BVI Companies.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Banking & Corporate Finance', 'sinclairs-services' ), 'target' => 'banking-finance', 'text' => __( 'Advising on corporate financing, registration of charges and security, and issuing legal opinions (whether on specific BVI legal issues for foreign courts or on the validity, capacity, power and enforceability of contracts/deeds entered into by BVI entities).', 'sinclairs-services' ) ),
				array( 'label' => __( 'Directorship', 'sinclairs-services' ), 'target' => 'corporate-commercial', 'text' => __( 'Providing directorship services to licensed entities.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Economic Substance', 'sinclairs-services' ), 'target' => 'economic-substance', 'text' => __( 'Advising BVI entities on the economic substance law and dealing with the ITA, if required.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Commercial Property', 'sinclairs-services' ), 'target' => 'corporate-commercial', 'text' => __( 'Handling commercial leases, land acquisitions, development projects, and property management.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Digital or Virtual Assets and FinTech', 'sinclairs-services' ), 'target' => 'virtual-assets-fintech', 'text' => __( 'Advising on VASP and other digital and cryptocurrency issues.', 'sinclairs-services' ) ),
				array( 'label' => __( 'AML/CFT & Regulatory Compliance', 'sinclairs-services' ), 'target' => 'aml-cft-compliance', 'text' => __( 'Advises regulated entities and other relevant persons on the BVI framework for anti-money laundering, countering the financing of terrorism and countering proliferation financing (“AML/CFT/CPF”).', 'sinclairs-services' ) ),
				array( 'label' => __( 'Voluntary Liquidations', 'sinclairs-services' ), 'target' => 'voluntary-liquidations', 'text' => __( 'Handling regulated and unregulated entities.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Trade Marks', 'sinclairs-services' ), 'target' => 'trade-marks', 'text' => __( 'Providing trade mark agent services, registrations, renewals and assignments.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Notarial Services', 'sinclairs-services' ), 'target' => 'notarial-services', 'text' => __( 'Notarising corporate documents, affidavits, transfer deeds, certificates and other personal documents, attending to apostille and legalization in the UK and UAE.', 'sinclairs-services' ) ),
			),
		),
		array(
			'label' => __( 'Private Client Law', 'sinclairs-services' ),
			'rows'  => array(
				// No detail section supplied for residential conveyancing —
				// renders unlinked until the client provides copy.
				array( 'label' => __( 'Residential Conveyancing', 'sinclairs-services' ), 'target' => '', 'text' => __( 'Advising on purchasing and transferring of residential and commercial property.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Wills, Trusts & Estate Planning', 'sinclairs-services' ), 'target' => 'trusts-estates', 'text' => __( 'Crafting wills, trust deeds, lasting powers of attorney.', 'sinclairs-services' ) ),
				array( 'label' => __( 'Probates', 'sinclairs-services' ), 'target' => 'trusts-estates', 'text' => __( 'Applications to the BVI High Court for grants of probate and letters of administration, and the resealing of qualifying foreign grants, so personal representatives can administer BVI estate assets.', 'sinclairs-services' ) ),
			),
		),
	);

	return apply_filters( 'sinclairs_services_nutshell', $groups );
}

/**
 * Flat id => title map, used by the nav dropdown and the jump-to box.
 */
function ssvc_section_index() {
	$index = array();
	foreach ( ssvc_sections() as $section ) {
		$index[ $section['id'] ] = $section['title'];
	}
	return $index;
}
