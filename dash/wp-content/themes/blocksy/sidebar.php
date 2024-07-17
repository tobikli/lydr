<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Blocksy
 */

$sidebar = new \Blocksy\Sidebar();

/**
 * Note to code reviewers: This line doesn't need to be escaped.
 * The value used here escapes the value properly.
 * It's the actual WordPress sidebar content.
 */
echo $sidebar->render();

