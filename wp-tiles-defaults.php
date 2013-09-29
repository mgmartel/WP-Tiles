<?php
//global $wptiles_defaults;
$wptiles_defaults = array(
                "display"       => array (
                    "text"          => "show",
                    "byline"        => "cats",
                    "bylineBg"      => "default",
                    "bylineOpacity" => '0.8',
                    "cellPadding"   => 10
                ),
                // Post query
                "posts_query"   => array (
                                    'numberposts'   => 20,
                                    'offset'        => 0,
                                    'category'      => 0,
                                    'orderby'       => 'post_date',
                                    'order'         => 'DESC',
                                    'include'       => array(),
                                    'exclude'       => array(),
                                    'meta_key'      => '',
                                    'meta_value'    =>'',
                                    'post_type'     => 'post',
                                    'suppress_filters'
                                                    => true,
                                    'posts_per_page'
                                                    => -1,
                                    'page'          => 1,
                                    'tax_query'     => array(),
                                    'post_parent'   => 0,
                                    'post_mime_type' => ''
                ),
                'colors'        => array (
                    "colors"        => array (
                                    "#009999",
                                    "#1D7373",
                                    "#006363",
                                    "#33CCCC",
                                    "#5CCCCC",
                        ),
                    ),
                'templates'     => array (
                    'show_selector' => 'true',
                    "small_screen_width" => "800",
                    "templates"     => array (
                                  "News" => " A A B B . \n"
                                          . " A A . C C \n"
                                          . " . D D E E \n"
                                          . " F F . E E ",

                           "Alternative" => " . A A B B \n"
                                          . " . A A C C \n"
                                          . " D D F F . \n"
                                          . " E E F F . ",

                                "Banner" => " A . B . C \n"
                                          . " A . B . C \n"
                                          . " . D . E . \n"
                                          . " . D . E . ",

                              "Featured" => " . A A A . \n"
                                          . " . A A A . \n"
                                          . " . A A A . ",

                                 "Plain" => " . . . . . ",

                             "Condensed" => " . . . . . . . . ",
                             // Meta:
                             "option_is_array"
                                         => 'exclusive'

                                    ),
                    "small_screen_template" =>
                                          " A A \n"
                                        . " . . \n"
                                        . " A A \n"
                                        . " . . \n"
                                        . " A A \n"
                                        . " . . \n"
                                        . " A A "
                    ,
                ),

                // These are only for in the shortcodes
                "template"  => '',
                'show_selector' => '',
            );