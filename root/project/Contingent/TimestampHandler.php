<?php
namespace Contingent;

class TimestampHandler {
    public static function format($timestamp) {
        $current_time = time();
        $time_diff = $current_time - $timestamp;
        
        $seconds = $time_diff;
        $minutes = round($seconds / 60);
        $hours = round($minutes / 60);
        $days = round($hours / 24);
        $weeks = round($days / 7);
        $months = round($weeks / 4.35);
        $years = round($months / 12);

        if ($seconds <= 10) {
            return "just now";
        } elseif ($seconds < 60) {
            return "$seconds seconds ago";
        } elseif ($minutes <= 1) {
            return "a minute ago";
        } elseif ($minutes < 60) {
            return "$minutes minutes ago";
        } elseif ($hours <= 24) {
            return ($hours == 1) ? "an hour ago" : "$hours hours ago";
        } elseif ($days <= 7) {
            return ($days == 1) ? "yesterday" : "$days days ago";
        } elseif ($weeks <= 4.35) {
            return ($weeks == 1) ? "a week ago" : "$weeks weeks ago";
        } elseif ($months <= 12) {
            return ($months == 1) ? "a month ago" : "$months months ago";
        } else {
            return ($years == 1) ? "a year ago" : "$years years ago";
        }
    }
}
?>
