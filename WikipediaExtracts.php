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

	static function onFunctionHook( $parser, $title = null, $sentences = null ) {
		$title = $title ? $title : $parser->getTitle()->getText();
		$language = $parser->getTargetLanguage()->getCode();
		$chars = null;
		$sentences = $sentences ? $sentences : null;
		$limit = null;
		$intro = null;
		$plaintext = null;
		$sectionformat = null;
		$continue = null;
		$variant = null;

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
}