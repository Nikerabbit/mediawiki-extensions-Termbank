<?php
/**
 * Semantic pages consists mostly of templates. Semantic data is inside wiki
 * pages in these templates. To update the data we need to modify the pages.
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2012, Niklas Laxström
 * @license Public domain
 * @file
 */

/**
 * With this class you can parse a page containing semantic templates into
 * structured array based data, compare and change some fields and regenerate
 * the page text otherwise unchanged.
 */
class TemplateParser {
	/**
	 * Holds the original page text.
	 * @var String
	 */
	protected $text;

	/**
	 * Holds the page text with templates replaced by placeholders.
	 * @var String
	 */
	protected $layoutTemplate;

	/**
	 * Holds the parsed templates.
	 * Array keys are the placeholder strings.
	 * Each item is an array, which contains:
	 * - text: the original template text
	 * - name: the name of the template
	 * - params: associative array keys and values
	 *
	 * @var Array*[String => String, String => String, String => Array*[String => String]]
	 */
	protected $holders;

	protected function __construct( $text ) {
		$this->text = $text;
	}

	/**
	 * Construct a new instance from text.
	 * @param String $text
	 * @return TemplateParser
	 */
	public function newFromText( $text ) {
		return new self( $text );
	}

	/**
	 * Construct a new instance from a title.
	 * @return TemplateParser
	 */
	public function newFromTitle( Title $title ) {
		$page = new WikiPage( $title );
		$text = $page->getText( Revision::RAW );
		return new self( $text );
	}

	/**
	 * Returns a random string that can be used as a placeholder.
	 * @return String
	 */
	protected static function placeholder() {
		static $i = 0;
		return "\x7fUNIQ" . dechex( mt_rand( 0, 0x7fffffff ) ) . dechex( mt_rand( 0, 0x7fffffff ) ) . '|' . $i++;
	}

	/**
	 * Replaces part of $string between $start and $end with $rep.
	 * @param String $string Full text
	 * @param String $rep Replacement text
	 * @param Int $start Start index of text to replace
	 * @param Int $end End index of text to replace
	 * @return String
	 */
	protected static function index_replace( $string, $rep, $start, $end ) {
		return substr( $string, 0, $start ) . $rep . substr( $string, $end );
	}

	/**
	 * Parses the page.
	 * @return Array
	 */
	public function extractTemplates() {
		$copy = $this->text;
		$holders = array();

		$offset = 0;
		while ( true ) {
			$re = '~^{{.*\n}}$~smU';
			$matches = array();
			$ok = preg_match( $re, $copy, $matches, PREG_OFFSET_CAPTURE, $offset );

			if ( $ok === 0 ) {
				break; // No matches
			}

			// Do-placehold for the whole stuff
			$content = $matches[0][0];
			$ph    = self::placeholder();
			$start = $matches[0][1];
			$len   = strlen( $content );
			$end   = $start + $len;
			$copy = self::index_replace( $copy, $ph, $start, $end );

			$holders[$ph] = self::parseTemplate( $content );
		}

		$this->layoutTemplate = $copy;
		$this->holders = $holders;
		return $holders;
	}

	/**
	 * Uses the holders returned by extractTemplates to reconstruct
	 * the page text. If you change param fields, unset the text field.
	 * To remove the template, set text field to empty string.
	 * To add new template, append new placeholder to the array with
	 * numerical index (like $holders[] = ...).
	 * @return String
	 */
	public function updateText( Array $holders ) {
		$copy = $this->layoutTemplate;
		foreach ( $holders as $placeholder => $template ) {
			$templateString = self::formatTemplate( $template );
			if ( is_int( $placeholder ) ) {
				$copy .= "\n" . $templateString;
				continue;
			}
			// Avoid build-up of whitespace when removing templates
			if ( $templateString === '' ) $placeholder .= "\n";
			$copy = str_replace( $placeholder, $templateString, $copy );
		}

		if ( $copy !== $this->text ) {
			return $copy;
		}
	}

	/**
	 * Given one template as string, parses it to easily modifiable format.
	 * @return Array
	 */
	protected static function parseTemplate( $text ) {
		$orig = $text;

		preg_match( '~^{{(.*)\n(.*)}}$~sU', $text, $m );
		list( $full, $name, $paramtext ) = $m;

		preg_match_all( '~^\|(.*)=(.*)((?=\n\|)|\Z)~smU', $paramtext, $p, PREG_SET_ORDER );
		foreach ( $p as $match ) {
			list( $full, $key, $value ) = $match;
			$params[$key] = $value;
		}
		return array(
			'text' => $text,
			'name' => $name,
			'params' => $params,
		);
	}

	/**
	 * Same as parseTemplate but in reverse.
	 * @return String
	 */
	protected static function formatTemplate( $ph ) {
		// Shortcut, if template was not modified, just return the original text
		if ( isset( $ph['text'] ) ) return $ph['text'];

		$name = $ph['name'];
		$params = '';
		foreach ( $ph['params'] as $key => $value ) {
			// If no params, the new line never gets added and we get {{daa}}
			if ( $params === '' ) $params = "\n";
			$params .= "|$key=$value\n";
		}

		return '{{' . $name . $params . '}}';
	}
}

/*$a = new TemplateParser( file_get_contents( 'mallikäsite.wiki' ) );
$holders = $a->extractTemplates();
echo $a->updateText( $holders );*/