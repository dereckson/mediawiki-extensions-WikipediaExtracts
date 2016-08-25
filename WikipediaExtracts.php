<?php

class WikipediaExtracts {

	static function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'WikipediaExtract', 'WikipediaExtracts::onHook' );
		$parser->setFunctionHook( 'WikipediaExtract', 'WikipediaExtracts::onFunctionHook' );
		return true;
	}

	static function onHook( $input, array $args, Parser $parser, PPFrame $frame ) {
		// Defaults
		$title = $parser->getTitle()->getText();
		$language = $parser->getTargetLanguage()->getCode();
		$chars = null;
		$sentences = null;
		$limit = null;
		$intro = null;
		$plaintext = null;
		$sectionformat = null;
		$continue = null;
		$variant = null;

		// Override with user input
		extract( $args );
		if ( $input ) {
			$title = $parser->recursiveTagParse( $input );
		}

		// Query the Wikipedia API
		$data = array(
			'action' => 'query',
			'titles' => $title,
			'prop' => 'extracts',
			'exchars' => $chars,
			'exsentences' => $sentences,
			'exlimit' => $limit,
			'exintro' => $intro,
			'explaintext' => $plaintext,
			'exsectionformat' => $sectionformat,
			'excontinue' => $continue,
			'exvariant' => $variant,
			'redirects' => true,
			'format' => 'json'
		);
		$query = 'https://' . $language . '.wikipedia.org/w/api.php?' . http_build_query( $data );
		$contents = file_get_contents( $query );
		$contents = json_decode( $contents );
		$pages = $contents->query->pages;
		foreach ( $pages as $key => $value ) {
			if ( $key === '-1' ) {
				return '<span class="error">' . wfMessage( 'wikipediaextracts-404', $title ) . '</span>';
			}
			$extract = $value->extract;
			return $extract;
		}
	}

	static function onFunctionHook( $parser, $input = null ) {
		// Defaults
		$title = $parser->getTitle()->getText();
		$language = $parser->getTargetLanguage()->getCode();
		$chars = null;
		$sentences = null;
		$limit = null;
		$intro = null;
		$plaintext = null;
		$sectionformat = null;
		$continue = null;
		$variant = null;

		// Override with user input
		$options = WikipediaExtracts::extractOptions( array_slice( func_get_args(), 1 ) );
		extract( $options );
		if ( $input ) {
			$title = $parser->recursiveTagParse( $input );
		}

		// Query the Wikipedia API
		$data = array(
			'action' => 'query',
			'titles' => $title,
			'prop' => 'extracts',
			'exchars' => $chars,
			'exsentences' => $sentences,
			'exlimit' => $limit,
			'exintro' => $intro,
			'explaintext' => $plaintext,
			'exsectionformat' => $sectionformat,
			'excontinue' => $continue,
			'exvariant' => $variant,
			'redirects' => true,
			'format' => 'json'
		);
		$query = 'https://' . $language . '.wikipedia.org/w/api.php?' . http_build_query( $data );
		$contents = file_get_contents( $query );
		$contents = json_decode( $contents );
		$pages = $contents->query->pages;
		foreach ( $pages as $key => $value ) {
			if ( $key === '-1' ) {
				return '<span class="error">' . wfMessage( 'wikipediaextracts-404', $title ) . '</span>';
			}
			$extract = $value->extract;
			return $extract;
		}
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value. If no = is provided,
	 * true is assumed like this: [name] => true
	 *
	 * @param array string $options
	 * @return array $results
	 */
	static function extractOptions( $options ) {
		$results = array();

		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) === 2 ) {
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				$results[ $name ] = $value;
			}
			if ( count( $pair ) === 1 ) {
				$name = trim( $pair[0] );
				$results[ $name ] = true;
			}
		}
		// Now you've got an array that looks like this:
		// [foo] => "bar"
		// [apple] => "orange"
		// [banana] => true
		return $results;
	}
}