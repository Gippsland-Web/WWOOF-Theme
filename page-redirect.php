<?php
/*
Template Name: Login-Register Redirect
*/

if(get_current_user_id() != 0) {
    header('Location: '.bp_core_get_user_domain(get_current_user_id()));
}
else {
    header('Location: '.get_site_url());
}