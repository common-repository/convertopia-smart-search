<?php

class ConvertopiaAdminNotices {

    function error($message) {
        echo "<div class='notice notice-error is-dismissible'>
            <p>" . esc_html($message) . "</p>
        </div>";

    }

    function info($message) {
        echo "<div class='notice notice-info is-dismissible'>
            <p>" . esc_html($message) . "</p>
        </div>";

    }

    function success($message) {
        echo "<div class='notice notice-success is-dismissible'>
            <p>" . esc_html($message) . "</p>
        </div>";

    }
}