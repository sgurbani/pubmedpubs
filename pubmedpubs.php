<?php

        /*
                Plugin Name: PubMedPubs
                Plugin URI: https://github.com/sgurbani/pubmedpubs
                Description: A simple plugin that uses PubMed's RSS feed feature to display a list of publications. Use the shortcode [pubmedpubs url='PUBMED_RSS_URL'] in any post or page.
                Version: 0.1
                Author: Saumya Gurbani
                Author URI: http://ssg.io
                License: MIT
        */

        // Register the plugin
        register_activation_hook(__FILE__, 'pubmedpubs_install');
        register_deactivation_hook(__FILE__, 'pubmedpubs_uninstall');
        add_action('init', 'pubmedpubs_install', 99);

        //shortcode function - all it does is return the total score of the user, unformatted
        function pubmedpubs_getfeed( $atts ) {
                //write to an output buffer, which will be returned at the end
                ob_start();

                //get the url from the attributes parameter, use "" as default
                $a = shortcode_atts( array(
                        'url' => '',
                ), $atts );

                //get page contents from PubMed feed
                $feedstr = file_get_contents($a['url'], 0, $ctx);
                if($feedstr==false) {echo 'Could not retrieve feed contents.'; return ob_get_clean();};

                //convert page string to xml object
                $xml = new SimpleXMLElement($feedstr, LIBXML_NOCDATA);

                //query for all channel/item tags
                $res = $xml->xpath('channel/item');

                while(list( , $node) = each($res)) {
                        $href = $node->xpath('link');
                        $title = $node->xpath('title');
                        $auths = $node->xpath('author');

                        //Journal name is in a CDATA formatted block of html
                        $cdatablock = $node->xpath('description');
                        $doc = new DOMDocument();
                        $doc->loadHTML($cdatablock[0]);
                        $xp = new DOMXpath($doc);
                        $el = $xp->query('*/p');
                        $journal = $el->item(1)->nodeValue;

                        //output the results
                        echo '<b><a style="color: #002878;" href="' . $href[0] . '" target="_new">' . $title[0] . '</a></b><br/>' . $auths[0] . '<br/>' . $journal . '<br/><br/>';
                }

                //return the contents of the output buffer
                return ob_get_clean();
        }

        function pubmedpubs_install() {
                //add shortcode
                add_shortcode('pubmedpubs', 'pubmedpubs_getfeed');
        }

        function pubmedpubs_uninstall() {
		//remove shortcode
		remove_shortcode('pubmedpubs');
	}
?>