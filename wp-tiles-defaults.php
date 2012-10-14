<?php
global $wptiles_defaults;
$wptiles_defaults = array(
                // Post query
                "posts_query"         => array (
                                    'numberposts'   => 20,
                                    'offset'        => 0,
                                    'category'      => 0,
                                    'orderby'       => 'rand',
                                    'order'         => 'DESC',
                                    'include'       => array(),
                                    'exclude'       => array(),
                                    'meta_key'      => '',
                                    'meta_value'    =>'',
                                    'post_type'     => 'post',
                                    'suppress_filters'
                                                    => true,
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
                    "show_selector" => "true",
                    "templates"     => array (
                                  "News" => " . A A B B \n"
                                          . " C C . B B \n"
                                          . " D D E E . \n"
                                          . " D D . C C ",

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
                                          //. " . G H I J ",

                                 "Plain" => " . . . . . ",

                             "Condensed" => " . . . . . . . . ",

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
                "template"  => '',
            );