<?php

class WikipediaExtracts {

	static function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'WikipediaExtract', 'WikipediaExtracts::onHook' );
		$parser->setFunctionHook( 'WikipediaExtract', 'WikipediaExtracts::onFunctionHook' );
		return true;
	}

	static function onHook( $input, array $args, Parser $parser, PPFrame $frame ) {
		// Set the defaults
		$title = $parser->getTitle()->getRootText();
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
			if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
				// Extract the title
				$path = parse_url( $input, PHP_URL_PATH );
				$PATH = explode( '/', $path );
				$title = $PATH[2];

				// Extract the language
				$host = parse_url( $input, PHP_URL_HOST );
				$HOST = explode( '.', $host );
				$language = $HOST[0];
			} else {
				$title = $input;
			}
		}

		// Validate language code
		if ( !Language::isValidCode( $language ) ) {
			return '<span class="error">' . wfMessage( 'wikipediaextracts-invalid-language', $language ) . '</span>';
		}

		// Query the Wikipedia API
		$data = array(
			'action' => 'query',
			'titles' => urldecode( $title ),
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
			$url = 'https://' . $language . '.wikipedia.org/wiki/' . urlencode( $title );
			$extract .= wfMessage( 'wikipediaextracts-credits', $url )->parse();
			return $extract;
		}
	}

	static function onFunctionHook( $parser, $input = null ) {
		// Set the defaults
		$title = $parser->getTitle()->getRootText();
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
		$options = WikipediaExtracts::extractOptions( array_slice( func_get_args(), 2 ) );
		extract( $options );
		if ( $input ) {
			if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
				// Extract the title
				$path = parse_url( $input, PHP_URL_PATH );
				$PATH = explode( '/', $path );
				$title = $PATH[2];

				// Extract the language
				$host = parse_url( $input, PHP_URL_HOST );
				$HOST = explode( '.', $host );
				$language = $HOST[0];
			} else {
				$title = $input;
			}
		}

		// Validate language code
		if ( !Language::isValidCode( $language ) ) {
			return '<span class="error">' . wfMessage( 'wikipediaextracts-invalid-language', $language ) . '</span>';
		}

		// Query the Wikipedia API
		$data = array(
			'action' => 'query',
			'titles' => urldecode( $title ),
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
			$url = 'https://' . $language . '.wikipedia.org/wiki/' . urlencode( $title );
			$extract .= wfMessage( 'wikipediaextracts-credits', $url )->plain();
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