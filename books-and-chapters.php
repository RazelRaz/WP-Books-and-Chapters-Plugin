<?php
/*
 * Plugin Name:       Books & Chapters
 * Description:       This is a basic Plugin
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Razel Ahmed
 * Author URI:        https://razelahmed.com
 */

 if ( ! defined('ABSPATH') ) {
    exit;
 }

 class Books_And_Chapters {
    public function __construct() {
        add_action('init', array( $this,'init') );
    }

    public function init() {
        add_filter('the_content', array( $this,'show_chapters_in_book') );
        add_filter('the_content', array( $this,'show_bookimage_in_chapter') );
        add_filter('post_type_link', array( $this,'chapter_cpt_slug_fix'), 1, 2 );
        add_filter('the_content', array( $this,'show_related_books_by_meta') );
        add_filter('the_content', array( $this,'show_related_books_by_texonomy') );
    }

    // Show related books / items by Texonomy
    public function show_related_books_by_texonomy($content) {
        if(is_singular('book')){
            $book_id = get_the_ID();
            $genres = wp_get_post_terms($book_id, 'genre');
            $genre = $genres[0]->term_id;
            $args = [
                'post_type' => 'book',
                'post__not_in' => [$book_id],
                'tax_query' => [
                    [
                        'taxonomy' => 'genre',
                        'field' => 'term_id',
                        'terms' => $genre
                    ]
                ]
            ];
            $books = get_posts($args);
            if($books){
                $content .= '<h2>Related Books By Taxonomy</h2>';
                $content .= '<ul>';
                foreach($books as $book){
                    $content .= '<li><a href="' . get_permalink($book->ID) . '">' . $book->post_title . '</a></li>';
                }
                $content .= '</ul>';
            }
        }
        return $content;
    }

    // Show related books / items by Meta
    public function show_related_books_by_meta($content) {
        if (is_singular('book')) {
            $book_id = get_the_ID();
            $genre = get_post_meta($book_id,'genre', true);
            $args = array(
                'post_type' => 'book',
                'post__not_in' => [$book_id],
                'meta_key'=>'genre',
                'meta_value'=>$genre
            );

            $books = get_posts($args);

            if($books){
                $content .= '<h2>Related Books By Meta Field</h2>';
                $content .= '<ul>';
                foreach($books as $book){
                    $content .= '<li><a href="' . get_permalink($book->ID) . '">' . $book->post_title . '</a></li>';
                }
                $content .= '</ul>';
            }
        }
        return $content;
    }

    // chapters slug fixing
    public function chapter_cpt_slug_fix( $post_link, $chapterpost ) {
        if (get_post_type($chapterpost) == 'chapter') {
            $book_id = get_post_meta( $chapterpost->ID,'book', true );
            $book = get_post( $book_id );
            $post_link = str_replace('%book%', $book->post_name, $post_link );
        }
        return $post_link;
    }


    // show book image in chapter
    public function show_bookimage_in_chapter($content) {
        if (is_singular('chapter')) {
            $chapter_id = get_the_ID();
            // parent book of the chapter
            $book_id = get_post_meta( $chapter_id,'book', true);
            $book = get_post($book_id);
            $image = get_the_post_thumbnail( $book_id, 'medium' );
            //$heading =  "<h2>Book: <a href='" . get_permalink($book_id) . "'>" . $book->post_title . "</a></h2>";
            $image_html = '<p><a href="' . get_permalink($book_id) . '">'. $image .'</a></p>';
            $content = $image_html . $content;
        }
        return $content;
    }

    //show chapters in book
    public function show_chapters_in_book($content) {
        if (is_singular('book')) {
            $book_id = get_the_ID();
            // $heading = '<h2>Chapter Heading</h2>';
            // $content = $content . $heading;

            // argument for  post query
            $args = array(
                'post_type' => 'chapter',
                // 'meta_key'=> 'book',
                // 'meta_value'=> $book_id,
                'meta_query' => array(
                    array(
                        'key' => 'book',
                        'value'=> $book_id,
                        'compare' => '=',
                    ),
                ),
                // 'orderby'=> 'id',
                // 'orderby'=> 'title',
                'meta_key' => 'chapter_number',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
            );

            $chapters = get_posts( $args );
            // print_r( $chapters );

            if ( ! empty( $chapters ) ) {
                $heading = '<h2>Chapter Heading</h2>';
                $content = $content . $heading;
                $content .= '<ul>';
                foreach ($chapters as $chapter) {
                    $content .= '<li><a href="' . get_the_permalink($chapter->ID) . '">' . $chapter->post_title .  '</a></li>';
                }
                $content .= '</ul>';
            }
        }
        return $content;
    }


 }

new Books_And_Chapters();