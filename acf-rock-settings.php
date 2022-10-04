<?php

/**
 * Plugin Name: ACF Rock Settings
 * Plugin URI:
 * Description: Additional settings for various ACF fields.
 * Version: 1.0.1
 * Author: Zane M. Kolnik
 * Author URI: ---
 * License:
 * License URI:
 */

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

function acf_rock_settings_acf_custom_settings( $field ) {
	acf_render_field_setting( $field, [
		'label' => 'Word Count',
		'instructions' => 'Enter the word count limit',
		'name' => 'word_count',
		'type' => 'number',
	]);
}
add_action('acf/render_field_settings/type=textarea', 'acf_rock_settings_acf_custom_settings');
add_action('acf/render_field_settings/type=text', 'acf_rock_settings_acf_custom_settings');

function acf_rock_settings_maximum_terms_settings( $field ) {
	acf_render_field_setting( $field, [
		'label' => 'Maximum',
		'instructions' => 'Enter the maximum terms.',
		'name' => 'maximum_terms',
		'type' => 'number',
	]);
}
add_action('acf/render_field_settings/type=taxonomy', 'acf_rock_settings_maximum_terms_settings');

function acf_rock_settings_maximum_terms_validate_value( $valid, $value, $field, $input ){
	// bail early if value is already invalid
	if ( !$valid ) return $valid;
	if ( empty($field['maximum_terms']) ) return $valid;

	$max_terms = $field['maximum_terms'];
	if(is_countable($value)) {
	$terms_count = count($value);
		if ($terms_count > $max_terms) {
			$valid = sprintf('Limited to: %s', $max_terms);
		}
	}
	return $valid;
}
add_filter('acf/validate_value/type=taxonomy', 'acf_rock_settings_maximum_terms_validate_value', 10, 4);

function acf_rock_settings_minimum_terms_settings( $field ) {
	acf_render_field_setting( $field, [
		'label' => 'Minimum',
		'instructions' => 'Enter the minimum terms.',
		'name' => 'minimum_terms',
		'type' => 'number',
	]);
}
add_action('acf/render_field_settings/type=taxonomy', 'acf_rock_settings_minimum_terms_settings');

function acf_rock_settings_minimum_terms_validate_value( $valid, $value, $field, $input ){
	// bail early if value is already invalid
	if ( !$valid ) return $valid;
	if ( empty($field['minimum_terms']) ) return $valid;

	$minimums_terms = $field['minimum_terms'];
	if(is_countable($value)) {
		$terms_count = count($value);

		if ($terms_count < $minimums_terms) {
			$valid = sprintf('Minimum is: %s', $minimums_terms);
		}
	}
	return $valid;
}
add_filter('acf/validate_value/type=taxonomy', 'acf_rock_settings_minimum_terms_validate_value', 10, 4);

function acf_rock_settings_acf_textarea_validate_value( $valid, $value, $field, $input ){
	// bail early if value is already invalid
	if ( !$valid ) return $valid;
	if ( empty($field['word_count']) ) return $valid;

	$max_words = $field['word_count'];
	$words_array = explode(' ', trim($value));
	if (count($words_array) > $max_words) {
		$valid = sprintf('Word count is limited to: %s', $max_words);
	}
	return $valid;
}
add_filter('acf/validate_value/type=textarea', 'acf_rock_settings_acf_textarea_validate_value', 10, 4);
add_filter('acf/validate_value/type=text', 'acf_rock_settings_acf_textarea_validate_value', 10, 4);

/**
 * Add a setting to select future posts from the admin.
 *
 * @param $field
 */
function acf_rock_settings_acf_relationship_publish_date_setting($field) {
	acf_render_field_setting( $field, array(
		'label'			=> 'Limit to only future Post Types.',
		'instructions'	=> 'When checked only future published Post Types are selectable.',
		'type'			=> 'checkbox',
		'name'			=> 'future_events_only',
		'choices'		=> array(
			'future_events_only'	=> 'Limit the available Events to future Events.',
		),
		'default_value' => 'future_events_only',
	));
}
add_filter( 'acf/render_field_settings/type=relationship', 'acf_rock_settings_acf_relationship_publish_date_setting');

/**
 * Allow the ability to select future posts from the relationship
 * admin.
 *
 * @param $args
 * @param $field
 * @param $post_id
 *
 * @return mixed
 * @throws \Exception
 */
function acf_rock_settings_acf_relationship_query_filter( $args, $field, $post_id ) {
	if ( empty( $field['future_events_only'] ) ) {
		return $args;
	}
	$now = new DateTime('now');
	$args['meta_query'] = [
		'relation' => 'AND',
		[
			'key' => 'event_group_event_start_date',
			'value' => $now->format('Y-m-d H:i:s'),
			'compare' => '>=',
		]
	];
	return $args;
}
add_filter('acf/fields/relationship/query', 'acf_rock_settings_acf_relationship_query_filter', 10, 3);


/**
 * Interesting; ACF allows you to select unpublished post,
 * and display them on the front-end.
 *
 * This disables that.
 *
 * https://support.advancedcustomfields.com/forums/topic/only-show-published-posts-in-relationship-post-page-link-picker-etc/
 *
 * @param $args
 * @param $field
 * @param $post_id
 *
 * @return mixed
 */
function my_acf_fields_post_object_query($args, $field, $post_id) {
	$args['post_status'] = ['publish'];
	return $args;
}
add_filter('acf/fields/relationship/query', 'my_acf_fields_post_object_query', 10, 3);