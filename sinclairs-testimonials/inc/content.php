<?php
/**
 * The testimonials, transcribed from the client's Word document
 * (Testimonials for Website for Sinclairs BVI, v03 / v02 250825).
 *
 * Wording is verbatim. The attribution that trailed each quote — "–
 * Benoit Q. Asset Management Director" — is split into 'name' and
 * 'role' so the carousel can set them above the quote, and the curly
 * quotation marks that wrapped each one are dropped because the styling
 * supplies them.
 *
 * Run through the `sinclairs_testimonials` filter before rendering, so
 * the list can be adjusted from a child theme without touching this file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvit_testimonials() {
	$items = array(
		array(
			'name'  => 'Benoit Q.',
			'role'  => 'Asset Management Director',
			'quote' => array(
				'What really makes Sinclairs (BVI) stand out is their genuine commitment to client satisfaction. They take the time to understand our unique needs and offer solutions that truly work for us. Plus, their communication is always prompt and clear, keeping us in the loop every step of the way.',
				'We highly recommend this firm to anyone seeking reliable, expert legal and notary services in the BVI. They have been an invaluable partner in our journey, and we look forward to many more years of successful collaboration.',
			),
		),
		array(
			'name'  => 'Matt F.',
			'role'  => 'In-House Counsel',
			'quote' => array(
				'Sinclairs (BVI)’s help has been essential in successfully restructuring elements of our investment fund business. It’s rare to find an expert in a niche area of law who is also a generalist but Sinclairs (BVI) is definitely both. Sinclairs (BVI) is personable and communicative. Internal clients that have worked with Sinclairs (BVI) have been particularly impressed by this.',
				'Beyond legal advice, Sinclairs (BVI) has connected us with other skilled professional service providers from her network and that is something we value greatly. We can recommend Sinclairs (BVI) to anyone looking for a more personalised and tailored legal support service that bigger firms sometimes struggle to offer.',
			),
		),
		array(
			'name'  => 'Victor C.',
			'role'  => 'FinTech',
			'quote' => array(
				'SINCLAIRS (BVI) Lawyers and Notaries Public have been invaluable in handling my legal and notarization issues in the British Virgin Islands. Their team is professional, dedicated, and highly knowledgeable. We are very satisfied with their services and highly recommend them.',
			),
		),
		array(
			'name'  => 'Marta F.',
			'role'  => 'Consultant',
			'quote' => array(
				'Sinclairs (BVI) was exceptional in their services providing guidance throughout the process and delivering as promised. Their friendliness and professionalism were key in making me feel valued as a client. I would definitely recommend Sinclairs BVI!',
			),
		),
		array(
			'name'  => 'Michael F.',
			'role'  => 'Former Counsel',
			'quote' => array(
				'I was fortunate enough to have engaged Sinclairs (BVI) to represent me on the purchase of property in the BVI…They expertly guided me through the process as a purchaser and demonstrated a firm command of the law as my attorney, as well as a practical skill set that was necessary to get the transaction accomplished. There were a number of points that I wanted included in the Sale and Purchase Agreement and her negotiation skills were key to getting them reflected in the final contract.',
				'As a practicing lawyer myself, I was quite demanding and far more engaged than most clients would have been, and this my appraisal of her should be viewed through that prism of heightened scrutiny and expectations. My well-deserved endorsement of her was thus earned by her impressive diligence, talent and professionalism.',
			),
		),
	);

	return apply_filters( 'sinclairs_testimonials', $items );
}
