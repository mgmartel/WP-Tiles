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
                'colors'        => array ( "colors" =>
                                  "#009999\n"
                                . "#1D7373\n"
                                . "#006363\n"
                                . "#33CCCC\n"
                                . "#5CCCCC"
                    ),
                'templates'     => array (
                    "templates"     => array (
                                     "1" => " . A A B B \n"
                                          . " C C . B B \n"
                                          . " D D E E . \n"
                                          . " D D . C C ",

                                     "2" => " . A A D B \n"
                                          . " C A A D B \n"
                                          . " G F E E . \n"
                                          . " G F . C C ",

                                     "3" => " A B C C D \n"
                                          . " A B C C D \n"
                                          . " G F E E . \n"
                                          . " G F . H H ",

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