<?php

namespace Municipio\Controller;

/**
 * Class Singular
 * @package Municipio\Controller
 */
class Singular extends \Municipio\Controller\BaseController
{
    /**
     * @return array|void
     */
    public function init()
    {   
        parent::init();

        //Get post data 
        $this->data['post'] = \Municipio\Helper\Post::preparePostObject(get_post($this->getPageID()));

        $this->data['isBlogStyle'] = $this->data['post']->postType === "post" || "nyheter" ? true : false;

        //Get feature image data
        $this->data['featuredImage'] = $this->getFeaturedImage($this->data['post']->id);
        
        //Signature options
        $this->data['signature'] = $this->getSignature(); 

        $this->data['publishTranslations'] = (object) array(
            'updated'   => __('Updated', 'municipio'),
            'publish'   => __('Published', 'municipio'),
            'by'        => __('Published by', 'municipio'),
            'on'        => __('on', 'municipio'),
        );

        //Reading time
        $this->data['readingTime']          = $this->getReadingTime($this->data['post']->postContent); 
        $this->data['lang']->readingTime    = __('Reading time', 'municipio');

        //Comments
        if(get_option('comment_moderation') === '1') {
            $this->data['comments'] = get_approved_comments($this->data['post']->id, array(
                'order'     => get_option('comment_order')
            ));
        }else {    
            $this->data['comments'] = get_comments(array(
                'post_id'   => $this->data['post']->id,
                'order'     => get_option('comment_order')
            ));            
        }

        //Replies
        $this->data['replyArgs'] = array(
            'add_below'  => 'comment',
            'respond_id' => 'respond',
            'reply_text' => __('Reply'),
            'login_text' => __('Log in to Reply'),
            'depth'      => 1,
            'before'     => '',
            'after'      => '',
            'max_depth'  => get_option('thread_comments_depth')
        );

        //Post settings
        $this->data['settingItems'] = apply_filters_deprecated('Municipio/blog/post_settings', array($this->data['post']), '3.0', 'Municipio/blog/postSettings'); 

        //Should link author page
        $this->data['authorPages'] = apply_filters('Municipio/author/hasAuthorPage', false);

        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getSignature() : object
    {
        $postId         = $this->data['post']->id;
        $displayAuthor  = get_field('page_show_author', 'option'); 
        $displayAvatar  = get_field('page_show_author_image', 'option'); 
        $linkAuthor     = get_field('page_link_to_author_archive', 'option');

        $displayPublish = in_array($this->data['postType'], (array) get_field('show_date_published', 'option')); 
        $displayUpdated = in_array($this->data['postType'], (array) get_field('show_date_updated', 'option'));

        if ($displayPublish) {
            $published  = $this->getPostDates($this->data['post']->id)->published;
        }

        if ($displayUpdated) {
            $updated    = $this->getPostDates($this->data['post']->id)->updated;
        }

        return (object) [
            'avatar'    => ($displayAvatar ? $this->getAuthor($postId)->avatar : ""),
            'role'      => ($displayAuthor ? __("Author", 'municipio') : ""),
            'name'      => ($displayAuthor ? $this->getAuthor($postId)->name : ""),
            'link'      => ($linkAuthor ? $this->getAuthor($postId)->link : ""),
            'published' => ($displayPublish ? $published : false),
            'updated'   => ($displayUpdated ? $updated : false),
        ];
    }

    /**
     * @param $id
     * @return object
     */
    private function getAuthor($id): object
    {
        $author = array(
            'id' => $this->data['post']->postAuthor,
            'link' => get_author_posts_url($this->data['post']->postAuthor),
            'name' => null,
            'avatar' => null
        );
        
        //Get setting for username
        $displayName = get_the_author_meta( 'display_name', $this->data['post']->postAuthor );  
        
        //List of less-fancy displaynames
        $prohoboitedUserNames = [
            get_the_author_meta( 'user_login', $this->data['post']->postAuthor ),
            get_the_author_meta( 'nickname', $this->data['post']->postAuthor )
        ]; 

        //Assign only if fancy variant of name
        if(!in_array($displayName, $prohoboitedUserNames)) {
            $author['name'] = $displayName; 
        }

        //Get avatar url
        $avatar = get_avatar_url($id, ['default' => 'blank']); 
        if(!preg_match('/d=blank/i', $avatar)) {
            $author['avatar'] = $avatar;
        }

        return apply_filters('Municipio/Controller/Singular/author', (object) $author);
    }

    /**
     * @param $id
     * @return mixed
     */
    private function getPostDates($id) : object
    {
        return apply_filters('Municipio/Controller/Singular/publishDate', (object) [
            'published' => get_the_date(), 
            'updated' => get_the_modified_date()
        ]);
    }

    /**
     * @param $postId Post id
     * @param $size Name or array for size of image
     * @return array An array of data related to the image
     */
    private function getFeaturedImage($postId, $size = [1920,1080])
    {

        //Check option if it should be displayed
        if(get_field('post_single_show_featured_image', $postId) == false) {
            return false; 
        }

        //Get the image id
        $featuredImageId = get_post_thumbnail_id($postId);

        //Bail out if not found
        if(!is_numeric($featuredImageId)) {
            return false; 
        }

        $featuredImageObject = (object) [
            'id'    => $featuredImageId, 
            'src'   => wp_get_attachment_image_src($featuredImageId, $size),
            'alt'   => get_post_meta($featuredImageId, '_wp_attachment_image_alt', true),
            'title' => get_the_title($featuredImageId)
        ];
         
        return apply_filters('Municipio/Controller/Singular/featureImage', $featuredImageObject);
    }

    /**
     * Calculate reading time
     *
     * @param   string      $postContent    The post content
     * @param   integer     $factor         What factor to devide with, default 200 = normal reading speed
     * @return  integer                     Interger representing number of reading minutes  
     */
    public function getReadingTime($postContent, $factor = 200) {
        return (int) ceil((str_word_count(strip_tags($postContent))/$factor));
    }
}
