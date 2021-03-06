<?php
/**
 * HelloWorld SpecialPage for LocalTag extension
 *
 * @file
 * @ingroup Extensions
 */

class SpecialLocalTag extends SpecialPage {
	public function __construct() {
		parent::__construct( 'LocalTag' );
	}

	/**
	 * Show the page to the user
	 *
	 * @param string $sub The subpage string argument (if any).
	 *  [[Special:LocalTagList/subpage]].
	 */
	public function execute( $sub ) {
		global $wgLocalTagSubstitutions, $wgLocalTagArgumentSeparator, $wgLocalTagArgumentMarker,
			$wgLocalTagShowHTMLOnSpecialPage, $wgLocalTagShowCSSOnSpecialPage;

		// Get output page object
		$out = $this->getOutput();

		// Set title of the page
		$out->setPageTitle( $this->msg( 'localtag-special-title' ) );

		// Add help link??
// 		$out->addHelpLink( 'How to become a MediaWiki hacker' );

		// Print introductory text.
		$out->addWikiMsg( 'localtag-special-intro' );

		// Get various words that may need translating.
		// syntax
		$syn =	$this->msg( 'localtag-syntax' );
		// value
		$val =	$this->msg( 'localtag-value' );
			//// values
			//$vals =	$this->msg( 'localtag-values' );
			//// argument
			//$arg =	$this->msg( 'localtag-argument' );
			//// arguments
			//$args =	$this->msg( 'localtag-arguments' );
		// abbreviation for 'argument'
		$ar =	$this->msg( 'localtag-arg' );
		// documentation
		$doc =	$this->msg( 'localtag-documentation' );
		// wikitext
		$wikt =	' '.trim( $this->msg( 'localtag-wikitext' ) ).' ';
 		// content
		$content = ' '.trim( $this->msg( 'localtag-content' ) ).' ';

		// Gather settings
		// all defined attributes
		$subs =	$wgLocalTagSubstitutions;
		// number of attributes
		$cnt =	count( $subs );
		// $set = the string that separates arguments in the localtag declaration
		//  e.g. -> the '!' in <localtag att="arg1!arg2">
		$sep =	$wgLocalTagArgumentSeparator;
		// $mark = the string that represents where an argument goes in the pre text or post text
		$mark =	$wgLocalTagArgumentMarker;
		// $html = a true/false flag that represents whether the raw HTML of the pre text and
		//   post text will be displayed on this Special page.
		$html =	$wgLocalTagShowHTMLOnSpecialPage;
		// $css = a true/false flag that represents whether the raw CSS will be displayed on
		//   this Special page.
		$css =	$wgLocalTagShowCSSOnSpecialPage;


		// Build table
		// Wrap with <div> construct to help save mobile devices
		$table = '<div style="overflow:auto; border=0; margin=0; padding=0; max-width:100%;">'."\n";
		// Start with headers.
		$table .= '{| class="specialLocaltag"'."\n|-\n!".$syn."\n!".$doc."\n";
		if( $html ) {
			// We're showing HTML
			$table .= "!HTML\n";
		}
		if( $css ) {
			// We're showing CSS
			$table .= "!CSS\n";
		}
		// Go through each defined attribute, add them to the table on separate rows.
		foreach ( $subs as $attribute => $definitions ) {
			$table .= "|-\n";
			// $d = documentation
			$d = ( isset( $definitions['doc'] ) ? $definitions['doc'] : '' );
			// $openHTML = pre text (html)
			$openHTML = ( isset( $definitions['open'] ) ? $definitions['open'] : '' );
			// $closeHTML = post text (html)
			$closeHTML = ( isset( $definitions['close'] ) ? $definitions['close'] : '' );
			// This next section figures out the number of arguments this
			//   particular attribute requires.
			$a = substr_count( $openHTML, $mark ) + substr_count( $closeHTML, $mark );
			// $arglist = the portion that defines an attribute value
			//    e.g. -> ="arg1!arg2"
			$arglist = '';
			if ( $a > 0 ) {
				// if we have at least one argument...
				if ( $a > 1 ) {
					// if we have at least two arguments...
					$arrrglist = [];
					while ( $a > 0 ) {
						$arrrglist[] = $ar.$a--;
					}
					$z = array_reverse( $arrrglist );
					$x = implode( $sep, $z );
				} else {
					// we only have one argument
					$x = $val;
					$arrrglist = [ $val ];
				}
				$arglist = '="'.$x.'"';
			}
			// construct the syntax
			if( $openHTML && $closeHTML ) {
				$syntax = '&lt;localtag '.$attribute.$arglist.'&gt;'.$content.'&lt;/localtag&gt;';
			} elseif ( $openHTML || $closeHTML ) {
				$syntax = '&lt;localtag '.$attribute.$arglist.' /&gt;';
			} else {
				$syntax = $this->msg( 'localtag-missing-html', $attribute );
			}
			// add attribute to the table
			// add documentation to the table
			$table .= '| class="LTmono LTsyntax"| <nowiki>'.$syntax."</nowiki>\n".'| class="LTdoc"| '.rtrim( $d )."\n";
			// if we're showing HTML, add it to the table
			if ( $html ) {
				// $z list of pre text, 'wikitext', and post text
				//   list is deliminated by 'wikitext' and any arguments
				$z = explode( $mark, $openHTML );
				$z[] = $wikt;
				$y = explode( $mark, $closeHTML );
				$z = array_merge( $z, $y );
				$x = '';
				while ( $z ) {
					// pull first segment
					$y = array_shift( $z );
					// if this isn't the last segment, this isn't 'wikitext'
					//   and this isn't the end of the pre text...
					if ( $z && $y !== $wikt && $z[0] !== $wikt ) {
						// save the segment, plus an 'arg#'
						$x .= htmlspecialchars( $y ).array_pop( $arrrglist );
					} else {
						// save 'wikitext' or the segment
						$x .= htmlspecialchars( $y );
					}
				}
				// add the HTML without wiki translation
				$table .= '| class="LTmono LTHTML"| <nowiki>'.$x."</nowiki>\n";
			}
			// if we're showing CSS, add it to the table
			if ( $css ) {
				$css = ( isset($definitions['css'] ) ? $definitions['css'] : '' );
				$table .= '| class="LTmono LTCSS"| <nowiki>'.$css."</nowiki>\n";
			}
		} // end foreach $subs, $attribute, $definitions
		// End table
		$table .= "|}\n";
		// End mobile-friendly construct
		$table .= "</div>\n";
		// Send the table out
		$out->addWikiText( $table );
		// Add CSS to style the table cells.
		$inCSS  = '
			table.specialLocaltag th,
			table.specialLocaltag thead,
			table.specialLocaltag tfoot,
			table.specialLocaltag caption { display: none; }

			table.specialLocaltag td,
			table.specialLocaltag tr,
			table.specialLocaltag { display: block; }

			table.specialLocaltag tr:nth-child(even) { background: #f9f9f9; }

			table.specialLocaltag td.LTmono { font-family: "courier new",monospace; font-size: 0.9em; }

			table.specialLocaltag td.LTsyntax { margin-top: 1.5em; }

			table.specialLocaltag td.LTdoc:before { font-weight: bold; font-family: inherit; content: \''.$doc.': \'; }

			';
		if( $html ) {
			$inCSS .= "table.specialLocaltag td.LTHTML:before { font-weight: bold; font-family: inherit; content: 'HTML: '; } ";
		}
		if ( $css ) {
			$inCSS .= "table.specialLocaltag td.LTCSS:before { font-weight: bold; font-family: inherit; content: 'CSS: '; } ";
		}
		$out->addInlineStyle( $inCSS );
	}
}
