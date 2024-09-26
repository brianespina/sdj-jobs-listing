<?php

function sdj_jobs_func() {
    return '<div id="sdj_jobs_app"></div>';
}

add_shortcode( 'sdj_jobs', 'sdj_jobs_func' );