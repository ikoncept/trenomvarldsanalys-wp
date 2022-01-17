<?php

namespace Municipio\Comment;
use \HelsingborgStad\RecaptchaIntegration as Captcha;

/**
 * Class CommentsActions
 * @package Municipio\Comment
 */
class CommentsActions
{
    public function __construct()
    {
        add_action('wp_ajax_remove_comment', array($this, 'removeComment'));
        add_action('wp_ajax_get_comment_form', array($this, 'getCommentForm'));
        add_action('wp_enqueue_scripts', array($this, 'getScripts'), 20);
        add_action('wp_ajax_update_comment', array($this, 'updateComment'));

    }

    /**
     * Update a comment front end
     * @return void
     */
    public function updateComment()
    {
        $newComment = $_POST['comment'] ?? null;
        $commentId = $_POST['commentId'] ?? null;

        if (!$newComment || !$commentId || !$comment = get_comment($commentId)) {
            wp_send_json_error('Missing variables');
        }

        if (!current_user_can('edit_comment', $comment->comment_ID) && !($comment->user_id == get_current_user_id())) {
            wp_send_json_error('Missing authorization');
        }

        // Validate nonce
        if (!check_ajax_referer("update-comment_$comment->comment_ID", 'nonce', false)) {
            wp_send_json_error('Nonce failed');
        }

        $comment->comment_content = $newComment;
        if (wp_update_comment((array) $comment)) {
            wp_send_json_success('Update was successful');
        }

        wp_send_json();
    }

    /**
     * Returns markup for the edit comment form
     * @return void
     */
    public function getCommentForm()
    {
        $postId = $_POST['postId'] ?? null;
        $commentId = $_POST['commentId'] ?? null;

        if (!$commentId || !$postId || !$comment = get_comment($commentId)) {
            wp_send_json_error('Missing variables');
        }

        if (!$comment = get_comment($commentId)) {
            wp_send_json_error('Comment is missing');
        }

        $args = array(
            'id_form' => 'commentupdate',
            'class_submit' => 'btn btn-sm btn-primary',
            'title_reply' => '',
            'title_reply_before' => '',
            'title_reply_after' => '',
            'label_submit' => __('Update', 'municipio'),
            'logged_in_as' => '',
            'comment_field' => '<textarea id="update-comment" name="comment" cols="45" rows="8" aria-required="true">' . $comment->comment_content . '</textarea>',
            'comment_notes_after' => '<input type="hidden" name="commentId" value="' . $commentId . '">
            <input type="hidden" name="nonce" value="' . wp_create_nonce("update-comment_$commentId") . '">',
            'submit_button' => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" /> <a href="#" class="cancel-update-comment gutter gutter-left gutter-sm"><small>' . __('Cancel', 'municipio') . '</small></a>'
        );

        ob_start();
        comment_form($args, $postId);
        $form = ob_get_clean();
        $form = str_replace('class="comment-respond"', 'class="comment-respond comment-respond-new comment-update"', $form);
        $form = str_replace('id="respond"', 'id="respond-edit"', $form);

        wp_send_json_success($form);
    }

    /**
     *
     */
    public static function getInitialCommentForm() {

        $reCaptcha = (!is_user_logged_in()) ? '<input type="hidden" class="g-recaptcha-response" 
            name="g-recaptcha-response" value="" />' : '';
        $reCaptchaTerms = __('This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy">Privacy Policy</a> and <a href="https://policies.google.com/terms">Terms of Service</a> apply.', 'municipio');
        ob_start();
        ob_get_clean();

        $args = array(
            'id_form'           => 'commentform',
            'logged_in_as'      => '',
            'must_log_in'       => '',
            'class_form'        => 'c-form',
            'id_submit'         => 'submit',
            'class_submit'      => 'c-button 
                                    comment-reply-link 
                                    c-button 
                                    c-button__filled 
                                    c-button__filled--primary 
                                    c-button--md',
            'name_submit'       => 'submit',
            'submit_button'     => '
                <div class="comment--actions">
                <input 
                    name="%1$s" 
                    type="submit" 
                    id="%2$s" 
                    class="u-padding__bottom--3%3$s" 
                    value="%4$s" 
                    /></div>',
            'format'            => 'html5',
            'cancel_reply_link' => __( 'Cancel reply' ),
            'comment_field'     =>
                $reCaptcha . '<div class="c-textarea">
                                <textarea 
                                    id="comment"
                                    name="comment"
                                    placeholder="'.__('Comment text','text-domain').'" 
                                    aria-required="true"></textarea><br><div class="c-typography 
                                    c-typography__variant--meta u-padding__top--2">'.$reCaptchaTerms.'</div></div>'
        );

        comment_form( $args );
    }

    /**
     * Init and enqueue Google Captcha Script
     */
    public static function getScripts(){
        Captcha::initScripts();
    }

    /**
     * Delete comment. Works similar as 'wp_ajax_delete_comment',
     * but this allows all user roles to delete their own comments.
     * @return void
     */
    public function removeComment()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if (!$comment = get_comment($id)) {
            wp_send_json_error('Comment is missing');
        }

        if (!current_user_can('edit_comment', $comment->comment_ID) && !($comment->user_id == get_current_user_id())) {
            wp_send_json_error('Missing authorization');
        }

        // Validate nonce
        if (!check_ajax_referer("delete-comment_$id", 'nonce', false)) {
            wp_send_json_error('Nonce failed');
        }

        $trashed = wp_trash_comment($comment);
        if ($trashed) {
            wp_send_json_success('Deletion was successful');
        }

        wp_send_json();
    }
}
