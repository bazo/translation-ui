<?php

/**
 * Activity
 *
 * @author Martin
 */
class Activity
{

	const CREATE_PROJECT = 'create_project';
	const DELETE_PROJECT = 'delete_project';
	const ADD_COLLABORATOR = 'add_collaborator';
	const REMOVE_COLLABORATOR = 'remove_collaborator';
	const CREATE_TRANSLATION = 'create_translation';
	const REMOVE_TRANSLATION = 'remove_translation';
	const TRANSLATE_SINGULAR = 'translate_singular';
	const TRANSLATE_PLURAL = 'translate_singular';
	const ADD_MESSAGE = 'add_message';
	const DELETE_MESSAGE = 'delete_message';
	const IMPORT_TEMPLATE = 'import_template';


	private static $messages = array(
		self::CREATE_PROJECT => 'created project <strong>%1$s</strong>',
		self::DELETE_PROJECT => 'deleted project <strong>%1$s</strong>',
		self::ADD_COLLABORATOR => 'added <strong>%2$s</strong> as <strong>%3$s</strong> to project <strong>%1$s</strong>',
		self::REMOVE_COLLABORATOR => 'removed <strong>%2$s</strong> as <strong>%2$s</strong> from project <strong>%1$s</strong>',
		self::CREATE_TRANSLATION => 'created <strong>%2$s</strong> translation for project <strong>%1$s</strong>',
		self::REMOVE_TRANSLATION => 'removed <strong>%2$s</strong> translation from project <strong>%1$s</strong>',
		self::TRANSLATE_SINGULAR => 'translated message <strong>%2$s</strong> to <strong>%3$s</strong>',
		self::TRANSLATE_PLURAL => 'added translation for <strong>%2$s</strong> to <strong>%3$s</strong>',
		self::ADD_MESSAGE => 'added message <strong>%2$s</strong> to project <strong>%1$s</strong>',
		self::DELETE_MESSAGE => 'deleted message <strong>%2$s</strong> from project <strong>%1$s</strong>',
		self::IMPORT_TEMPLATE => 'imported <strong>%2$d</strong> new messages to project <strong>%1$s</strong>, increasing the count to <strong>%3$d</strong>.'
	);
	private static $classes = array(
		self::CREATE_PROJECT => 'success',
		self::DELETE_PROJECT => 'danger',
		self::ADD_COLLABORATOR => 'info',
		self::REMOVE_COLLABORATOR => 'danger',
		self::CREATE_TRANSLATION => 'success',
		self::REMOVE_TRANSLATION => 'danger',
		self::TRANSLATE_SINGULAR => 'info',
		self::TRANSLATE_PLURAL => 'info',
		self::ADD_MESSAGE => 'success',
		self::DELETE_MESSAGE => 'danger',
		self::IMPORT_TEMPLATE => 'info'
	);
	private static $icons = array(
		self::CREATE_PROJECT => 'icon-plus-sign',
		self::DELETE_PROJECT => 'icon-remove',
		self::ADD_COLLABORATOR => 'icon-user',
		self::REMOVE_COLLABORATOR => 'icon-user',
		self::CREATE_TRANSLATION => 'icon-plus',
		self::REMOVE_TRANSLATION => 'icon-remove',
		self::TRANSLATE_SINGULAR => 'icon-text-width',
		self::TRANSLATE_PLURAL => 'icon-text-width',
		self::ADD_MESSAGE => 'icon-plus',
		self::DELETE_MESSAGE => 'icon-remove',
		self::IMPORT_TEMPLATE => 'icon-upload'
	);


	public static function getMessage($activity)
	{
		return self::$messages[$activity];
	}


	public static function getClass($activity)
	{
		return self::$classes[$activity];
	}


	public static function getIcon($activity)
	{
		return self::$icons[$activity];
	}


}

